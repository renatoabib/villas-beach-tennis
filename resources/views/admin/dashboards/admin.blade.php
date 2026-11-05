@extends('layouts.main')

@section('title', 'Admin Dashboard')
@section('breadcrumb-item', 'Dashboard')
@section('breadcrumb-item-active', 'Gestão do Clube')

@section('css')
@endsection

@section('content')
    <div class="row align-items-center mb-3">
        <div class="col-md-8">
            <p class="text-muted mb-0">
                Gerencie sócios, finanças e atividades do clube
                <strong>{{ $association->name }}</strong>.
            </p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('admin.membres.create') }}" class="btn btn-primary d-inline-flex align-items-center">
                <i class="ti ti-user-plus me-2"></i>Novo Sócio
            </a>
        </div>
    </div>

    {{-- Row 1: Financial cards --}}
    <div class="row g-3">

        {{-- Entradas --}}
        <div class="col-md-6 col-xxl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="avtar avtar-s bg-light-success"><i class="ph-duotone ph-arrow-circle-up f-20"></i></div>
                        <span class="badge bg-light-success">Entradas — Caixa (histórico)</span>
                    </div>
                    <h4 class="mt-3 mb-1 fw-semibold">
                        {{ number_format($financials['totalInflow'], 2, ',', '.') }}
                        <small class="text-muted">BRL</small>
                    </h4>
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        <span class="badge bg-light-secondary">Mensalidades ({{ $financials['membershipsCount'] }} pagas): {{ number_format($financials['memberships'], 2, ',', '.') }}</span>
                        <span class="badge bg-light-secondary">Aportes: {{ number_format($financials['cotisations'], 2, ',', '.') }}</span>
                        <span class="badge bg-light-secondary">Diárias ({{ $financials['contributionsCount'] }}): {{ number_format($financials['contributions'], 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Saídas --}}
        <div class="col-md-6 col-xxl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="avtar avtar-s bg-light-danger"><i class="ph-duotone ph-arrow-circle-down f-20"></i></div>
                        <span class="badge bg-light-danger">Saídas</span>
                    </div>
                    <h4 class="mt-3 mb-1 fw-semibold">
                        {{ number_format($financials['totalOutflow'], 2, ',', '.') }}
                        <small class="text-muted">BRL</small>
                    </h4>
                    <p class="text-sm text-muted mt-2 mb-0">Total pago do caixa (histórico)</p>
                </div>
            </div>
        </div>

        {{-- Saldo --}}
        <div class="col-md-6 col-xxl-3">
            <div class="card">
                <div class="card-body">
                    @php $saldoClass = $financials['delta'] >= 0 ? 'success' : 'danger'; @endphp
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="avtar avtar-s bg-light-{{ $saldoClass }}"><i class="ph-duotone ph-wallet f-20"></i></div>
                        <span class="badge bg-light-{{ $saldoClass }}">Saldo Líquido</span>
                    </div>
                    <h4 class="mt-3 mb-1 fw-semibold text-{{ $saldoClass }}">
                        {{ number_format($financials['delta'], 2, ',', '.') }}
                        <small class="text-muted">BRL</small>
                    </h4>
                    <p class="text-sm text-muted mt-2 mb-0">Entradas menos saídas (caixa)</p>
                </div>
            </div>
        </div>

        {{-- Sócios --}}
        <div class="col-md-6 col-xxl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="avtar avtar-s bg-light-primary"><i class="ph-duotone ph-users f-20"></i></div>
                        <span class="badge bg-light-primary">Sócios</span>
                    </div>
                    <h4 class="mt-3 mb-1 fw-semibold">{{ $users['total'] }}</h4>
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        <span class="badge bg-light-success">Ativos: {{ $users['active'] }}</span>
                        <span class="badge bg-light-danger">Inativos: {{ $users['inactive'] }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Row 2: Chart + Recent + Actions --}}
    <div class="row g-3 mt-1">

        {{-- LEFT --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Fluxo de Caixa (Entradas × Saídas)</h5>
                    <select id="cashflow-range" class="form-select rounded-3 form-select-sm w-auto">
                        <option value="30">Últimos 30 Dias</option>
                        <option value="180" selected>Últimos 6 Meses</option>
                        <option value="365">Últimos 12 Meses</option>
                    </select>
                </div>
                <div class="card-body">
                    <div id="association-cotisations-chart"></div>
                </div>
            </div>

            <div class="card table-card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Últimas Mensalidades</h5>
                    <a href="{{ route('admin.memberships.index') }}" class="btn btn-sm btn-link-primary">
                        Ver Todas
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <tbody>
                                @forelse($recentMemberships as $membership)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avtar avtar-s bg-light-primary">
                                                    <i class="ti ti-user"></i>
                                                </div>
                                                <h6 class="mb-0 ms-3">
                                                    {{ $membership->user?->name ?? 'Desconhecido' }}
                                                </h6>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ number_format($membership->amount, 2, ',', '.') }} BRL</span>
                                            @php
                                                $statusColors = [
                                                    'paid' => 'bg-light-success',
                                                    'pending' => 'bg-light-warning',
                                                    'overdue' => 'bg-light-danger',
                                                    'rejected' => 'bg-light-secondary',
                                                ];
                                                $statusLabels = [
                                                    'paid' => 'Pago',
                                                    'pending' => 'Pendente',
                                                    'overdue' => 'Vencido',
                                                    'rejected' => 'Rejeitado',
                                                ];
                                            @endphp
                                            <span class="badge {{ $statusColors[$membership->status] ?? 'bg-light' }} ms-2">
                                                {{ $statusLabels[$membership->status] ?? ucfirst($membership->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end text-muted">
                                            <small>{{ $membership->created_at->diffForHumans() }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">
                                            Nenhuma mensalidade lançada ainda.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Ações Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.memberships.create') }}"
                            class="btn btn-outline-primary d-inline-flex align-items-center justify-content-center">
                            <i class="ti ti-wallet me-2"></i>Nova Mensalidade
                        </a>
                        <a href="{{ route('admin.cotisations.create') }}"
                            class="btn btn-outline-success d-inline-flex align-items-center justify-content-center">
                            <i class="ti ti-currency-dollar me-2"></i>Novo Aporte
                        </a>
                        <a href="{{ route('admin.contributions.create') }}"
                            class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center">
                            <i class="ti ti-cash me-2"></i>Nova Diária
                        </a>
                        <a href="{{ route('admin.events.create') }}"
                            class="btn btn-outline-warning d-inline-flex align-items-center justify-content-center">
                            <i class="ti ti-calendar-plus me-2"></i>Novo Evento
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mt-4 h-100">
                <div class="card-header">
                    <h5 class="mb-0">Composição das Entradas</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <div id="composition-donut-chart" class="mx-auto mt-2"></div>
                    <ul class="list-group list-group-flush mt-3">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="d-inline-flex align-items-center"><i class="ti ti-circle-filled text-success me-2"></i>Mensalidades</span>
                            <span class="fw-semibold">{{ number_format($financials['memberships'], 2, ',', '.') }} BRL</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="d-inline-flex align-items-center"><i class="ti ti-circle-filled text-warning me-2"></i>Aportes</span>
                            <span class="fw-semibold">{{ number_format($financials['cotisations'], 2, ',', '.') }} BRL</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="d-inline-flex align-items-center"><i class="ti ti-circle-filled text-primary me-2"></i>Diárias</span>
                            <span class="fw-semibold">{{ number_format($financials['contributions'], 2, ',', '.') }} BRL</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="d-inline-flex align-items-center text-danger"><i class="ti ti-circle-filled text-danger me-2"></i>Despesas</span>
                            <span class="fw-semibold text-danger">- {{ number_format($financials['totalOutflow'], 2, ',', '.') }} BRL</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-top">
                            <span class="fw-semibold">Saldo Líquido</span>
                            <span class="fw-bold {{ $financials['delta'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($financials['delta'], 2, ',', '.') }} BRL
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>
    <script>
        // Composição das Entradas (donut)
        const compositionChart = new ApexCharts(document.querySelector("#composition-donut-chart"), {
            chart: {
                type: 'donut',
                height: 200
            },
            series: [
                {{ $financials['memberships'] }},
                {{ $financials['cotisations'] }},
                {{ $financials['contributions'] }}
            ],
            labels: ['Mensalidades', 'Aportes', 'Diárias'],
            colors: ['var(--bs-success)', 'var(--bs-warning)', 'var(--bs-primary)'],
            dataLabels: {
                enabled: false
            },
            legend: {
                show: false
            }
        });
        compositionChart.render();

        // Fluxo de Caixa (entradas x saídas + meta)
        const cashflowData = @json($cashflowData);

        const buildCashflowSeries = (d) => [
            { name: d.series[0].name, type: 'bar', data: d.series[0].data },
            { name: d.series[1].name, type: 'bar', data: d.series[1].data },
            { name: d.targetLabel, type: 'line', data: d.target }
        ];

        const cashflowChart = new ApexCharts(document.querySelector("#association-cotisations-chart"), {
            chart: {
                type: 'bar',
                height: 300,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '45%'
                }
            },
            dataLabels: {
                enabled: false
            },
            colors: ['var(--bs-primary)', 'var(--bs-danger)', 'var(--bs-success)'],
            stroke: {
                width: [0, 0, 3],
                dashArray: [0, 0, 7]
            },
            series: buildCashflowSeries(cashflowData['180']),
            xaxis: {
                categories: cashflowData['180'].categories
            },
            yaxis: {
                labels: {
                    formatter: (val) => `${Number(val).toLocaleString('pt-BR')} BRL`
                }
            }
        });

        cashflowChart.render();

        document.getElementById('cashflow-range').addEventListener('change', function() {
            const range = this.value;
            cashflowChart.updateOptions({
                series: buildCashflowSeries(cashflowData[range]),
                xaxis: {
                    categories: cashflowData[range].categories
                }
            });
        });
    </script>

@endsection
