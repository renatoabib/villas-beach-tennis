@extends('layouts.main')

@section('title', 'Lançar Mensalidade / Pagamento')
@section('breadcrumb-item', 'Mensalidades')
@section('breadcrumb-item-active', 'Novo Lançamento')

@section('content')
<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card">
            <div class="card-header">
                <h5>Lançamento de Mensalidade (Avulso ou Antecipado)</h5>
                <small class="text-muted">Selecione o sócio e a quantidade de meses. O sistema criará automaticamente as mensalidades futuras mês a mês como adimplente.</small>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.memberships.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="user_id" class="form-label">Sócio <span class="text-danger">*</span></label>
                        <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">Selecione o sócio...</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ (old('user_id', $selectedUserId) == $user->id) ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->phone ?? $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_month" class="form-label">Mês Inicial de Competência <span class="text-danger">*</span></label>
                            <input type="month" name="start_month" id="start_month" 
                                class="form-control @error('start_month') is-invalid @enderror" 
                                value="{{ old('start_month', now()->format('Y-m')) }}" required>
                            <small class="text-muted">Mês a partir do qual as mensalidades serão geradas.</small>
                            @error('start_month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="months_count" class="form-label">Quantidade de Meses Pagos <span class="text-danger">*</span></label>
                            <select name="months_count" id="months_count" class="form-select @error('months_count') is-invalid @enderror" required>
                                <option value="1" {{ old('months_count', '1') == '1' ? 'selected' : '' }}>1 Mês (R$ 50,00)</option>
                                <option value="2" {{ old('months_count') == '2' ? 'selected' : '' }}>2 Meses (R$ 100,00)</option>
                                <option value="3" {{ old('months_count') == '3' ? 'selected' : '' }}>3 Meses - Trimestral (R$ 150,00)</option>
                                <option value="6" {{ old('months_count') == '6' ? 'selected' : '' }}>6 Meses - Semestral (R$ 300,00)</option>
                                <option value="12" {{ old('months_count') == '12' ? 'selected' : '' }}>12 Meses - Anual Antecipado (R$ 600,00)</option>
                            </select>
                            @error('months_count')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="amount_per_month" class="form-label">Valor por Mês (R$) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="amount_per_month" id="amount_per_month" 
                                class="form-control @error('amount_per_month') is-invalid @enderror" 
                                value="{{ old('amount_per_month', '50.00') }}" required>
                            @error('amount_per_month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label">Forma de Pagamento <span class="text-danger">*</span></label>
                            <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                <option value="pix" {{ old('payment_method') === 'pix' ? 'selected' : '' }}>PIX</option>
                                <option value="dinheiro" {{ old('payment_method') === 'dinheiro' ? 'selected' : '' }}>Dinheiro</option>
                                <option value="transferencia" {{ old('payment_method') === 'transferencia' ? 'selected' : '' }}>Transferência Bancária</option>
                                <option value="cartao" {{ old('payment_method') === 'cartao' ? 'selected' : '' }}>Cartão</option>
                                <option value="outro" {{ old('payment_method') === 'outro' ? 'selected' : '' }}>Outro</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Resumo Dinâmico em Destaque -->
                    <div class="alert alert-primary mb-3">
                        <div class="d-flex align-items-center">
                            <i class="ti ti-calculator f-24 me-3 text-primary"></i>
                            <div>
                                <h6 class="mb-1 text-primary">Resumo do Lançamento:</h6>
                                <p class="mb-0">
                                    Total a pagar: <strong id="lbl-total-amount" class="fs-5">R$ 50,00</strong> 
                                    (<span id="lbl-months-summary">1 mês de cobertura</span>)
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status Inicial <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="paid" {{ old('status', 'paid') === 'paid' ? 'selected' : '' }}>Pago / Confirmado (Adimplente)</option>
                                <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pendente (Aguardando Confirmação)</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="payment_proof" class="form-label">Comprovante de Pagamento (Opcional)</label>
                            <input type="file" name="payment_proof" id="payment_proof" 
                                class="form-control @error('payment_proof') is-invalid @enderror"
                                accept="image/*,.pdf">
                            <small class="text-muted">JPG, PNG ou PDF até 5MB.</small>
                            @error('payment_proof')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label">Observações Internas (Opcional)</label>
                        <textarea name="notes" id="notes" rows="2" class="form-control" placeholder="Ex: Pagamento anual via PIX recebido no banco.">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.memberships.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="ti ti-check me-1"></i> Confirmar e Gerar Mensalidades
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selMonths = document.getElementById('months_count');
        const inpAmount = document.getElementById('amount_per_month');
        const lblTotal = document.getElementById('lbl-total-amount');
        const lblSummary = document.getElementById('lbl-months-summary');

        function updateTotal() {
            const count = parseInt(selMonths.value) || 1;
            const amount = parseFloat(inpAmount.value) || 0;
            const total = count * amount;

            lblTotal.textContent = 'R$ ' + total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            lblSummary.textContent = count === 1 ? '1 mês de cobertura' : count + ' meses de cobertura contínua';
        }

        selMonths.addEventListener('change', updateTotal);
        inpAmount.addEventListener('input', updateTotal);
        updateTotal();
    });
</script>
@endsection
