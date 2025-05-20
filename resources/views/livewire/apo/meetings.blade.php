<div>
    <!--begin::Mixed Widget 5-->
    <div class="card card-xxl-stretch" style="display: {{ $show ? '' : 'none' }};">
        <!--begin::Header-->
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1">Meetings</span>
                <span class="text-muted fw-bold fs-7">Over {{ $meetings->count() }} meetingss</span>
            </h3>
            <div class="card-toolbar">
                <div class="d-flex align-items-center gap-2">
                    <!--begin::Menu Filter-->
                    <livewire:components.menu-filter-component />
                    <!--end::Menu Filter-->

                    <!--begin::Menu 2-->
                    @can('meeting.create')
                    <div class="vr"></div> <!-- Vertical Divider -->
                    <a href="#" class="btn btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#meetingModal"><i class="bi bi-plus-circle"></i></a>
                    @endcan
                    <!--end::Menu 2-->
                </div>
            </div>
        </div>
        <!--end::Header-->

        <!--begin::Body-->
        <div class="card-body d-flex flex-column" style="position: relative;">
            <!-- begin:search -->
            <div class="row py-5 justify-content-between">
                <div class="col-sm-12 col-md-12 col-lg-4">
                    <input type="search" wire:model.live="search" class="form-control" placeholder="Type a keyword..." aria-label="Type a keyword..." style="appearance: none; background-color: #fff; border: 1px solid #eff2f5; border-radius: 5px; font-size: 14px; line-height: 1.45; outline: 0; padding: 10px 13px;">
                </div>
            </div>
            <!-- end:search -->

            <div class="table-responsive" wire:loading.class="opacity-50" wire:target.except="saveMeeting">
                <table class="table align-middle table-hover table-rounded border gy-7 gs-7">
                    <thead>
                        <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200 bg-light">
                            <th>Date</th>
                            <th>Category</th>
                            <th>Time</th>
                            <th>Description</th>
                            <th>Prepared by</th>
                            <th>Approved by</th>
                            <th>Noted by</th>
                            @can('meeting.update')
                            <th class="text-center">Actions</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($meetings as $item)
                        <tr>
                            <td>{{ $item->formatted_date }}</td>
                            <td>{{ $item->apoMeetingsCategory->name ?? '' }}</td>
                            <td>{{ $item->time_range }}</td>
                            <td>{{ $item->description }}</td>
                            <td>{{ $item->preparedBy->name }}</td>
                            <td>{{ $item->approvedBy->name ?? '-' }}</td>
                            <td>{{ $item->notedBy->name ?? '-' }}</td>
                            <td class="text-center" wire:loading.class="pe-none">
                                <div class="btn-group" role="group" aria-label="Actions">
                                    @can('meeting.update')
                                    <button type="button" class="btn btn-icon btn-sm btn-secondary" title="Edit" wire:click="editMeeting({{ $item->id }})">
                                        <i class="bi bi-pencil" wire:loading.remove wire:target="editMeeting({{ $item->id }})"></i>
                                        <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="editMeeting({{ $item->id }})">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </button>
                                    @endcan
                                    @can('minutesOfMeeting.read')
                                    <button type="button" class="btn btn-icon btn-sm btn-info" title="Read" wire:click="readMinutesOfMeeting({{ $item->id }})">
                                        <i class="bi bi-eye" wire:loading.remove wire:target="readMinutesOfMeeting({{ $item->id }})"></i>
                                        <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="readMinutesOfMeeting({{ $item->id }})">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!--begin::Pagination-->
            <div class="pt-3">
                {{ $meetings->links(data: ['scrollTo' => false]) }}
            </div>
            <!--end::Pagination-->

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

    <!--begin::Modal - Meeting-->
    <div class="modal fade" tabindex="-1" id="meetingModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-xxl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Edit' : 'Add' }} Meeting</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clear">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    <form wire:submit="saveMeeting">
                        <div class="p-2">
                            <div class="mb-10">
                                <label class="form-label required">Date</label>
                                <input type="date" class="form-control" wire:model="date">
                                @error('date')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Category</label>
                                <select class="form-select" aria-label="Select category" wire:model="ref_apo_meetings_category_id">
                                    <option value="">-Select-</option>
                                    @foreach($categories as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('date')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Description</label>
                                <textarea class="form-control" wire:model="description"></textarea>
                                @error('description')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Time (start)</label>
                                <input type="time" class="form-control" wire:model="time_start">
                                @error('time_start')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label">Time (end)</label>
                                <input type="time" class="form-control" wire:model="time_end">
                                @error('time_end')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Venue</label>
                                <input type="text" class="form-control" wire:model="venue">
                                @error('venue')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label">Prepared by</label>
                                <input type="text" class="form-control" wire:model="prepared_by" disabled>
                            </div>
                            <div class="mb-10">
                                <label class="form-label">Approved by</label>
                                <select class="form-select" aria-label="Select approved by" wire:model="approved_by">
                                    <option>-Select-</option>
                                    @foreach ($signatories as $item)
                                    <option value="{{ $item->user_id }}">{{ $item->user->name }}</option>
                                    @endforeach
                                </select>
                                @error('approved_by')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label">Noted by</label>
                                <select class="form-select" aria-label="Select noted by" wire:model="noted_by">
                                    <option>-Select-</option>
                                    @foreach ($signatories as $item)
                                    <option value="{{ $item->user_id }}">{{ $item->user->name }}</option>
                                    @endforeach
                                </select>
                                @error('noted_by')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="clear">Close</button>
                    <div wire:loading.remove>
                        <button type="submit" class="btn btn-primary">{{ $editMode ? 'Update' : 'Create' }}</button>
                    </div>
                    </form>
                    <div wire:loading wire:target="saveMeeting">
                        <button class="btn btn-primary" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                            <span role="status">Loading...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Modal - Meeting-->
    </div>

    @if ($meetingId)
    <livewire:components.minutes-of-a-meeting :apoMeetingId="$meetingId" :$show />
    @endif
</div>

@script
<script>
    $wire.on('show-meeting-modal', () => {
        $('#meetingModal').modal('show');
    });

    $wire.on('hide-meeting-modal', () => {
        $('#meetingModal').modal('hide');
    });
</script>
@endscript