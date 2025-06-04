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
                            <div class="fw-bolder mt-5">Category</div>
                            <div class="text-gray-600">{{ $apo_meeting->apoMeetingsCategory->name ?? '-' }}</div>
                            <!--begin::Details item-->
                            <!--begin::Details item-->
                            <div class="fw-bolder mt-5">Description</div>
                            <div class="text-gray-600">{{ $apo_meeting->description ?? '-' }}</div>
                            <!--begin::Details item-->
                            <!--begin::Details item-->
                            <div class="fw-bolder mt-5">Time</div>
                            <div class="text-gray-600">
                                @if($apo_meeting->formatted_time_start && $apo_meeting->formatted_time_end)
                                {{ $apo_meeting->time_range }}
                                @else
                                {{ $apo_meeting->time_range }} - <span class="badge badge-danger">Not assigned</span>
                                @endif
                            </div>
                            <!--begin::Details item-->
                            <!--begin::Details item-->
                            <div class="fw-bolder mt-5">Venue</div>
                            <div class="text-gray-600">{{ $apo_meeting->venue ?? '-' }}</div>
                            <!--begin::Details item-->
                            <!--begin::Details item-->
                            <div class="fw-bolder mt-5">Prepared by</div>
                            <div class="text-gray-600">
                                {{ $apo_meeting->preparedBy->name }}
                            </div>
                            <!--begin::Details item-->
                            <!--begin::Details item-->
                            <div class="fw-bolder mt-5">Approved by</div>
                            <div class="text-gray-600">
                                @if($apo_meeting->approvedBy?->name)
                                {{ $apo_meeting->approvedBy->name }}
                                @else
                                <span class="badge badge-danger">Not assigned</span>
                                @endif
                            </div>
                            <!--begin::Details item-->
                            <!--begin::Details item-->
                            <div class="fw-bolder mt-5">Noted by</div>
                            <div class="text-gray-600">
                                @if($apo_meeting->notedBy?->name)
                                {{ $apo_meeting->notedBy->name }}
                                @else
                                <span class="badge badge-danger">Not assigned</span>
                                @endif
                            </div>
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
                                    <span>Cancel</span>
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

                    <div class="card-toolbar">
                        <div class="d-flex align-items-center gap-2">
                            <!-- begin::Menu -->
                            <div class="btn-group" role="group" aria-label="Actions">
                                <button type="button" class="btn btn-icon btn-warning" title="Print" wire:click="printMinutesOfMeeting({{ $apoMeetingId }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-arrow-up" viewBox="0 0 16 16" wire:loading.remove wire:target="printMinutesOfMeeting({{ $apoMeetingId }})">
                                        <path d="M8 11a.5.5 0 0 0 .5-.5V6.707l1.146 1.147a.5.5 0 0 0 .708-.708l-2-2a.5.5 0 0 0-.708 0l-2 2a.5.5 0 1 0 .708.708L7.5 6.707V10.5a.5.5 0 0 0 .5.5" />
                                        <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1" />
                                    </svg>
                                    <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="printMinutesOfMeeting({{ $apoMeetingId }})">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </button>
                                @if ($apo_meeting->file)
                                <button type="button" class="btn btn-icon btn-info" title="View PDF" wire:click="viewExportedMinutesOfMeeting({{ $apoMeetingId }})">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-filetype-pdf" viewBox="0 0 16 16" wire:loading.remove wire:target="viewExportedMinutesOfMeeting({{ $apoMeetingId }})">
                                        <path fill-rule="evenodd" d="M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5zM1.6 11.85H0v3.999h.791v-1.342h.803q.43 0 .732-.173.305-.175.463-.474a1.4 1.4 0 0 0 .161-.677q0-.375-.158-.677a1.2 1.2 0 0 0-.46-.477q-.3-.18-.732-.179m.545 1.333a.8.8 0 0 1-.085.38.57.57 0 0 1-.238.241.8.8 0 0 1-.375.082H.788V12.48h.66q.327 0 .512.181.185.183.185.522m1.217-1.333v3.999h1.46q.602 0 .998-.237a1.45 1.45 0 0 0 .595-.689q.196-.45.196-1.084 0-.63-.196-1.075a1.43 1.43 0 0 0-.589-.68q-.396-.234-1.005-.234zm.791.645h.563q.371 0 .609.152a.9.9 0 0 1 .354.454q.118.302.118.753a2.3 2.3 0 0 1-.068.592 1.1 1.1 0 0 1-.196.422.8.8 0 0 1-.334.252 1.3 1.3 0 0 1-.483.082h-.563zm3.743 1.763v1.591h-.79V11.85h2.548v.653H7.896v1.117h1.606v.638z" />
                                    </svg>
                                    <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="viewExportedMinutesOfMeeting({{ $apoMeetingId }})">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </button>
                                @endif
                            </div>
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
                                            <button type="button" class="btn btn-icon btn-sm btn-danger" title="Remove" wire:confirm="Are you sure you want to remove this record?" wire:click="removeMinute({{ $item->id }})" wire:loading.attr="disabled">
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

    <!--begin::Modal - PDF-->
    <div class="modal fade" tabindex="-1" id="pdfModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">PDF</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="cancel">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    @if ($pdf)
                    <iframe src="{{ $pdf }}" class="w-100" height="650px" frameborder="0"></iframe>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="cancel">Close</button>
                    <button type="button" class="btn btn-primary" wire:click="$dispatch('confirm-export-minutes-of-meeting')">
                        <div wire:loading.remove wire:target="exportAndUploadPDF">
                            Export
                        </div>
                        <div wire:loading wire:target="exportAndUploadPDF">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - PDF-->

    <!-- begin::Modal - View PDF -->
    <div class="modal fade" tabindex="-1" id="viewPdfModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">PDF</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="cancel">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    @if ($pdf)
                    <iframe src="{{ $pdf }}" class="w-100" height="650px" frameborder="0"></iframe>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="cancel">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- end::Modal - View PDF -->
</div>

@script
<script>
    $wire.on('scroll-to-top', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    $wire.on('show-pdf-modal', () => {
        $('#pdfModal').modal('show');
    });

    $wire.on('hide-pdf-modal', () => {
        $('#pdfModal').modal('hide');
    });

    $wire.on('show-view-pdf-modal', () => {
        $('#viewPdfModal').modal('show');
    });

    $wire.on('confirm-export-minutes-of-meeting', () => {
        Swal.fire({
            title: "You're about to export the document. Continue?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, export it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.exportAndUploadPDF();
            }
        });
    });
</script>
@endscript