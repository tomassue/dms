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
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('livewire:navigated', () => {
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
        });
    </script>
</div>