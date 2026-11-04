@extends('layouts.main')

@section('title', 'Editar Mensalidade')
@section('breadcrumb-item', 'Mensalidades')
@section('breadcrumb-item-active', 'Editar')

@section('content')
<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card">
            <div class="card-header">
                <h5>Editar Mensalidade — {{ $membership->user->name ?? 'Sócio' }}</h5>
                <small class="text-muted">Mês de Competência: <strong>{{ $membership->month_year_formatted }}</strong></small>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.memberships.update', $membership) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sócio</label>
                            <input type="text" class="form-control" value="{{ $membership->user->name ?? '—' }}" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mês de Competência</label>
                            <input type="text" class="form-control" value="{{ $membership->month_year_formatted }}" disabled>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Valor (R$) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="amount" id="amount" 
                                class="form-control @error('amount') is-invalid @enderror" 
                                value="{{ old('amount', $membership->amount) }}" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="paid" {{ old('status', $membership->status) === 'paid' ? 'selected' : '' }}>Pago / Adimplente</option>
                                <option value="pending" {{ old('status', $membership->status) === 'pending' ? 'selected' : '' }}>Pendente</option>
                                <option value="overdue" {{ old('status', $membership->status) === 'overdue' ? 'selected' : '' }}>Em Atraso</option>
                                <option value="cancelled" {{ old('status', $membership->status) === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label">Forma de Pagamento <span class="text-danger">*</span></label>
                            <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                <option value="pix" {{ old('payment_method', $membership->payment_method) === 'pix' ? 'selected' : '' }}>PIX</option>
                                <option value="dinheiro" {{ old('payment_method', $membership->payment_method) === 'dinheiro' ? 'selected' : '' }}>Dinheiro</option>
                                <option value="transferencia" {{ old('payment_method', $membership->payment_method) === 'transferencia' ? 'selected' : '' }}>Transferência</option>
                                <option value="cartao" {{ old('payment_method', $membership->payment_method) === 'cartao' ? 'selected' : '' }}>Cartão</option>
                                <option value="outro" {{ old('payment_method', $membership->payment_method) === 'outro' ? 'selected' : '' }}>Outro</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="payment_proof" class="form-label">Comprovante de Pagamento</label>
                            <input type="file" name="payment_proof" id="payment_proof" 
                                class="form-control @error('payment_proof') is-invalid @enderror"
                                accept="image/*,.pdf">
                            @if($membership->payment_proof)
                                <small class="d-block mt-1">
                                    <a href="{{ asset('storage/' . $membership->payment_proof) }}" target="_blank" class="text-info">
                                        <i class="ti ti-file-text"></i> Ver comprovante atual anexado
                                    </a>
                                </small>
                            @endif
                            @error('payment_proof')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label">Observações Internas</label>
                        <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes', $membership->notes) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.memberships.index') }}" class="btn btn-outline-secondary">Voltar</a>
                        <button type="submit" class="btn btn-primary px-4">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
