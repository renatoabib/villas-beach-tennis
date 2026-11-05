<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\User;
use App\Models\Association;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MembershipController extends Controller
{
    /**
     * Lista as mensalidades com filtros e KPIs
     */
    public function index(Request $request)
    {
        $authUser = auth()->user();

        $query = Membership::with(['user', 'association', 'approvedBy']);

        if (!$authUser->hasRole('superadmin')) {
            $query->where('association_id', $authUser->association_id);
        }

        // Filtro por sócio
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por ano/mês
        if ($request->filled('month')) {
            $query->where('reference_month', Carbon::createFromFormat('Y-m', $request->month)->startOfMonth()->toDateString());
        }

        $memberships = $query->orderByDesc('reference_month')->paginate(20)->withQueryString();

        // Usuários disponíveis para filtro
        $usersQuery = User::where('is_active', true);
        if (!$authUser->hasRole('superadmin')) {
            $usersQuery->where('association_id', $authUser->association_id);
        }
        $users = $usersQuery->orderBy('name')->get();

        // KPIs do mês atual
        $currentMonth = now()->startOfMonth()->toDateString();
        $baseKpi = Membership::query();
        if (!$authUser->hasRole('superadmin')) {
            $baseKpi->where('association_id', $authUser->association_id);
        }

        $totalPaidMonth = (clone $baseKpi)
            ->where('reference_month', $currentMonth)
            ->where('status', Membership::STATUS_PAID)
            ->sum('amount');

        $countPaidMonth = (clone $baseKpi)
            ->where('reference_month', $currentMonth)
            ->where('status', Membership::STATUS_PAID)
            ->count();

        $countPendingMonth = (clone $baseKpi)
            ->where('reference_month', $currentMonth)
            ->where('status', '!=', Membership::STATUS_PAID)
            ->count();

        return view('admin.memberships.index', compact(
            'memberships',
            'users',
            'totalPaidMonth',
            'countPaidMonth',
            'countPendingMonth'
        ));
    }

    /**
     * Formulário de lançamento de mensalidade (suporta múltiplos meses antecipados)
     */
    public function create(Request $request)
    {
        $authUser = auth()->user();

        $usersQuery = User::where('is_active', true);
        if (!$authUser->hasRole('superadmin')) {
            $usersQuery->where('association_id', $authUser->association_id);
        }
        $users = $usersQuery->orderBy('name')->get();

        $selectedUserId = $request->get('user_id');

        return view('admin.memberships.create', compact('users', 'selectedUserId'));
    }

    /**
     * Salva o pagamento e gera os registros mês a mês
     */
    public function store(Request $request)
    {
        $authUser = auth()->user();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'start_month' => 'required|date_format:Y-m',
            'months_count' => 'required|integer|min:1|max:36',
            'amount_per_month' => 'required|numeric|min:0',
            'status' => 'required|in:paid,pending',
            'payment_method' => 'required|string|max:50',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $targetUser = User::findOrFail($validated['user_id']);

        if (!$authUser->hasRole('superadmin') && (int)$targetUser->association_id !== (int)$authUser->association_id) {
            abort(403, 'Acesso não autorizado para sócios de outro clube.');
        }

        // Upload do comprovante se enviado
        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
            $proofPath = $file->storeAs('proofs', $filename, 'public');
        }

        $startCarbon = Carbon::createFromFormat('Y-m', $validated['start_month'])->startOfMonth();
        $monthsCount = (int)$validated['months_count'];
        $createdOrUpdated = 0;

        for ($i = 0; $i < $monthsCount; $i++) {
            $refMonth = $startCarbon->copy()->addMonths($i);
            $dueDate = $refMonth->copy()->day(5);

            $data = [
                'association_id' => $targetUser->association_id,
                'due_date' => $dueDate->toDateString(),
                'amount' => $validated['amount_per_month'],
                'status' => $validated['status'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'],
            ];

            if ($proofPath) {
                $data['payment_proof'] = $proofPath;
            }

            if ($validated['status'] === Membership::STATUS_PAID) {
                $data['paid_at'] = now();
                $data['approved_by'] = $authUser->id;
                $data['approved_at'] = now();
                $data['receipt_number'] = 'VBT-' . $refMonth->format('Ym') . '-' . str_pad($targetUser->id, 4, '0', STR_PAD_LEFT);
            }

            Membership::updateOrCreate(
                [
                    'user_id' => $targetUser->id,
                    'reference_month' => $refMonth->toDateString(),
                ],
                $data
            );

            $createdOrUpdated++;
        }

        $mesesTexto = $monthsCount === 1 ? '1 mês registrado' : "{$monthsCount} meses registrados";
        return redirect()->route('admin.memberships.index')
            ->with('toast', "Sucesso: {$mesesTexto} com sucesso para {$targetUser->name}!");
    }

    /**
     * Edição de uma mensalidade específica
     */
    public function edit(Membership $membership)
    {
        $authUser = auth()->user();

        if (!$authUser->hasRole('superadmin') && (int)$membership->association_id !== (int)$authUser->association_id) {
            abort(403);
        }

        return view('admin.memberships.edit', compact('membership'));
    }

    /**
     * Atualização de uma mensalidade
     */
    public function update(Request $request, Membership $membership)
    {
        $authUser = auth()->user();

        if (!$authUser->hasRole('superadmin') && (int)$membership->association_id !== (int)$authUser->association_id) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:paid,pending,overdue,cancelled',
            'payment_method' => 'required|string|max:50',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
            $validated['payment_proof'] = $file->storeAs('proofs', $filename, 'public');
        }

        if ($validated['status'] === Membership::STATUS_PAID && !$membership->paid_at) {
            $validated['paid_at'] = now();
            $validated['approved_by'] = $authUser->id;
            $validated['approved_at'] = now();
            if (!$membership->receipt_number) {
                $ref = Carbon::parse($membership->reference_month);
                $validated['receipt_number'] = 'VBT-' . $ref->format('Ym') . '-' . str_pad($membership->user_id, 4, '0', STR_PAD_LEFT);
            }
        }

        $membership->update($validated);

        return redirect()->route('admin.memberships.index')
            ->with('toast', 'Mensalidade atualizada com sucesso.');
    }

    /**
     * Aprova um pagamento pendente (marca como pago)
     */
    public function approve(Request $request, Membership $membership)
    {
        $authUser = auth()->user();

        if (!$authUser->hasAnyRole(['admin', 'superadmin'])) {
            abort(403, 'Apenas administradores podem aprovar pagamentos.');
        }

        if (!$authUser->hasRole('superadmin') && (int)$membership->association_id !== (int)$authUser->association_id) {
            abort(403, 'Acesso não autorizado para sócios de outro clube.');
        }

        if ($membership->status === Membership::STATUS_PAID) {
            return redirect()->route('admin.memberships.index')
                ->with('toast', 'Esta mensalidade já está paga.');
        }

        $membership->update([
            'status' => Membership::STATUS_PAID,
            'paid_at' => now(),
            'approved_by' => $authUser->id,
            'approved_at' => now(),
            'payment_method' => $membership->payment_method ?? 'pix',
            'receipt_number' => $membership->receipt_number
                ?: 'VBT-' . Carbon::parse($membership->reference_month)->format('Ym') . '-' . str_pad($membership->user_id, 4, '0', STR_PAD_LEFT),
        ]);

        return redirect()->route('admin.memberships.index')
            ->with('toast', 'Pagamento aprovado: mensalidade de '
                . Carbon::parse($membership->reference_month)->format('m/Y')
                . ' de ' . ($membership->user?->name ?? 'sócio') . ' marcada como paga.');
    }

    /**
     * Gera pendências para todos os usuários ativos que não possuem registro no mês atual
     */
    public function generatePending(Request $request)
    {
        $authUser = auth()->user();
        if (!$authUser->hasAnyRole(['admin', 'superadmin'])) {
            abort(403);
        }

        $currentMonth = now()->startOfMonth()->toDateString();
        $users = User::where('is_active', true)->get();
        $count = 0;

        foreach ($users as $user) {
            $exists = Membership::where('user_id', $user->id)
                ->where('reference_month', $currentMonth)
                ->exists();

            if (!$exists) {
                Membership::create([
                    'user_id' => $user->id,
                    'association_id' => $user->association_id,
                    'reference_month' => $currentMonth,
                    'due_date' => now()->day(5)->toDateString(),
                    'amount' => 50.00,
                    'status' => Membership::STATUS_PENDING,
                    'payment_method' => 'pix',
                    'notes' => 'Gerado automaticamente pelo sistema.',
                ]);
                $count++;
            }
        }

        return redirect()->route('admin.memberships.index')
            ->with('toast', "{$count} pendências geradas com sucesso para o mês atual.");
    }

    /**
     * Exclui uma mensalidade
     */
    public function destroy(Membership $membership)
    {
        $authUser = auth()->user();

        if (!$authUser->hasAnyRole(['admin', 'superadmin'])) {
            abort(403, 'Apenas administradores podem excluir registros de mensalidade.');
        }

        if (!$authUser->hasRole('superadmin') && (int)$membership->association_id !== (int)$authUser->association_id) {
            abort(403);
        }

        $membership->delete();

        return redirect()->route('admin.memberships.index')
            ->with('toast', 'Registro de mensalidade excluído.');
    }

    /**
     * Visão do Sócio: histórico e status próprio
     */
    public function myMemberships()
    {
        $user = auth()->user();

        $memberships = Membership::where('user_id', $user->id)
            ->orderByDesc('reference_month')
            ->get();

        $financial = $user->getMembershipFinancialStatus();

        return view('admin.memberships.my', compact('memberships', 'financial'));
    }

    /**
     * Sócio anexa comprovante de pagamento
     */
    public function uploadProof(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'reference_month' => 'required|date_format:Y-m',
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $refDate = Carbon::createFromFormat('Y-m', $request->reference_month)->startOfMonth();
        $dueDate = $refDate->copy()->day(5);

        $file = $request->file('payment_proof');
        $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $proofPath = $file->storeAs('proofs', $filename, 'public');

        Membership::updateOrCreate(
            [
                'user_id' => $user->id,
                'reference_month' => $refDate->toDateString(),
            ],
            [
                'association_id' => $user->association_id,
                'due_date' => $dueDate->toDateString(),
                'amount' => 50.00,
                'status' => Membership::STATUS_PENDING,
                'payment_method' => 'pix',
                'payment_proof' => $proofPath,
                'notes' => 'Comprovante enviado pelo sócio.',
            ]
        );

        return back()->with('toast', 'Comprovante enviado com sucesso! Aguardando aprovação da administração.');
    }
}
