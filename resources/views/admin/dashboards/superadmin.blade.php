@extends('layouts.main')

@section('title', 'Superadmin Dashboard')
@section('breadcrumb-item', 'Dashboard')
@section('breadcrumb-item-active', 'Visão Geral')

@section('css')
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Row 1: Financial cards --}}
    <div class="row g-3">

        {{-- Entradas --}}
        <div class="col-md-6 col-xxl-3">
            <div class="card statistics-card-1 overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="avtar avtar-s bg-light-success"><i class="ph-duotone ph-arrow-circle-up f-20"></i></div>
                        <span class="badge bg-light-success">Entradas</span>
                    </div>
                    <h4 class="mt-3 mb-1 fw-semibold">
                        {{ number_format($financials['totalInflow'], 2, ',', '.') }}
                        <small class="text-muted">BRL</small>
                    </h4>
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        <span class="badge bg-light-secondary">Mensalidades: {{ number_format($financials['memberships'], 2, ',', '.') }}</span>
                        <span class="badge bg-light-secondary">Aportes: {{ number_format($financials['cotisations'], 2, ',', '.') }}</span>
                        <span class="badge bg-light-secondary">Diárias: {{ number_format($financials['contributions'], 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Saídas --}}
        <div class="col-md-6 col-xxl-3">
            <div class="card statistics-card-1 overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="avtar avtar-s bg-light-danger"><i class="ph-duotone ph-arrow-circle-down f-20"></i></div>
                        <span class="badge bg-light-danger">Saídas</span>
                    </div>
                    <h4 class="mt-3 mb-1 fw-semibold">
                        {{ number_format($financials['totalOutflow'], 2, ',', '.') }}
                        <small class="text-muted">BRL</small>
                    </h4>
                    <p class="text-sm text-muted mt-2 mb-0">Total de despesas registradas</p>
                </div>
            </div>
        </div>

        {{-- Saldo --}}
        <div class="col-md-6 col-xxl-3">
            <div class="card statistics-card-1 overflow-hidden">
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
            <div class="card statistics-card-1 overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="avtar avtar-s bg-light-primary"><i class="ph-duotone ph-users f-20"></i></div>
                        <span class="badge bg-light-primary">Sócios</span>
                    </div>
                    <h4 class="mt-3 mb-1 fw-semibold">{{ $users['total'] }}</h4>
                    <div class="d-flex gap-1 mt-2">
                        <span class="badge bg-light-success">Ativos: {{ $users['active'] }}</span>
                        <span class="badge bg-light-danger">Inativos: {{ $users['inactive'] }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Row 2: Cashflow chart + Composition --}}
    <div class="row g-3 mt-1">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Fluxo de Caixa (Entradas Consolidadas)</h5>
                    <select id="cashflow-range" class="form-select rounded-3 form-select-sm w-auto">
                        <option value="30">Últimos 30 Dias</option>
                        <option value="180" selected>Últimos 6 Meses</option>
                        <option value="365">Últimos 12 Meses</option>
                    </select>
                </div>
                <div class="card-body">
                    <div id="cashflow-bar-chart"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Composição das Entradas</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    <div id="composition-donut-chart" class="mx-auto mt-2"></div>
                    <ul class="list-group mt-3 list-group-flush">
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

    {{-- Row 3: Associations --}}
    <div class="row">
        <div class="col-12">
            <div class="card table-card mt-4">
                <div class="card-header d-flex align-items-center justify-content-between py-3">
                    <h5 class="mb-0">Clubes por Receita</h5>
                    <a href="{{ route('admin.associations.index') }}" class="btn btn-sm btn-link-primary">
                        Ver Todos os Clubes
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Clube</th>
                                    <th>Sócios</th>
                                    <th>Receitas (Mensalidades + Aportes + Diárias)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topAssociations ?? [] as $assoc)
                                    <tr>
                                        <td>
                                            <h6 class="mb-0">{{ $assoc['name'] }}</h6>
                                        </td>
                                        <td>{{ $assoc['members'] }}</td>
                                        <td>{{ number_format($assoc['revenue'], 2, ',', '.') }} BRL</td>
                                        <td>
                                            <span class="badge bg-light-{{ $assoc['status_color'] }}">
                                                {{ $assoc['status_label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Nenhum clube registrado.
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

        // Fluxo de Caixa (entradas consolidadas)
        const cashflowData = @json($cashflowData);

        const cashflowChart = new ApexCharts(document.querySelector("#cashflow-bar-chart"), {
            chart: {
                type: 'bar',
                height: 285,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 4
                }
            },
            dataLabels: {
                enabled: false
            },
            colors: ['var(--bs-primary)'],
            series: cashflowData['180'].series,
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
                series: cashflowData[range].series,
                xaxis: {
                    categories: cashflowData[range].categories
                }
            });
        });
    </script>

@endsection
