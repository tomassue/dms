<div>
    <!--begin::Mixed Widget 5-->
    <div class="card card-xxl-stretch">
        <!--begin::Header-->
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1">Calendar</span>
                <span class="text-muted fw-bold fs-7">Over {{ $incoming_requests->count() }} incoming requests</span>
            </h3>
            <div class="card-toolbar">
                <div class="d-flex align-items-center gap-2">

                </div>
            </div>
        </div>
        <!--end::Header-->

        <!--begin::Body-->
        <div class="card-body d-flex flex-column" style="position: relative;">

            <div wire:ignore>
                <div id="incoming_requests_calendar"></div>
            </div>

            <div class="resize-triggers">
                <div class="expand-trigger">
                    <div style="width: 404px; height: 426px;"></div>
                </div>
                <div class="contract-trigger"></div>
            </div>
        </div>
        <!--end::Body-->
    </div>
    <!--end::Mixed Widget 5-->

    <!-- detailsModal -->
    <div class="modal fade" id="detailsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="detailsModalLabel">Details</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="clear"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <h5>Incoming Request</h5>
                        <div class="row">
                            <div class="col-4 fw-bold">Status:</div>
                            <div class="col-8">
                                <span class="badge 
                                @switch(strtolower($incomingRequest->status->name ?? '-'))
                                    @case('pending')
                                        badge-light-danger
                                        @break
                                    @case('processed')
                                        badge-light-primary
                                        @break
                                    @case('forwarded')
                                        badge-light-warning
                                        @break
                                    @case('completed')
                                        badge-light-success
                                        @break
                                    @case('cancelled')
                                        badge-light-dark
                                        @break
                                    @default
                                        badge-light-secondary
                                @endswitch
                                text-capitalize">
                                    {{ $incomingRequest->status->name ?? '-' }}
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4 fw-bold">Forwarded to:</div>
                            <div class="col-8">
                                @foreach($forwarded_divisions as $item)
                                {{ $item['division_name'] }}@if(!$loop->last), @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4 fw-bold">No.:</div>
                            <div class="col-8">{{ $incomingRequest->no ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-4 fw-bold">Office/Brgy/Org:</div>
                            <div class="col-8">{{ $incomingRequest->office_barangay_organization ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-4 fw-bold">Date requested:</div>
                            <div class="col-8">{{ $incomingRequest->formatted_date_requested ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-4 fw-bold">Category:</div>
                            <div class="col-8">{{ $incomingRequest->category->name ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-4 fw-bold">Date and Time:</div>
                            <div class="col-8">{{ $incomingRequest->date_time ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-4 fw-bold">Contact person name:</div>
                            <div class="col-8">{{ $incomingRequest->contact_person_name ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-4 fw-bold">Contact person number:</div>
                            <div class="col-8">{{ $incomingRequest->contact_person_number ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-4 fw-bold">Description:</div>
                            <div class="col-8">{{ $incomingRequest->description ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-4 fw-bold">Remarks:</div>
                            <div class="col-8">{{ $incomingRequest->remarks ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h5>Files</h5>
                        <div class="row">
                            @forelse ($previewFile as $file)
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="bi bi-file-earmark-text me-2"></i> {{ $file->name }}
                                        </h6>
                                        <p class="card-text text-muted">{{ $file->type }}</p>
                                        <a href="#" wire:click="viewFile({{ $file->id }})" class="btn btn-primary btn-sm">Preview</a>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <p class="text-muted">No files available.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="clear">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('show-detailsModal', () => {
        $('#detailsModal').modal('show');
    });

    $wire.on('hide-detailsModal', () => {
        $('#detailsModal').modal('hide');
    });

    /* -------------------------------------------------------------------------- */

    $wire.on('open-file', (url) => {
        window.open(event.detail.url, '_blank'); // Open the signed URL in a new tab
    });

    /* -------------------------------------------------------------------------- */

    document.addEventListener('livewire:initialized', function() {
        var calendarEl = document.getElementById('incoming_requests_calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            themeSystem: 'bootstrap5',
            initialView: 'dayGridMonth',
            height: 650,
            headerToolbar: {
                left: 'dayGridMonth,listWeek,timeGridWeek,timeGridDay',
                center: 'title',
                right: 'prev,today,next' // user can switch between the two
            },
            selectable: true,
            events: @json($incoming_requests),

            views: {
                dayGridMonth: {
                    displayEventTime: false // Hides the time in Month View, allowing colors to apply
                }
            },

            eventDidMount: function(info) {
                info.el.style.backgroundColor = info.event.backgroundColor;
                info.el.style.borderColor = info.event.borderColor;
                info.el.style.color = 'white'; // Ensures text is visible
            },


            eventContent: function(arg) {
                let startTime = FullCalendar.formatDate(arg.event.start, {
                    hour: 'numeric',
                    minute: '2-digit',
                    meridiem: 'short'
                });
                let endTime = FullCalendar.formatDate(arg.event.end, {
                    hour: 'numeric',
                    minute: '2-digit',
                    meridiem: 'short'
                });

                // Combine the start and end time in the display
                // let timeHtml = startTime + ' - ' + endTime;

                // return {
                //     html: '<div class="fc-event-time" style="cursor: pointer;">' + timeHtml + '</div><div class="fc-event-title" style="cursor: pointer;">' + arg.event.title + '</div>'
                // };

                let timeHtml = startTime;

                return {
                    html: '<div class="fc-event-time" style="cursor: pointer;">' + timeHtml + '</div><div class="fc-event-title" style="cursor: pointer;">' + arg.event.title + '</div>'
                };
            },

            eventClick: function(info) {
                // Trigger Livewire event to show details
                // $wire.dispatch('show-details', {
                //     key: info.event.id
                // });

                $wire.showDetails(info.event.id);
            },

            dateClick: function(info) {
                // Change to dayGridDay view on date click
                calendar.changeView('timeGridDay', info.dateStr);
            }
        });

        calendar.render();

        $wire.on('refresh-calendar', (data) => {
            calendar.removeAllEvents();
            calendar.addEventSource(data.meetings); // Use `data.meetings` here
        });

    });
</script>
@endscript