<div>
    <!--begin::Content-->
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Container-->
        <div class="container-xxl" id="kt_content_container">
            <div class="row col-xxl-12">
                <!--begin::Row-->
                <div class="row g-5 g-xl-8 col-xxl-8">
                    <div class="col-xxl-12">
                        <!--begin::Mixed Widget 5-->
                        <div class="card card-xxl-stretch">
                            <!--begin::Header-->
                            <div class="card-header border-0 py-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bolder fs-3 mb-1">Incoming Requests</span>
                                    <span class="text-muted fw-bold fs-7">Over {{ $incoming_requests->count() }} incoming requests</span>
                                </h3>
                                <div class="card-toolbar">
                                    <div class="d-flex align-items-center gap-2">
                                        <!--begin::Menu Filter-->
                                        <livewire:components.menu-filter-component />
                                        <!--end::Menu Filter-->

                                        <!--begin::Menu 2-->
                                        @can('incoming.requests.create')
                                        <div class="vr"></div> <!-- Vertical Divider -->
                                        <a href="#" class="btn btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#incomingRequestModal" wire:click="{{ $editMode ? '' : 'generateReferenceNo' }}"><i class="bi bi-plus-circle"></i></a>
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

                                <div class="table-responsive" wire:loading.class="opacity-50" wire:target.except="saveIncomingRequest, generateReferenceNo">
                                    <table class="table align-middle table-hover table-rounded border gy-7 gs-7">
                                        <thead>
                                            <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200 bg-light">
                                                <th>No.</th>
                                                <th>Date Requested</th>
                                                <th>Office/Brgy/Org</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                @can('incoming.requests.update')
                                                <th class="text-center">Actions</th>
                                                @endcan
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($incoming_requests as $item)
                                            <tr>
                                                <td>
                                                    {{ $item->no }}
                                                </td>
                                                <td>
                                                    {{ $item->formatted_date_requested }}
                                                </td>
                                                <td>
                                                    {{ $item->office_barangay_organization }}
                                                </td>
                                                <td>
                                                    {{ $item->category->name }}
                                                </td>
                                                <td>
                                                    <span class="badge
                                            @switch($item->status->name)
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
                                            badge-light-dark
                                            @endswitch
                                            text-capitalize
                                            ">
                                                        {{ $item->status->name }}
                                                    </span>
                                                </td>

                                                <td class="text-center" wire:loading.class="pe-none">
                                                    <div class="btn-group" role="group" aria-label="Basic example">
                                                        @can('incoming.requests.update')
                                                        <button type="button" class="btn btn-icon btn-sm btn-secondary" title="Edit" wire:click="editIncomingRequest({{ $item->id }})">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        @endcan
                                                        @can('incoming.requests.forward')
                                                        <button type="button" class="btn btn-icon btn-sm btn-warning" title="Forward" wire:click="$dispatch('show-forward-modal', { id: {{ $item->id }} })" {{ $item->isForwarded() ? 'disabled' : '' }}>
                                                            <i class="bi bi-arrow-up-square"></i>
                                                        </button>
                                                        @endcan
                                                        <button type="button" class="btn btn-icon btn-sm btn-info" title="Log" wire:click="activityLog({{ $item->id }})">
                                                            <i class="bi bi-clock-history"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No records found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <!--begin::Pagination-->
                                <div class="pt-3">
                                    {{ $incoming_requests->links(data: ['scrollTo' => false]) }}
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
                    </div>
                </div>
                <!--end::Row-->

                <!--begin::Row-->
                <div class="row g-5 g-xl-8 col-xxl-4">
                    <div class="col-xxl-12">
                        <!--begin::Mixed Widget 5-->
                        <div class="card card-xxl-stretch">
                            <!--begin::Header-->
                            <div class="card-header border-0 py-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bolder fs-3 mb-1">Recents</span>
                                    <span class="text-muted fw-bold fs-7">-</span>
                                </h3>
                            </div>
                            <!--end::Header-->

                            <!--begin::Body-->
                            <div class="card-body d-flex flex-column" style="position: relative;">

                                <!-- begin::Items -->
                                <div class="timeline-label">
                                    @foreach ($recent_forwards as $item)
                                    <!--begin::Item-->
                                    <div class="timeline-item">
                                        <!--begin::Label-->
                                        <div class="timeline-label fw-bolder text-gray-800 fs-9">
                                            {{ $item->updated_at->diffForHumans() }}
                                        </div>
                                        <!--end::Label-->

                                        <!--begin::Badge-->
                                        <div class="timeline-badge">
                                            <i class="fa fa-genderless
                                            text-dark
                                            fs-1"></i>
                                        </div>
                                        <!--end::Badge-->

                                        <!--begin::Text-->
                                        <div class="fw-mormal timeline-content text-muted ps-3">
                                            {{ $item->forwardable->no ?? '' }}
                                            forwarded to
                                            {{ $item->division->name ?? '' }}
                                        </div>
                                        <!--end::Text-->
                                    </div>
                                    <!--end::Item-->
                                    @endforeach
                                </div>
                                <!-- end::Items -->

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
                </div>
                <!--end::Row-->
            </div>
        </div>
        <!--end::Container-->
    </div>
    <!--end::Content-->

    @include('livewire.shared.modals.activity-log-modal')

    <!--begin::Modal - Incoming Requests-->
    <div class="modal fade" tabindex="-1" id="incomingRequestModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Edit' : 'Add' }} Incoming Request</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clear">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    <form wire:submit="saveIncomingRequest">
                        <div class="p-2">
                            @can('incoming.requests.update.status')
                            <div class="mb-10" style="display:{{ $editMode ? '' : 'none' }};">
                                <label class="form-label required">Status</label>
                                <select class="form-select text-uppercase" aria-label="Select status" wire:model="ref_status_id">
                                    <option>-Select-</option>
                                    @foreach ($status as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('ref_status_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            @endcan
                            <div class="mb-10">
                                <label class="form-label required">No.</label>
                                <input type="text" class="form-control" wire:model="no" disabled>
                                @error('no')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Office/Brgy/Org</label>
                                <input type="text" class="form-control" wire:model="office_barangay_organization">
                                @error('office_barangay_organization')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Date Requested</label>
                                <input type="date" class="form-control" wire:model="date_requested">
                                @error('date_requested')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Category</label>
                                <select class="form-select" aria-label="Select document category" wire:model="ref_incoming_request_category_id">
                                    <option>-Select-</option>
                                    @foreach ($incoming_request_categories as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('ref_incoming_request_category_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Date and Time</label>
                                <input type="datetime-local" class="form-control" wire:model="date_time">
                                @error('date_time')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Contact Person (Name)</label>
                                <input type="text" class="form-control" wire:model="contact_person_name">
                                @error('contact_person_name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Contact Number</label>
                                <input type="text" class="form-control" wire:model="contact_person_number"
                                    maxlength="11"
                                    oninput="this.value = '09' + this.value.slice(2).replace(/\D/g, '');"
                                    placeholder="09XXXXXXXXX">
                                @error('contact_person_number')
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
                                <label class="form-label">File Upload</label>
                                <div wire:ignore>
                                    <input type="file" class="form-control files" multiple>
                                </div>
                                @error('file_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <!-- Files -->
                            <div class="col-12 mb-3" style="display: {{ $editMode ? '' : 'none' }};">
                                <table class="table table-row-dashed table-row-gray-300 gy-7">
                                    <thead>
                                        <tr class="fw-bolder fs-6 text-gray-800">
                                            <th width="80%">File</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($preview_file as $item)
                                        <tr>
                                            <td>
                                                {{ $item->name }}
                                            </td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-info" wire:click="viewFile({{ $item->id }})">View</a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="2" class="text-center">No files uploaded.</td>
                                            <td class="text-center"></td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="clear">Close</button>
                    <div wire:loading.remove>
                        <button type="submit" class="btn btn-primary">{{ $editMode ? 'Update' : 'Create' }}</button>
                    </div>
                    <div wire:loading wire:target="saveIncomingRequest">
                        <button class="btn btn-primary" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                            <span role="status">Loading...</span>
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - Incoming Requests-->

    <!--begin::Modal - Forward-->
    <div class="modal fade" tabindex="-1" id="forwardModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Forward</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clear">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    <form wire:submit="forward">
                        <div class="p-2">
                            <div class="mb-10">
                                <label class="form-label required">Division</label>
                                <div wire:ignore>
                                    <div id="division-select"></div>
                                </div>
                                @error('selected_divisions')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="clear">Close</button>
                    <div wire:loading.remove>
                        <button type="submit" class="btn btn-primary">Forward</button>
                    </div>
                    <div wire:loading wire:target="saveIncomingRequest">
                        <button class="btn btn-primary" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                            <span role="status">Loading...</span>
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - Forward-->
</div>

@script
<script>
    $wire.on('hide-incoming-request-modal', () => {
        $('#incomingRequestModal').modal('hide');
    });

    $wire.on('show-incoming-request-modal', () => {
        $('#incomingRequestModal').modal('show');
    });

    $wire.on('show-forward-modal', (id) => {
        @this.set('incomingRequestId', id.id);
        $('#forwardModal').modal('show');
    });

    $wire.on('hide-forward-modal', (id) => {
        $('#forwardModal').modal('hide');
    });

    /* -------------------------------------------------------------------------- */

    // Register the plugin 
    FilePond.registerPlugin(FilePondPluginFileValidateType); // for file type validation
    FilePond.registerPlugin(FilePondPluginFileValidateSize); // for file size validation
    FilePond.registerPlugin(FilePondPluginImagePreview); // for image preview

    // Turn input element into a pond with configuration options
    $('.files').filepond({
        // required: true,
        allowFileTypeValidation: true,
        acceptedFileTypes: ['image/jpeg', 'image/png', 'application/pdf'],
        labelFileTypeNotAllowed: 'File of invalid type',
        allowFileSizeValidation: true,
        maxFileSize: '10MB',
        labelMaxFileSizeExceeded: 'File is too large',
        server: {
            // This will assign the data to the files[] property.
            process: (fieldName, file, metadata, load, error, progress, abort) => {
                @this.upload('file_id', file, load, error, progress);
            },
            revert: (uniqueFileId, load, error) => {
                @this.removeUpload('file_id', uniqueFileId, load, error);
            }
        }
    });

    $wire.on('reset-files', () => {
        $('.files').each(function() {
            $(this).filepond('removeFiles');
        });
    });

    /* -------------------------------------------------------------------------- */

    // Listen for event
    $wire.on('open-file', (url) => {
        window.open(event.detail.url, '_blank'); // Open the signed URL in a new tab
    });

    /* -------------------------------------------------------------------------- */

    VirtualSelect.init({
        ele: '#division-select',
        options: @json($divisions),
        multiple: true,
        maxWidth: '100%',
        dropboxWrapper: 'body', // Append to body instead of parent
        zIndex: 1060, // Higher than modal's z-index
    });

    let selected_divisions = document.querySelector('#division-select');
    selected_divisions.addEventListener('change', () => {
        let data = selected_divisions.value;
        @this.set('selected_divisions', data);
    });

    $wire.on('reset-division-select', () => {
        document.querySelector('#division-select').reset();
    });
</script>
@endscript