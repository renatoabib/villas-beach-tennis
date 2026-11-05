@extends('layouts.main')

@section('title', 'Meu Dashboard')
@section('breadcrumb-item', 'Dashboard')
@section('breadcrumb-item-active', 'Meu Perfil')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/js/plugins/apexcharts.min.css') }}">
@endsection

@section('content')
    <div class="row g-3 mb-4">
        <!-- Welcome Card -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body bg-light py-4 px-3 rounded-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 me-3">
                            <i class="ti ti-user fs-3"></i>
                        </div>
                        <div>
                            <h2 class="mb-0 fw-bold">Olá, {{ $user->name }}</h2>
                            <p class="mb-0 text-muted small">
                                <strong>{{ $user->member_number }}</strong>
                                • {{ $user->association?->name ?? 'Sem clube' }}
                                • {{ $user->email }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="ti ti-qrcode me-1"></i>Meu QR Code</h6>
                </div>
                <div class="card-body text-center py-4">
                    @php
                        $checkUrl = route('check.status', $user->member_number, absolute: true);
                        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($checkUrl);
                    @endphp
                    <img src="{{ $qrUrl }}"
                         alt="QR Code {{ $user->member_number }}"
                         class="img-fluid rounded border"
                         style="max-width: 220px; max-height: 220px;" />
                    <p class="mt-3 text-muted small">
                        <strong>{{ $user->member_number }}</strong><br>
                        Escaneie para verificar adimplência
                    </p>
                    <button class="btn btn-outline-primary btn-sm mt-2" onclick="window.print()">
                        <i class="ti ti-printer me-1"></i> Imprimir Etiqueta
                    </button>
                </div>
            </div>
        </div>

        <!-- Adimplência Card -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="ti ti-wallet me-1"></i>Adimplência</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="mb-1 text-muted small">Status</p>
                            <h5 class="mb-0 fw-bold text-{{ $user->isAdimplente() ? 'success' : 'danger' }}">
                                {{ $user->isAdimplente() ? 'Adimplente' : 'Inadimplente' }}
                            </h5>
                        </div>
                        <span class="badge fs-6 p-2 {{ $user->isAdimplente() ? 'bg-success' : 'bg-danger' }} text-white rounded-pill">
                            {{ $user->isAdimplente() ? '✓ Pode jogar' : '✗ Bloqueado' }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-around text-center mb-3">
                        <div>
                            <p class="mb-0 text-muted small">Pago</p>
                            <h6 class="mb-0 fw-bold text-success">R$ {{ number_format($paidTotal, 2, ',', '.') }}</h6>
                        </div>
                        <div>
                            <p class="mb-0 text-muted small">Pendente</p>
                            <h6 class="mb-0 fw-bold text-warning">R$ {{ number_format($pendingTotal, 2, ',', '.') }}</h6>
                        </div>
                        <div>
                            <p class="mb-0 text-muted small">Vencido</p>
                            <h6 class="mb-0 fw-bold text-danger">R$ {{ number_format($overdueTotal, 2, ',', '.') }}</h6>
                        </div>
                    </div>

                    <div class="progress bg-light rounded-3 mb-2" style="height: 10px;">
                        @php
                            $totalPct = $paidTotal + $pendingTotal + $overdueTotal;
                            $pct = $totalPct > 0 ? round(($paidTotal / $totalPct) * 100) : 0;
                        @endphp
                        <div class="progress-bar bg-{{ $user->isAdimplente() ? 'success' : 'warning' }}"
                             role="progressbar" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Completion -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold text-dark"><i class="ti ti-user-circle me-1"></i>Perfil Completo</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Completude do perfil</span>
                        <span class="fw-bold">{{ $profileCompletion }}%</span>
                    </div>
                    <div class="progress bg-light rounded-3" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar"
                             style="width: {{ $profileCompletion }}%"></div>
                    </div>
                    <p class="text-muted small mt-2 mb-0">
                        Complete seus dados (nome, e-mail, telefone, endereço) no perfil para facilitar o contato.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Minhas Cotisations (últimos 6 meses) -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="ti ti-calendar me-1"></i>Fluxo de Pagamentos (6 Meses)</h6>
                </div>
                <div class="card-body">
                    <div id="memberCashflowChart" style="height: 260px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Minhas Mensalidades Recentes -->
    <div class="row g-3">
        <div class="col-12">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="ti ti-file me-1"></i>Minhas Mensalidades Recentes</h6>
                </div>
                <div class="card-body p-0">
                    @if ($myCotisationsList->isEmpty())
                        <div class="p-4 text-center text-muted">
                            <i class="ti ti-inbox fs-1 mb-3 opacity-25"></i>
                            <p class="mb-0">Nenhuma cotização encontrada.</p>
                        </div>
                    @else
                        <div class="table-responsive border-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 small text-muted fw-semibold">Ciclo</th>
                                        <th class="border-0 small text-muted fw-semibold text-end">Valor</th>
                                        <th class="border-0 small text-muted fw-semibold">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($myCotisationsList as $cotisation)
                                        <tr>
                                            <td class="border-0 small">{{ $cotisation->cycle }}</td>
                                            <td class="border-0 small text-end fw-bold">R$ {{ number_format($cotisation->amount, 2, ',', '.') }}</td>
                                            <td class="border-0 small">
                                                @php
                                                    $statusLabels = [
                                                        0 => ['label' => 'Pendente', 'class' => 'text-warning'],
                                                        1 => ['label' => 'Pago', 'class' => 'text-success'],
                                                        2 => ['label' => 'Vencido', 'class' => 'text-danger'],
                                                        3 => ['label' => 'Rejeitado', 'class' => 'text-secondary'],
                                                    ];
                                                    $st = $statusLabels[$cotisation->status] ?? ['label' => 'Desconhecido', 'class' => 'text-secondary'];
                                                @endphp
                                                <span class="badge bg-{{ $st['class'] }} fs-7 p-2">{{ $st['label'] }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
    <script>
        // Gráfico de fluxo de pagamentos (últimos 6 meses)
        const labels = @json($cashflowLabels);
        const values = @json($cashflowValues);

        new ApexCharts(document.querySelector("#memberCashflowChart"), {
            chart: {
                type: 'bar',
                height: 260,
                toolbar: { show: false },
                fontFamily: 'inherit',
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '60%',
                }
            },
            dataLabels: { enabled: false },
            colors: ['#04A9F5'],
            series: [{ name: 'Pago', data: values }],
            xaxis: {
                categories: labels,
                labels: { style: { fontSize: '12px' } },
            },
            yaxis: {
                labels: {
                    formatter: (val) => 'R$ ' + val.toLocaleString('pt-BR'),
                },
            },
            grid: {
                borderColor: '#f1f1f1',
                strokeDashArray: 4,
            },
        }).render();
    </script>
@endsection
