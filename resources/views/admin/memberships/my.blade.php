@extends('layouts.main')

@section('title', 'Minhas Mensalidades')
@section('breadcrumb-item', 'Clube')
@section('breadcrumb-item-active', 'Minhas Mensalidades')

@section('content')
    @if(session('toast') || session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <img src="{{ asset('favicon.svg') }}" class="img-fluid me-2" alt="favicon" style="width: 17px">
                    <strong class="me-auto">Villas Beach Tennis</strong>
                    <small>Agora</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    {{ session('toast') ?? session('success') ?? session('error') }}
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <!-- Card de Status do Sócio -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100 text-center {{ $financial['status'] === 'adimplente' ? 'bg-light-success border-success' : ($financial['status'] === 'tolerancia' ? 'bg-light-warning border-warning' : 'bg-light-danger border-danger') }}">
                <div class="card-body d-flex flex-column justify-content-center py-4">
                    <h5 class="mb-2 text-muted">Situação Atual da sua Mensalidade</h5>
                    <h2 class="{{ $financial['status'] === 'adimplente' ? 'text-success' : ($financial['status'] === 'tolerancia' ? 'text-warning' : 'text-danger') }} mb-2">
                        {{ $financial['label'] }}
                    </h2>
                    <p class="fs-6 text-muted mb-3">{{ $financial['message'] }}</p>

                    <div>
                        @if($financial['can_play'])
                            <span class="badge bg-success fs-6 py-2 px-3">
                                <i class="ti ti-check me-1"></i> Acesso às Quadras Liberado
                            </span>
                        @else
                            <span class="badge bg-danger fs-6 py-2 px-3">
                                <i class="ti ti-ban me-1"></i> Acesso às Quadras Bloqueado por Inadimplência
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Card de Instruções de Pagamento / PIX -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5><i class="ti ti-qrcode me-1 text-primary"></i> Pagamento via PIX</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">A mensalidade do Villas Beach Tennis é de <strong>R$ 50,00</strong> com vencimento entre o <strong>dia 01 e 05</strong> de cada mês.</p>
                    
                    <div class="bg-light p-3 rounded border mb-3">
                        <small class="text-muted d-block mb-1">Chave PIX (E-mail):</small>
                        <div class="d-flex align-items-center justify-content-between">
                            <strong class="fs-6 text-primary">contato@villasbeachtennis.com.br</strong>
                        </div>
                    </div>

                    <p class="small text-muted mb-3">
                        <i class="ti ti-info-circle me-1"></i> Após efetuar o PIX, anexe o comprovante abaixo para liberação imediata.
                    </p>

                    <!-- Formulário de Envio de Comprovante -->
                    <form action="{{ route('membre.memberships.proof') }}" method="POST" enctype="multipart/form-data" class="row g-2">
                        @csrf
                        <div class="col-sm-6">
                            <input type="month" name="reference_month" class="form-control form-control-sm" value="{{ now()->format('Y-m') }}" required>
                        </div>
                        <div class="col-sm-6">
                            <input type="file" name="payment_proof" class="form-control form-control-sm" accept="image/*,.pdf" required>
                        </div>
                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <i class="ti ti-upload me-1"></i> Enviar Comprovante de Pagamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela com Histórico de Mensalidades -->
    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header">
                    <h5>Meu Histórico de Mensalidades</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Mês de Competência</th>
                                    <th>Vencimento</th>
                                    <th>Valor</th>
                                    <th>Forma</th>
                                    <th>Data de Pagamento</th>
                                    <th>Recibo</th>
                                    <th>Comprovante</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($memberships as $m)
                                    <tr>
                                        <td><strong>{{ $m->month_year_formatted }}</strong></td>
                                        <td>{{ $m->due_date ? $m->due_date->format('d/m/Y') : 'Dia 05' }}</td>
                                        <td>R$ {{ number_format($m->amount, 2, ',', '.') }}</td>
                                        <td>{{ strtoupper($m->payment_method ?? 'PIX') }}</td>
                                        <td>{{ $m->paid_at ? $m->paid_at->format('d/m/Y H:i') : '—' }}</td>
                                        <td>
                                            @if($m->receipt_number)
                                                <span class="badge bg-light-primary">{{ $m->receipt_number }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($m->payment_proof)
                                                <a href="{{ asset('storage/' . $m->payment_proof') }}" target="_blank" class="btn btn-sm btn-outline-info py-0 px-2">
                                                    <i class="ti ti-file-text"></i> Ver Anexo
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="{{ $m->status_badge_class }}">
                                                {{ $m->status_label }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            Você ainda não possui registros de mensalidade no sistema.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
