@extends('layouts.main')

@section('title', 'My Dashboard')
@section('breadcrumb-item', 'Member')
@section('breadcrumb-item-active', 'My Dashboard')

@section('css')
@endsection

@section('content')
<div class="row g-3">

    <!-- Cotisation Status -->
    <div class="col-md-6 col-xl-4">
        <div class="card h-100 d-flex flex-column">
            <div class="card-header">
                <h5 class="mb-0"><i class="ph-duotone ph-wallet me-1"></i> My Cotisation Status (This Year)</h5>
            </div>
            <div class="card-body d-flex flex-column justify-content-between">
                @php
                    $myPaid = $myCotisations['paid'] ?? 0;
                    $myTotalAll = $myPaid + ($myCotisations['pending'] ?? 0) + ($myCotisations['overdue'] ?? 0) + ($myCotisations['rejected'] ?? 0);
                    $myPaidPercent = $myTotalAll > 0 ? round(($myPaid / $myTotalAll) * 100, 1) : 0;
                @endphp
                <div class="my-n4 text-center mx-auto" style="max-width: 150px">
                    <div id="total-earning-chart-1" data-percent="{{ $myPaidPercent }}"></div>
                </div>
                <div>
                    <div class="bg-body mt-4 py-2 px-3 rounded d-flex align-items-center justify-content-between">
                        <p class="mb-0">
                            <i class="ph-duotone ph-circle text-success f-12"></i> Total Paid
                        </p>
                        <h5 class="mb-0">
                            BRL {{ number_format($myCotisations['paid'] ?? 0, 2, ',', '.') }}
                        </h5>
                    </div>
                    <div class="bg-body mt-2 py-2 px-3 rounded d-flex align-items-center justify-content-between">
                        <p class="mb-0">
                            <i class="ph-duotone ph-circle text-danger f-12"></i> Total Unpaid
                        </p>
                        <h5 class="mb-0">
                            BRL {{
                                number_format(
                                    ($myCotisations['pending'] ?? 0)
                                    + ($myCotisations['overdue'] ?? 0)
                                    + ($myCotisations['rejected'] ?? 0), 2
                                )
                            }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Completeness -->
    <div class="col-md-6 col-xl-4">
        <div class="card h-100 d-flex flex-column">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><i class="ph-duotone ph-user-circle-gear me-1"></i> Profile Completeness</h5>
                <a href="{{ route('profile.index') }}" class="btn btn-sm btn-link-primary">
                    Edit Profile <i class="ph ph-arrow-square-out ms-1"></i>
                </a>
            </div>
            <div class="card-body d-flex flex-column justify-content-between">
                <div class="d-flex flex-column align-items-center justify-content-center mb-3 gap-2">
                    <h3 class="mb-0">
                        @if (auth()->user()->is_active)
                            <span class="text-success">Ativo</span>
                        @else
                            <span class="text-danger">Inativo</span>
                        @endif
                    </h3>
                    <p class="text-muted mb-0">
                        Sócio desde {{ auth()->user()->created_at->format('m/Y') }}
                    </p>
                </div>
                <div class="text-center mt-3">
                    <p class="text-muted">Mantenha seus dados atualizados no perfil!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Meetings & Events -->
    <div class="col-xl-4">
        <div class="card h-100 d-flex flex-column">
            <div class="card-header">
                <h5 class="mb-0"><i class="ph-duotone ph-calendar-check me-1"></i> My Upcoming Meetings & Events</h5>
            </div>
            <ul class="list-group list-group-flush flex-grow-1">
                @forelse ($upcomingMeetingsEvents as $item)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-1">{{ $item['title'] }}</h6>
                            <small class="text-muted">{{ $item['time_diff'] }}</small>
                        </div>
                        <p class="mb-1 text-muted">{{ $item['date'] }} - {{ $item['location'] }}</p>
                        <small><a href="{{ $item['link'] }}">View Details</a></small>
                    </li>
                @empty
                    <li class="list-group-item text-center text-muted">No upcoming meetings or events.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Cotisation History Chart -->
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0"><i class="ph-duotone ph-chart-bar me-1"></i> My Cotisation History</h5>
                <select id="cotisation-range" class="form-select form-select-sm w-auto border-0 shadow-none">
                    <option value="6">Last 6 months</option>
                    <option value="12" selected>Last 12 months</option>
                </select>
            </div>
            <div class="card-body">
                <p class="mb-0">Total paid this year</p>
                <h4>BRL {{ number_format($myCotisations['paid'] ?? 0, 2, ',', '.') }}</h4>
                <div id="monthly-report-graph"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ URL::asset('build/js/plugins/apexcharts.min.js') }}"></script>

<script>
    // Gauge: % de aportes pagos no ano (real)
    const myPaidPercent = parseFloat(document.getElementById('total-earning-chart-1').dataset.percent) || 0;
    new ApexCharts(document.querySelector("#total-earning-chart-1"), {
        chart: { height: 150, type: 'radialBar' },
        series: [myPaidPercent],
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

<script>
    const chartData = {
        6: {
            categories: @json(array_slice($cashflowLabels, -6)),
            series: [{
                name: 'Paid',
                data: @json(array_slice($cashflowValues->toArray(), -6))

            }]
        },
        12: {
            categories: @json($cashflowLabels),
            series: [{
                name: 'Paid',
                data: @json($cashflowValues)
            }]
        }
    };

    const options = {
        chart: {
            type: 'bar',
            height: 300,
            toolbar: { show: false }
        },
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: false,
                columnWidth: '40%'
            }
        },
        dataLabels: { enabled: false },
        xaxis: { categories: chartData[12].categories },
        series: chartData[12].series,
        colors: ['#16a34a']
    };

    const cotisationChart = new ApexCharts(document.querySelector("#monthly-report-graph"), options);
    cotisationChart.render();

    document.getElementById('cotisation-range').addEventListener('change', function () {
        const val = this.value;
        cotisationChart.updateOptions({
            xaxis: { categories: chartData[val].categories },
            series: chartData[val].series
        });
    });
</script>

@endsection
