<div>
    <div class="row col-xxl-12" style="display: {{ $apoMeetingId && ($show == false) ? '' : 'none' }};">
        <div class="row g-5 g-xl-8 col-xxl-4">
            <!-- begin::Meeting Details -->
            <div class="col-xxl-12">
                <!--begin::Mixed Widget 5-->
                <div class="card">
                    <!--begin::Header-->
                    <div class="card-header border-0 py-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder fs-3 mb-1">Meeting Details</span>
                            <span class="text-muted fw-bold fs-7"></span>
                        </h3>
                        <div class="card-toolbar">
                            <div class="d-flex align-items-center gap-2">
                                <!-- begin::Menu -->
                                <a href="#" class="btn btn-icon btn-info" wire:click="clear" title="Go Back">
                                    <i class="bi bi-arrow-left-short"></i>
                                </a>
                                <!-- end::Menu -->
                            </div>
                        </div>
                    </div>
                    <!--end::Header-->

                    <!--begin::Body-->
                    <div class="card-body d-flex flex-column" style="position: relative; padding-top: unset;">
                        <div id="kt_customer_view_details" class="collapse show">
                            <div class="py-5 fs-6">
                                <!--begin::Details item-->
                                <div class="fw-bolder mt-5">Date</div>
                                <div class="text-gray-600">{{ $apo_meeting->formatted_date }}</div>
                                <!--begin::Details item-->
                                <!--begin::Details item-->
                                <div class="fw-bolder mt-5">Description</div>
                                <div class="text-gray-600">{{ $apo_meeting->description }}</div>
                                <!--begin::Details item-->
                                <!--begin::Details item-->
                                <div class="fw-bolder mt-5">Time</div>
                                <div class="text-gray-600">{{ $apo_meeting->time_range }}</div>
                                <!--begin::Details item-->
                                <!--begin::Details item-->
                                <div class="fw-bolder mt-5">Venue</div>
                                <div class="text-gray-600">{{ $apo_meeting->venue }}</div>
                                <!--begin::Details item-->
                                <!--begin::Details item-->
                                <div class="fw-bolder mt-5">Prepared by</div>
                                <div class="text-gray-600">{{ $apo_meeting->preparedBy->name }}</div>
                                <!--begin::Details item-->
                                <!--begin::Details item-->
                                <div class="fw-bolder mt-5">Approved by</div>
                                <div class="text-gray-600">{{ $apo_meeting->approved_by ?? '-' }}</div>
                                <!--begin::Details item-->
                                <!--begin::Details item-->
                                <div class="fw-bolder mt-5">Noted by</div>
                                <div class="text-gray-600">{{ $apo_meeting->noted_by ?? '-'  }}</div>
                                <!--begin::Details item-->
                            </div>
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
            </div>
            <!-- end::Meeting Details -->
        </div>

        <div class="row g-5 g-xl-8 col-xxl-8">
            <!-- begin::Meeting Minutes -->
            <div class="col-xxl-12">
                <!--begin::Mixed Widget 5-->
                <div class="card card-xxl-stretch">
                    <!--begin::Header-->
                    <div class="card-header border-0 py-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder fs-3 mb-1">Minutes of a Meeting</span>
                            <span class="text-muted fw-bold fs-7">Over {{ $minutes_of_meeting->count() }} entries</span>
                        </h3>
                        <div class="card-toolbar">
                            <div class="d-flex align-items-center gap-2">
                                <!-- begin::Menu -->
                                @can('minutesOfMeeting.create')
                                <a href="#" class="btn btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#meetingModal"><i class="bi bi-plus-circle"></i></a>
                                @endcan
                                <!-- end::Menu -->
                            </div>
                        </div>
                    </div>
                    <!--end::Header-->

                    <!--begin::Body-->
                    <div class="card-body d-flex flex-column" style="position: relative;">
                        <div class="table-responsive" wire:loading.class="opacity-50" wire:target.except="saveOutgoing">
                            <table class="table align-middle table-hover table-rounded border gy-7 gs-7">
                                <thead>
                                    <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200 bg-light">
                                        <th>Activities</th>
                                        <th>Point Person</th>
                                        <th>Expected Output</th>
                                        <th>Agreements</th>
                                        @can('minutesOfMeeting.update')
                                        <th class="text-center">Actions</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($minutes_of_meeting as $item)
                                    <td>{{ $item->activity }}</td>
                                    <td>{{ $item->point_person }}</td>
                                    <td>{{ $item->expected_output }}</td>
                                    <td>{{ $item->agreements }}</td>
                                    <td class="text-center" wire:loading.class="pe-none">
                                        <div class="btn-group" role="group" aria-label="Actions">
                                            @can('minutesOfMeeting.update')
                                            <button type="button" class="btn btn-icon btn-sm btn-secondary" title="Edit" wire:click="editMeeting({{ $item->id }})">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            @endcan
                                            @can('minutesOfMeeting.read')
                                            <button type="button" class="btn btn-icon btn-sm btn-info" title="Read" wire:click="readMinutesOfMeeting({{ $item->id }})">
                                                <i class="bi bi-hourglass"></i>
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No records found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
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
            </div>
            <!-- end::Meeting Minutes -->
        </div>
    </div>

</div>

@script
<script>
    $wire.on('read-minutes-of-meeting', (meetingId) => {
        console.log(meetingId);
    });
</script>
@endscript