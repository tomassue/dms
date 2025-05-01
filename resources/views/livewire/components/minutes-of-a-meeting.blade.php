<div>
    <div class="row col-xxl-12" style="display: {{ $apoMeetingId && ($show == false) ? '' : 'none' }};">
        <!-- begin::Meeting Details -->
        <div class="col-md-3 g-5">
            <!--begin::Mixed Widget 5-->
            <div class="card card-md-stretch">
                <!--begin::Header-->
                <div class="card-header border-0 py-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">Meeting Details</span>
                        <span class="text-muted fw-bold fs-7"></span>
                    </h3>
                </div>
                <!--end::Header-->

                <!--begin::Body-->
                <div class="card-body d-flex flex-column" style="position: relative; padding-top: unset;">
                    <div id="kt_customer_view_details" class="collapse show">
                        <div class="py-5 fs-6">
                            <!--begin::Details item-->
                            <div class="fw-bolder mt-5">Date</div>
                            <div class="text-gray-600">{{ $apo_meeting->formatted_date ?? '-' }}</div>
                            <!--begin::Details item-->
                            <!--begin::Details item-->
                            <div class="fw-bolder mt-5">Description</div>
                            <div class="text-gray-600">{{ $apo_meeting->description ?? '-' }}</div>
                            <!--begin::Details item-->
                            <!--begin::Details item-->
                            <div class="fw-bolder mt-5">Time</div>
                            <div class="text-gray-600">{{ $apo_meeting->time_range ?? '-' }}</div>
                            <!--begin::Details item-->
                            <!--begin::Details item-->
                            <div class="fw-bolder mt-5">Venue</div>
                            <div class="text-gray-600">{{ $apo_meeting->venue ?? '-' }}</div>
                            <!--begin::Details item-->
                            <!--begin::Details item-->
                            <div class="fw-bolder mt-5">Prepared by</div>
                            <div class="text-gray-600">{{ $apo_meeting->preparedBy->name ?? '-' }}</div>
                            <!--begin::Details item-->
                            <!--begin::Details item-->
                            <div class="fw-bolder mt-5">Approved by</div>
                            <div class="text-gray-600">{{ $apo_meeting->approvedBy->name ?? '-' }}</div>
                            <!--begin::Details item-->
                            <!--begin::Details item-->
                            <div class="fw-bolder mt-5">Noted by</div>
                            <div class="text-gray-600">{{ $apo_meeting->notedBy->name ?? '-'  }}</div>
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

        <!-- begin::Add Meeting Minutes -->
        <div class="col-md-9 g-5">
            <!--begin::Mixed Widget 5-->
            <div class="card card-md-stretch">
                <!--begin::Header-->
                <div class="card-header border-0 py-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">Add Minutes</span>
                        <span class="text-muted fw-bold fs-7"></span>
                    </h3>
                    <div class="card-toolbar">
                        <div class="d-flex align-items-center gap-2">
                            <!-- begin::Menu -->
                            <a href="#" class="btn btn-icon btn-info" wire:click="goBack" title="Go Back">
                                <i class="bi bi-arrow-left-short" wire:loading.remove wire:target="goBack"></i></i>
                                <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="goBack">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </a>
                            <!-- end::Menu -->
                        </div>
                    </div>
                </div>
                <!--end::Header-->

                @can('minutesOfMeeting.create')
                <!--begin::Body-->
                <div class="card-body d-flex flex-column" style="position: relative;">
                    <form wire:submit="saveMinutes">
                        <div class="p-2">
                            <div class="mb-10">
                                <label class="form-label required">Activity</label>
                                <textarea class="form-control" wire:model.live="activity"></textarea>
                                @error('activity')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label">Point person</label>
                                <input type="text" class="form-control" wire:model.live="point_person">
                                @error('point_person')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label">Expected output</label>
                                <textarea class="form-control" wire:model.live="expected_output"></textarea>
                                @error('expected_output')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label">Agreements</label>
                                <textarea class="form-control" wire:model.live="agreements"></textarea>
                                @error('agreements')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 gap-sm-5 my-10">
                                <button type="button" class="btn btn-danger" wire:click="cancel">
                                    <span wire:loading.remove wire:target="cancel">Cancel</span>
                                    <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="cancel">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </button>
                                <button type="submit" class="btn btn-primary"
                                    @if(!$apoMeetingId || !$activity) disabled @endif
                                    wire:loading.attr="disabled" wire:target="saveMinutes">
                                    <span wire:loading.remove wire:target="saveMinutes">Save</span>
                                    <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="saveMinutes">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="resize-triggers">
                        <div class="expand-trigger">
                            <div style="width: 404px; height: 426px;"></div>
                        </div>
                        <div class="contract-trigger"></div>
                    </div>
                </div>
                <!--end::Body-->
                @endcan
            </div>
            <!--end::Mixed Widget 5-->
        </div>
        <!-- end::Add Meeting Minutes -->

        <!-- begin::Meeting Minutes -->
        <div class="col-xxl-12 g-5">
            <!--begin::Mixed Widget 5-->
            <div class="card card-xxl-stretch">
                <!--begin::Header-->
                <div class="card-header border-0 py-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">Minutes of a Meeting</span>
                        <span class="text-muted fw-bold fs-7">Over {{ $minutes_of_meeting->count() }} entries</span>
                    </h3>
                </div>
                <!--end::Header-->

                <!--begin::Body-->
                <div class="card-body d-flex flex-column" style="position: relative;">
                    <div class="table-responsive" wire:loading.class="opacity-50" wire:target.except="saveOutgoing">
                        <table class="table align-middle table-hover table-rounded border gy-7 gs-7">
                            <thead>
                                <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200 bg-light">
                                    <th>No.</th>
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
                                @forelse($minutes_of_meeting as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->activity }}</td>
                                    <td>{{ $item->point_person }}</td>
                                    <td>{{ $item->expected_output }}</td>
                                    <td>{{ $item->agreements }}</td>
                                    @can('minutesOfMeeting.update')
                                    <td class="text-center" wire:loading.class="pe-none">
                                        <div class="btn-group" role="group" aria-label="Actions">
                                            @can('minutesOfMeeting.update')
                                            <button type="button" class="btn btn-icon btn-sm btn-secondary" title="Edit" wire:click="editMinute({{ $item->id }})" wire:loading.attr="disabled">
                                                <i class="bi bi-pencil" wire:loading.remove wire:target="editMinute({{ $item->id }})"></i>
                                                <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="editMinute({{ $item->id }})">
                                                    <span class="sr-only">Loading...</span>
                                                </div>
                                            </button>
                                            <button type="button" class="btn btn-icon btn-sm btn-danger" title="Remove" wire:click="removeMinute({{ $item->id }})" wire:loading.attr="disabled">
                                                <i class="bi bi-dash-circle-dotted" wire:loading.remove wire:target="removeMinute({{ $item->id }})"></i>
                                                <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="removeMinute({{ $item->id }})">
                                                    <span class="sr-only">Loading...</span>
                                                </div>
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                    @endcan
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No records found.</td>
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

@script
<script>
    $wire.on('scroll-to-top', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
</script>
@endscript