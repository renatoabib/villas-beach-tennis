<?php

namespace App\Models;

use BaconQrCode\Creator;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, InteractsWithMedia;

    /**
     * Explicitly set the guard name for Spatie Permissions.
     *
     * This ensures Spatie always looks for permissions
     * assigned under the "web" guard.
     */
    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'bio',
        'availability',
        'skills',
        'is_active',
        'association_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->member_number)) {
                $year = now()->format('Y');
                $lastUser = User::whereNotNull('member_number')
                                ->where('member_number', 'like', "VBT-{$year}-%")
                                ->orderBy('id', 'desc')
                                ->first();

                $lastNumber = $lastUser ? (int)substr($lastUser->member_number, -4) : 0;
                $user->member_number = sprintf('VBT-%s-%04d', $year, $lastNumber + 1);
            }

            // Gerar e salvar QR Code SVG após definir o member_number
            if (!empty($user->member_number)) {
                $user->generateAndSaveQrCode();
            }
        });
    }

    public function association()
    {
        return $this->belongsTo(Association::class);
    }

    public function cotisations()
    {
        return $this->hasMany(Cotisation::class);
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * Verifica se o sócio está adimplente com base nas regras:
     * - Se o mês atual estiver pago -> Adimplente.
     * - Se for entre os dias 01 e 05 e o mês atual não foi pago -> tolerância (adimplente se o anterior estava em dia).
     * - A partir do dia 06, se o mês atual não estiver pago -> Inadimplente.
     */
    public function isAdimplente(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        $currentMonth = $now->copy()->startOfMonth()->toDateString();

        // 1. Mês atual está pago?
        $currentPaid = $this->memberships()
            ->where('reference_month', $currentMonth)
            ->where('status', Membership::STATUS_PAID)
            ->exists();

        if ($currentPaid) {
            return true;
        }

        // 2. Janela entre o dia 01 e 05: tolerância se o mês anterior estava pago ou se é novo sócio
        if ($now->day <= 5) {
            $previousMonth = $now->copy()->subMonth()->startOfMonth()->toDateString();
            $hasPrevious = $this->memberships()->where('reference_month', $previousMonth)->exists();

            if (!$hasPrevious) {
                return true;
            }

            return $this->memberships()
                ->where('reference_month', $previousMonth)
                ->where('status', Membership::STATUS_PAID)
                ->exists();
        }

        // 3. Após o dia 05 sem pagamento do mês corrente -> Inadimplente
        return false;
    }

    /**
     * Retorna a situação detalhada da mensalidade
     */
    public function getMembershipFinancialStatus(): array
    {
        $now = now();
        $currentMonth = $now->copy()->startOfMonth()->toDateString();

        $currentPaid = $this->memberships()
            ->where('reference_month', $currentMonth)
            ->where('status', Membership::STATUS_PAID)
            ->exists();

        if ($currentPaid) {
            // Obter até quando está pago (último mês pago antecipadamente)
            $lastPaid = $this->memberships()
                ->where('status', Membership::STATUS_PAID)
                ->where('reference_month', '>=', $currentMonth)
                ->orderByDesc('reference_month')
                ->first();

            $untilText = $lastPaid ? \Carbon\Carbon::parse($lastPaid->reference_month)->translatedFormat('F/Y') : null;

            return [
                'status' => 'adimplente',
                'label' => 'ADIMPLENTE',
                'badge' => 'bg-light-success text-success',
                'message' => $untilText ? "Mensalidades em dia até {$untilText}." : "Pagamentos em dia. Obrigado!",
                'can_play' => true,
            ];
        }

        if ($now->day <= 5) {
            return [
                'status' => 'tolerancia',
                'label' => 'AGUARDANDO PAGAMENTO',
                'badge' => 'bg-light-warning text-warning',
                'message' => 'Janela de pagamento aberta até o dia 05. Valor: R$ 50,00.',
                'can_play' => true,
            ];
        }

        return [
            'status' => 'inadimplente',
            'label' => 'INADIMPLENTE',
            'badge' => 'bg-light-danger text-danger',
            'message' => 'Mensalidade do mês em aberto após o dia 05. Uso da quadra bloqueado.',
            'can_play' => false,
        ];
    }

    // Register MediaLibrary collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_photo')
            ->singleFile() // only one profile photo per user
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif']);
    }

    /**
     * Gera e salva o QR Code SVG do sócio no storage público.
     * Chamado automaticamente no evento creating.
     */
    public function generateAndSaveQrCode(): void
    {
        $url = route('check.status', $this->member_number, absolute: true);

        $rendererStyle = new RendererStyle(300, 10);
        $backEnd = new SvgImageBackEnd();
        $renderer = new ImageRenderer($rendererStyle, $backEnd);

        $writer = new Writer($renderer);
        $contents = $writer->writeString($url);

        Storage::disk('public')->put("qrcodes/{$this->member_number}.svg", $contents);
    }

    /**
     * Retorna a URL pública do QR Code salvo, servida via PHP (evita problema com nginx/symlink).
     */
    public function qrCodeUrl(): ?string
    {
        $path = "qrcodes/{$this->member_number}.svg";

        if (Storage::disk('public')->exists($path)) {
            return route('check.qrcode', $this->member_number);
        }

        return null;
    }
}
