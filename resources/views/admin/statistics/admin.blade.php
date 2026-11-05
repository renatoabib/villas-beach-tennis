@extends('layouts.main')

@section('title', 'Admin Dashboard')
@section('breadcrumb-item', 'Management')
@section('breadcrumb-item-active', 'Association Dashboard')

@section('css')
@endsection

@section('content')
<div class="row g-3">
    <!-- Member Growth -->
    <div class="col-md-6 col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    <i class="ph-duotone ph-users-four me-1"></i> 
                    Member Registration Growth
                </h5>
                <select id="member-growth-range" class="form-select form-select-sm w-auto border-0 shadow-none">
                    <option value="6">Last 6 months</option>
                    <option value="12" selected>This Year</option>
                    <option value="all">All Time</option>
                </select>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-1">
                    <h3 class="mb-0">
                        {{ $totalMembers }} <small class="text-muted">/ total members</small>
                    </h3>
                    <span class="badge bg-light-success ms-2">
                        +{{ $memberGrowthThisYear ?? 0 }} since last year
                    </span>
                </div>
                <p>New member registrations for your association over time.</p>
                <div id="customer-rate-graph"></div>
            </div>
        </div>
    </div>

    <!-- Membership Status -->
    <div class="col-md-6 col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="ph-duotone ph-wallet me-1"></i> 
                    Status das Mensalidades (R$ 50/mês)
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center" style="max-width: 150px; margin: auto;">
                    <div id="total-earning-chart-1" data-percent="{{ $membershipsPaidPercent ?? 0 }}"></div>
                </div>
                <div class="bg-body mt-4 py-2 px-3 rounded d-flex justify-content-between">
                    <p class="mb-0">
                        <i class="ph-duotone ph-circle text-success f-12"></i> Pago
                    </p>
                    <h5 class="mb-0">{{ number_format($membershipsPaidSum ?? 0, 2, ',', '.') }} BRL</h5>
                </div>
                <div class="bg-body mt-2 py-2 px-3 rounded d-flex justify-content-between">
                    <p class="mb-0">
                        <i class="ph-duotone ph-circle text-warning f-12"></i> Pendente
                    </p>
                    <h5 class="mb-0">{{ number_format($membershipsPendingSum ?? 0, 2, ',', '.') }} BRL</h5>
                </div>
                <div class="bg-body mt-2 py-2 px-3 rounded d-flex justify-content-between">
                    <p class="mb-0">
                        <i class="ph-duotone ph-circle text-danger f-12"></i> Vencido / Rejeitado
                    </p>
                    <h5 class="mb-0">{{ number_format($membershipsOverdueRejectedSum ?? 0, 2, ',', '.') }} BRL</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Cotisations -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="ph-duotone ph-currency-circle-dollar me-1"></i> 
                    Monthly Cotisations
                </h5>
            </div>
            <div class="card-body">
                <p>Total paid cotisations (last 6 months)</p>
                <h4 class="fw-semibold">
                    {{ number_format(array_sum($cashflowValues->toArray()), 2, ',', '.') }} BRL
                </h4>
                <div id="monthly-report-graph"></div>
            </div>
        </div>
    </div>

    <!-- Monthly Expenses -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="ph-duotone ph-credit-card me-1"></i> 
                    Monthly Expenses
                </h5>
            </div>
            <div class="card-body">
                <p>Recorded expenses (last 6 months)</p>
                <h4 class="fw-semibold">
                    {{ number_format($totalOutflowMAD, 2, ',', '.') }} BRL
                </h4>
                <div id="yearly-summary-chart" data-expense-labels='@json($expensesLabels)' data-expense-values='@json($expensesValues)'></div>
            </div>
        </div>
    </div>

    <!-- Diárias e Aportes -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="ph-duotone ph-handshake me-1"></i> 
                    Diárias e Aportes (Este Ano)
                </h5>
                <a href="{{ route('admin.contributions.create') }}" class="btn btn-sm btn-primary">
                    Nova Diária
                </a>
            </div>
            <div class="card-body">
                <div id="overview-bar-chart" data-contrib-labels='@json($contributionsLabels)' data-contrib-values='@json($contributionsValues)'></div>
                <div class="row text-center mt-4">
                    <div class="col-md-6">
                        <p class="mb-0">
                            <i class="ph-duotone ph-circle text-primary f-12"></i> 
                            Diárias (não-sócios)
                        </p>
                        <h5 class="mb-0">
                            {{ number_format($totalContributions ?? 0, 2, ',', '.') }} BRL
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-0">
                            <i class="ph-duotone ph-circle text-warning f-12"></i> 
                            Aportes Pagos
                        </p>
                        <h5 class="mb-0">
                            {{ number_format($cotisationsPaidMAD, 2, ',', '.') }} BRL
                        </h5>
                    </div>
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

    const memberChart = new ApexCharts(document.querySelector("#customer-rate-graph"), {
        chart: {
            type: 'area',
            height: 300,
            toolbar: { show: false }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth' },
        xaxis: {
            categories: memberGrowthData[12].categories
        },
        series: [{
            name: 'Registrations',
            data: memberGrowthData[12].data
        }],
        colors: ['#2563eb']
    });

    memberChart.render();

    document.getElementById("member-growth-range").addEventListener("change", function () {
        const selected = this.value === 'all' ? 'all' : parseInt(this.value);
        memberChart.updateOptions({
            xaxis: { categories: memberGrowthData[selected].categories },
            series: [{
                name: 'Registrations',
                data: memberGrowthData[selected].data
            }]
        });
    });
</script>

<script>
    // Monthly cotisation graph
    const cashflowData = {
        categories: @json($cashflowLabels),
        series: [{
            name: 'Cotisations',
            data: @json($cashflowValues)
        }]
    };

    const cotisationChart = new ApexCharts(document.querySelector("#monthly-report-graph"), {
        chart: { type: 'bar', height: 250, toolbar: { show: false } },
        plotOptions: { bar: { borderRadius: 4 } },
        dataLabels: { enabled: false },
        colors: ['#2563eb'],
        series: cashflowData.series,
        xaxis: { categories: cashflowData.categories },
        yaxis: {
            labels: {
                formatter: (val) => `${Number(val).toLocaleString('pt-BR')} BRL`
            }
        }
    });

    cotisationChart.render();

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

    // Diárias por mês (real)
    const contribChartEl = document.getElementById('overview-bar-chart');
    new ApexCharts(contribChartEl, {
        chart: { type: 'bar', height: 200, toolbar: { show: false } },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
        dataLabels: { enabled: false },
        colors: ['#04A9F5'],
        series: [{ name: 'Diárias', data: JSON.parse(contribChartEl.dataset.contribValues) }],
        xaxis: { categories: JSON.parse(contribChartEl.dataset.contribLabels) },
        yaxis: {
            labels: { formatter: (val) => Number(val).toLocaleString('pt-BR') }
        }
    }).render();

    // Despesas por mês (real)
    const expenseChartEl = document.getElementById('yearly-summary-chart');
    new ApexCharts(expenseChartEl, {
        chart: { type: 'bar', height: 200, toolbar: { show: false } },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
        dataLabels: { enabled: false },
        colors: ['#dc2626'],
        series: [{ name: 'Despesas', data: JSON.parse(expenseChartEl.dataset.expenseValues) }],
        xaxis: { categories: JSON.parse(expenseChartEl.dataset.expenseLabels) },
        yaxis: {
            labels: { formatter: (val) => Number(val).toLocaleString('pt-BR') }
        }
    }).render();
</script>

@endsection
