<div>
    <div class="card-header border-0 py-5">
    <h3 class="card-title align-items-start flex-column">
        <div class="d-flex align-items-center">
            <span class="card-label badge-light-info fw-bolder fs-3 mb-1">
                @if(auth()->user()->user_metadata?->division)
                    {{ auth()->user()->user_metadata->division->name }}
                @endif
            </span>
        </div>
        
        <span class="text-muted fw-bold fs-7">Over {{ $incoming_requests->total() }} incoming requests for your division</span>
    </h3>

    
    <!--begin::Row-->
    <div class="row g-5 g-xl-8 justify-content-center">
        <!--begin::Col-->
        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3">
            <div class="card card-dashed">
                <div class="card-header">

                    <h3 class="card-title">Pending Request</h3>
                </div>
                <div class="card-body text-center" style="font-size: 30px;">
                    {{ $pending_incoming_requests->count() }}
                </div>
            </div>
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3">
            <div class="card card-dashed">
                <div class="card-header">
                    <h3 class="card-title">Forwarded Request</h3>
                </div>
                <div class="card-body text-center" style="font-size: 30px;">
                    {{ $forwarded_incoming_requests->count() }}
                </div>
            </div>
        </div>
        <!--end::Col-->

        @php
        //* Hidden because completed requests are not needed at the moment. Just keeping this in case it will be needed in the future.
        @endphp
        <!--begin::Col-->
        <!-- <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3">
            <div class="card card-dashed">
                <div class="card-header">
                    <h3 class="card-title">Completed Request</h3>
                </div>
                <div class="card-body text-center" style="font-size: 50px;">
                    {{ $completed_incoming_requests->count() }}
                </div>
            </div>
        </div> -->
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3">
            <div class="card card-dashed">
                <div class="card-header">
                    <h3 class="card-title">Total Requests</h3>
                </div>
                <div class="card-body text-center" style="font-size: 30px;">
                    {{ $total_incoming_requests }}
                </div>
            </div>
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->
    </div>
    
    <div class="row g-5 g-xl-8 mt-5">
        <div class="col-xl-12">
            <div class="card card-xl-stretch mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">Monthly Request Performance</span>
                        <span class="text-muted fw-bold fs-7">Requests vs. Completed for {{ now()->year }}</span>
                    </h3>
                </div>
                <div class="card-body">
                    <div id="kt_charts_widget_requests" style="height: 350px"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-8 mt-5">
        <div class="col-xl-12">
            <div class="card card-xl-stretch mb-xl-8">
                <div class="card-header border-0 pt-5 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <h3 class="card-title align-items-start flex-column mb-0">
                        <span class="card-label fw-bolder fs-3 mb-1">Weekly Request Performance</span>
                        <span class="text-muted fw-bold fs-7" id="kt_weekly_range_label">{{ $weekly_stats['range_label'] }}</span>
                    </h3>
                    <div class="d-flex align-items-center gap-2">
                        <button
                            type="button"
                            class="btn btn-sm btn-icon btn-light"
                            wire:click="previousWeek"
                            wire:loading.attr="disabled"
                            wire:target="previousWeek,nextWeek"
                            title="Previous week"
                        >
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button
                            type="button"
                            class="btn btn-sm btn-icon btn-light"
                            wire:click="nextWeek"
                            wire:loading.attr="disabled"
                            wire:target="previousWeek,nextWeek"
                            id="kt_weekly_next_btn"
                            {{ $weekOffset >= 0 ? 'disabled' : '' }}
                            title="Next week"
                        >
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="kt_charts_widget_weekly_requests" style="height: 350px"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @script
    <script>
        (() => {
            var element = document.getElementById('kt_charts_widget_requests');

            if (!element) return;

            var options = {
                series: [{
                    name: 'Total Requests',
                    data: @json($monthly_stats['total'])
                }, {
                    name: 'Completed',
                    data: @json($monthly_stats['completed'])
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        borderRadius: 5
                    },
                },
                legend: { show: true },
                dataLabels: { enabled: false },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: @json($monthly_stats['months']),
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: {
                        style: { colors: '#a1a5b7', fontSize: '12px' }
                    }
                },
                yaxis: {
                    labels: {
                        style: { colors: '#a1a5b7', fontSize: '12px' }
                    }
                },
                fill: { opacity: 1 },
                states: {
                    normal: { filter: { type: 'none', value: 0 } },
                    hover: { filter: { type: 'none', value: 0 } },
                    active: { allowMultipleDataPointsSelection: false, filter: { type: 'none', value: 0 } }
                },
                tooltip: {
                    style: { fontSize: '12px' },
                    y: {
                        formatter: function (val) { return val + " requests" }
                    }
                },
                colors: ['#009EF7', '#50CD89'], // Blue for total, Green for completed
                grid: {
                    borderColor: '#eff2f5',
                    strokeDashArray: 4,
                    yaxis: { lines: { show: true } }
                }
            };

            var chart = new ApexCharts(element, options);
            chart.render();

            var weeklyElement = document.getElementById('kt_charts_widget_weekly_requests');

            if (!weeklyElement) return;

            var weeklyOptions = {
                series: [{
                    name: 'Total Requests',
                    data: @json($weekly_stats['total'])
                }, {
                    name: 'Completed',
                    data: @json($weekly_stats['completed'])
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        borderRadius: 5
                    },
                },
                legend: { show: true },
                dataLabels: { enabled: false },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: @json($weekly_stats['days']),
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: {
                        style: { colors: '#a1a5b7', fontSize: '12px' }
                    }
                },
                yaxis: {
                    labels: {
                        style: { colors: '#a1a5b7', fontSize: '12px' }
                    }
                },
                fill: { opacity: 1 },
                states: {
                    normal: { filter: { type: 'none', value: 0 } },
                    hover: { filter: { type: 'none', value: 0 } },
                    active: { allowMultipleDataPointsSelection: false, filter: { type: 'none', value: 0 } }
                },
                tooltip: {
                    style: { fontSize: '12px' },
                    y: {
                        formatter: function (val) { return val + " requests" }
                    }
                },
                colors: ['#009EF7', '#50CD89'], // Blue for total, Green for completed
                grid: {
                    borderColor: '#eff2f5',
                    strokeDashArray: 4,
                    yaxis: { lines: { show: true } }
                }
            };

            var weeklyChart = new ApexCharts(weeklyElement, weeklyOptions);
            weeklyChart.render();

            // Re-render the weekly chart in place when Previous/Next is clicked,
            // instead of reloading the whole page (canGoNext disables Next at the current week).
            $wire.on('weekly-stats-updated', (event) => {
                weeklyChart.updateOptions({
                    xaxis: { categories: event.days }
                });
                weeklyChart.updateSeries([
                    { name: 'Total Requests', data: event.total },
                    { name: 'Completed', data: event.completed }
                ]);

                var rangeLabelEl = document.getElementById('kt_weekly_range_label');
                if (rangeLabelEl) rangeLabelEl.textContent = event.rangeLabel;

                var nextBtn = document.getElementById('kt_weekly_next_btn');
                if (nextBtn) nextBtn.disabled = !event.canGoNext;
            });
        })();
    </script>
    @endscript
</div>