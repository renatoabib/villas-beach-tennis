@extends('layouts.main')

@section('title', 'Global Overview')
@section('breadcrumb-item', 'Super Admin')
@section('breadcrumb-item-active', 'Global Statistique')

@section('css')
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="ph-duotone ph-trend-up me-1"></i> Platform-Wide Member Growth</h5>
                    <select id="member-growth-range" class="form-select form-select-sm w-auto border-0 shadow-none">
                        <option value="3">Last 3 months</option>
                        <option value="6" selected>Last 6 months</option>
                        <option value="12">This Year</option>
                    </select>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-1">
                        <h3 class="mb-0">
                            {{ number_format($totalActiveMembers, 0, ',', '.') }}
                            <small class="text-muted">/ total active members</small>
                        </h3>
                        <span class="badge bg-light-success ms-2">
                            +{{ number_format($memberGrowthThisYear, 0, ',', '.') }} this year
                        </span>
                    </div>
                    <p>New member registrations across all associations.</p>
                    <div id="customer-rate-graph"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="ph-duotone ph-calculator me-1"></i> Status Global das Mensalidades (R$ 50/mês)</h5>
                </div>
                <div class="card-body">
                    <div class="my-n4 text-center mx-auto" style="max-width: 150px">
                        <div id="total-earning-chart-1" data-percent="{{ $membershipsPaidPercent ?? 0 }}"></div>
                    </div>
                    <div class="bg-body mt-4 py-2 px-3 rounded d-flex align-items-center justify-content-between">
                        <p class="mb-0"><i class="ph-duotone ph-circle text-success f-12"></i> Pago</p>
                        <h5 class="mb-0 ms-1">{{ number_format($membershipsPaidSum ?? 0, 0, ',', '.') }} BRL</h5>
                    </div>
                    <div class="bg-body mt-1 py-2 px-3 rounded d-flex align-items-center justify-content-between">
                        <p class="mb-0"><i class="ph-duotone ph-circle text-warning f-12"></i> Pendente</p>
                        <h5 class="mb-0 ms-1">{{ number_format($membershipsPendingSum ?? 0, 0, ',', '.') }} BRL</h5>
                    </div>
                    <div class="bg-body mt-1 py-2 px-3 rounded d-flex align-items-center justify-content-between">
                        <p class="mb-0"><i class="ph-duotone ph-circle text-danger f-12"></i> Vencido / Rejeitado</p>
                        <h5 class="mb-0 ms-1">{{ number_format($membershipsOverdueRejectedSum ?? 0, 0, ',', '.') }} BRL</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="ph-duotone ph-bar-chart me-1"></i> Receitas por Clube (Mensalidades + Aportes + Diárias)</h5>
                </div>
                <div class="card-body">
                    <div id="monthly-report-graph" style="min-height: 250px;"></div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6 col-xl-4">
                <div class="card h-100" style="min-height: 320px">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="ph-duotone ph-users-three me-1"></i> Users by Role</h5>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div id="project-rating-chart" data-roles='@json($rolesCounts)' style="height: 180px;"></div>
                        <div class="text-center">
                            <div class="d-inline-flex align-items-center m-1">
                                <p class="mb-0"><i class="ph-duotone ph-circle text-success f-12"></i> Sócios</p>
                                <span class="badge bg-light-secondary ms-1">{{ number_format($rolesCounts['member'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-inline-flex align-items-center m-1">
                                <p class="mb-0"><i class="ph-duotone ph-circle text-primary f-12"></i> Admins</p>
                                <span class="badge bg-light-secondary ms-1">{{ number_format($rolesCounts['admin'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-inline-flex align-items-center m-1">
                                <p class="mb-0"><i class="ph-duotone ph-circle text-warning f-12"></i> Board</p>
                                <span class="badge bg-light-secondary ms-1">{{ number_format($rolesCounts['board'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-inline-flex align-items-center m-1">
                                <p class="mb-0"><i class="ph-duotone ph-circle text-danger f-12"></i> Super Admins</p>
                                <span class="badge bg-light-secondary ms-1">{{ number_format($rolesCounts['superadmin'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-4">
                <div class="card h-100" style="min-height: 320px">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="ph-duotone ph-coins me-1"></i> Contributions vs. Platform Expenses</h5>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div id="inflow-outflow-chart" style="height: 180px;"></div>
                        <div class="row text-center mt-3">
                            <div class="col-6">
                                <p class="text-muted mb-1"><i class="ph-duotone ph-circle text-success f-12"></i> Total
                                    Inflow</p>
                                <h4 class="mb-0">{{ number_format($totalInflowMAD, 0, ',', '.') }} BRL</h4>
                            </div>
                            <div class="col-6">
                                <p class="text-muted mb-1"><i class="ph-duotone ph-circle text-danger f-12"></i> Total
                                    Outflow</p>
                                <h4 class="mb-0">{{ number_format($totalOutflowMAD, 0, ',', '.') }} BRL</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card h-100" style="min-height: 320px">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5>
                            <i class="ph-duotone ph-buildings me-1"></i>
                            Clubes Registrados por Ano
                        </h5>
                        <a href="{{ route('admin.associations.index') }}" class="btn btn-sm btn-link-primary">
                            Gerenciar <i class="ti ti-arrow-right"></i>
                        </a>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <p>Total de clubes ativos na plataforma</p>
                            <h3 class="mb-3">
                                {{ number_format($associationsTotal, 0, ',', '.') }}
                                <span class="badge bg-light-success ms-2">
                                    +{{ $associationsGrowthThisYear }} este ano
                                </span>
                            </h3>
                        </div>
                        <div id="overview-bar-chart" class="mt-auto" data-years='@json($associationsByYear)'></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>

    <script>
        const memberGrowthData = @json($memberGrowthChartData);

        let chart = new ApexCharts(document.querySelector("#customer-rate-graph"), {
            chart: {
                type: 'area',
                height: 300,
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            xaxis: {
                categories: memberGrowthData[6].categories
            },
            series: [{
                name: 'Registrations',
                data: memberGrowthData[6].data
            }],
            colors: ['#2563eb']
        });
        chart.render();

        document.getElementById('member-growth-range').addEventListener('change', function() {
            const range = parseInt(this.value);
            chart.updateOptions({
                xaxis: {
                    categories: memberGrowthData[range].categories
                },
                series: [{
                    name: 'Registrations',
                    data: memberGrowthData[range].data
                }]
            });
        });

        const inflowOutflowChart = new ApexCharts(document.querySelector("#inflow-outflow-chart"), {
            chart: {
                type: 'donut',
                height: 180
            },
            series: [{{ $totalInflowMAD }}, {{ $totalOutflowMAD }}],
            labels: ['Inflow', 'Outflow'],
            colors: ['#16a34a', '#dc2626'],
            legend: {
                show: false
            },
            dataLabels: {
                enabled: true,
                formatter: val => val.toFixed(0) + '%'
            }
        });
        inflowOutflowChart.render();

        // Gauge: % de mensalidades pagas (real)
        const paidPercent = parseFloat(document.getElementById('total-earning-chart-1').dataset.percent) || 0;
        new ApexCharts(document.querySelector("#total-earning-chart-1"), {
            chart: { height: 150, type: 'radialBar' },
            series: [paidPercent],
            colors: ['#16a34a'],
            plotOptions: {
                radialBar: {
                    hollow: { size: '55%' },
                    dataLabels: {
                        show: true,
                        name: { show: false },
                        value: { formatter: (val) => val.toFixed(1) + '%', offsetY: 7, fontSize: '18px', color: '#16a34a' }
                    },
                    track: { background: '#f1f5f9' }
                }
            }
        }).render();

        // Receitas por Clube (stacked: Mensalidades + Aportes + Diárias)
        const assocRevenue = @json($associationsRevenue);
        new ApexCharts(document.querySelector("#monthly-report-graph"), {
            chart: { type: 'bar', height: 260, stacked: true, toolbar: { show: false } },
            series: [
                { name: 'Mensalidades', data: assocRevenue.map(a => a.memberships) },
                { name: 'Aportes', data: assocRevenue.map(a => a.cotisations) },
                { name: 'Diárias', data: assocRevenue.map(a => a.contributions) }
            ],
            colors: ['#16a34a', '#f59e0b', '#04A9F5'],
            xaxis: {
                categories: assocRevenue.map(a => a.name),
                labels: { style: { fontSize: '12px' } }
            },
            yaxis: {
                labels: { formatter: (val) => Number(val).toLocaleString('pt-BR') }
            },
            dataLabels: { enabled: false },
            legend: { position: 'top' },
            grid: { strokeDashArray: 4 }
        }).render();

        // Usuários por Papel (donut real)
        const roleCounts = JSON.parse(document.getElementById('project-rating-chart').dataset.roles);
        new ApexCharts(document.querySelector("#project-rating-chart"), {
            chart: { type: 'donut', height: 180 },
            series: [roleCounts.member, roleCounts.admin, roleCounts.board, roleCounts.superadmin],
            labels: ['Sócios', 'Admins', 'Board', 'Super Admins'],
            colors: ['#16a34a', '#2563eb', '#f59e0b', '#dc2626'],
            legend: { show: false },
            dataLabels: { enabled: true, formatter: (val) => val.toFixed(0) }
        }).render();

        // Clubes por Ano (real)
        const assocYears = JSON.parse(document.getElementById('overview-bar-chart').dataset.years);
        new ApexCharts(document.querySelector("#overview-bar-chart"), {
            chart: { type: 'bar', height: 120, toolbar: { show: false }, sparkline: { enabled: true } },
            series: [{ name: 'Clubes', data: assocYears.data }],
            colors: ['#2563eb'],
            plotOptions: { bar: { borderRadius: 2, columnWidth: '60%' } },
            xaxis: { categories: assocYears.labels },
            tooltip: { y: { formatter: (val) => val + ' clubes' } }
        }).render();
    </script>

@endsection
