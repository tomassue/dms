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
                        <div class="card card-xxl-stretch" wire:loading.class="opacity-50 pe-none" wire:target.except="saveIncomingRequest, generateReferenceNo">
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

                                <div class="table-responsive">
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
                                                    {{ $item->category->incoming_request_category_name }}
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
                                                    <div class="btn-group" role="group" aria-label="Actions">
                                                        @can('incoming.requests.update')
                                                        <button type="button" class="btn btn-icon btn-sm btn-secondary" title="Edit" wire:click="editIncomingRequest({{ $item->id }})" {{ ($item->IsCompleted() || $item->IsCancelled()) ? 'disabled' : '' }}>
                                                            <div wire:loading.remove wire:target="editIncomingRequest({{ $item->id }})">
                                                                <i class="bi bi-pencil"></i>
                                                            </div>
                                                            <div wire:loading wire:target="editIncomingRequest({{ $item->id }})">
                                                                <div class="spinner-border spinner-border-sm" role="status">
                                                                    <span class="visually-hidden">Loading...</span>
                                                                </div>
                                                            </div>
                                                        </button>
                                                        @endcan
                                                        @can('incoming.requests.forward')
                                                        <button type="button" class="btn btn-icon btn-sm btn-warning" title="Forward" wire:click="$dispatch('show-forward-modal', { id: {{ $item->id }} })" {{ ($item->IsForwarded() || $item->IsCancelled() || $item->IsCompleted()) ? 'disabled' : '' }}>
                                                            <i class="bi bi-arrow-up-square"></i>
                                                        </button>
                                                        @endcan
                                                        <button type="button" class="btn btn-icon btn-sm btn-info" title="Log" wire:click="activityLog({{ $item->id }})">
                                                            <div wire:loading.remove wire:target="activityLog({{ $item->id }})">
                                                                <i class="bi bi-clock-history"></i>
                                                            </div>
                                                            <div wire:loading wire:target="activityLog({{ $item->id }})">
                                                                <div class="spinner-border spinner-border-sm" role="status">
                                                                    <span class="visually-hidden">Loading...</span>
                                                                </div>
                                                            </div>
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

                @include('livewire.directives.recent-forwards-directive')
            </div>
        </div>
        <!--end::Container-->
    </div>
    <!--end::Content-->

    @include('livewire.shared.modals.activity-log-modal')
    @include('livewire.shared.modals.forward-modal')

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
                                <input type="text" class="form-control" wire:model="office_barangay_organization" {{ $is_office_admin ? '' : 'disabled' }}>
                                @error('office_barangay_organization')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Date Requested</label>
                                <input type="date" class="form-control" wire:model="date_requested" {{ $is_office_admin ? '' : 'disabled' }}>
                                @error('date_requested')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Category</label>
                                <select class="form-select" aria-label="Select document category" wire:model="ref_incoming_request_category_id" {{ $is_office_admin ? '' : 'disabled' }}>
                                    <option>-Select-</option>
                                    @foreach ($incoming_request_categories as $item)
                                    <option value="{{ $item->id }}">{{ $item->incoming_request_category_name }}</option>
                                    @endforeach
                                </select>
                                @error('ref_incoming_request_category_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Date and Time</label>
                                <input type="datetime-local" class="form-control" wire:model="date_time" {{ $is_office_admin ? '' : 'disabled' }}>
                                @error('date_time')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Contact Person (Name)</label>
                                <input type="text" class="form-control" wire:model="contact_person_name" {{ $is_office_admin ? '' : 'disabled' }}>
                                @error('contact_person_name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Contact Number</label>
                                <input type="text" class="form-control" wire:model="contact_person_number"
                                    maxlength="11"
                                    oninput="this.value = '09' + this.value.slice(2).replace(/\D/g, '');"
                                    placeholder="09XXXXXXXXX"
                                    {{ $is_office_admin ? '' : 'disabled' }}>
                                @error('contact_person_number')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Description</label>
                                <textarea class="form-control" wire:model="description" {{ $is_office_admin ? '' : 'disabled' }}></textarea>
                                @error('description')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10" style="display:{{ $editMode ? '' : 'none' }};">
                                <label class="form-label">Remarks</label>
                                <textarea class="form-control" wire:model="remarks"></textarea>
                                @error('remarks')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <!-- begin::Alert -->
                            <div class="alert alert-dismissible bg-light-danger border border-danger border-dashed d-flex flex-column flex-sm-row w-100 p-5 mb-10">
                                <!--begin::Icon-->
                                <!--begin::Svg Icon | path: icons/duotune/communication/com003.svg-->
                                <span class="svg-icon svg-icon-2hx svg-icon-danger me-4 mb-5 mb-sm-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path opacity="0.3" d="M2 4V16C2 16.6 2.4 17 3 17H13L16.6 20.6C17.1 21.1 18 20.8 18 20V17H21C21.6 17 22 16.6 22 16V4C22 3.4 21.6 3 21 3H3C2.4 3 2 3.4 2 4Z" fill="black"></path>
                                        <path d="M18 9H6C5.4 9 5 8.6 5 8C5 7.4 5.4 7 6 7H18C18.6 7 19 7.4 19 8C19 8.6 18.6 9 18 9ZM16 12C16 11.4 15.6 11 15 11H6C5.4 11 5 11.4 5 12C5 12.6 5.4 13 6 13H15C15.6 13 16 12.6 16 12Z" fill="black"></path>
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                                <!--end::Icon-->
                                <!--begin::Content-->
                                <div class="d-flex flex-column pe-0 pe-sm-10">
                                    <h5 class="mb-1">Note:</h5>
                                    <span>Please wait for the file to be <b>uploaded</b> before saving changes. Thank you.</span>
                                </div>
                                <!--end::Content-->
                            </div>
                            <!-- end::Alert -->
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
</div>

@script
<script>
    $wire.on('hide-incoming-request-modal', () => {
        $('#incomingRequestModal').modal('hide');
    });

    $wire.on('show-incoming-request-modal', () => {
        $('#incomingRequestModal').modal('show');
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
</script>
@endscript