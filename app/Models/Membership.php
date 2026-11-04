<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Membership extends Model
{
    use HasFactory;

    public const STATUS_PAID = 'paid';
    public const STATUS_PENDING = 'pending';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'association_id',
        'reference_month',
        'due_date',
        'amount',
        'status',
        'payment_method',
        'payment_proof',
        'receipt_number',
        'paid_at',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'reference_month' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'approved_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PAID => 'Pago / Adimplente',
            self::STATUS_PENDING => 'Pendente',
            self::STATUS_OVERDUE => 'Em Atraso',
            self::STATUS_CANCELLED => 'Cancelado',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'badge bg-light-success text-success',
            self::STATUS_PENDING => 'badge bg-light-warning text-warning',
            self::STATUS_OVERDUE => 'badge bg-light-danger text-danger',
            default => 'badge bg-light-secondary text-secondary',
        };
    }

    public function getMonthYearFormattedAttribute(): string
    {
        if (!$this->reference_month) {
            return '-';
        }
        return Carbon::parse($this->reference_month)->translatedFormat('F / Y');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function association()
    {
        return $this->belongsTo(Association::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
