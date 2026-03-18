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
                                        <livewire:components.menu-filter-component page="incoming" context="requests" />
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
                                                <th>Status</th>
                                                <th>Assign By</th>
                                                <th>Category No.</th>
                                                <th>Office/Brgy/Org</th>
                                                <th>Date Requested</th>
                                                @can('incoming.requests.update')
                                                <th class="text-center">Actions</th>
                                                @endcan
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($incoming_requests as $item)
                                            <tr wire:click="viewIncomingRequest({{ $item->id }})" class="cursor-pointer">
                                                <td>
                                                    <span class="badge
                                            @switch($item->status->name)
                                            @case('pending')
                                            badge-danger
                                            @break
                                            @case('processed')
                                            badge-primary
                                            @break
                                            @case('forwarded')
                                            badge-warning
                                            @break
                                            @case('completed')
                                            badge-info
                                            @break
                                            @case('cancelled')
                                            badge-dark
                                            @break
                                            @default
                                            badge-dark
                                            @endswitch
                                            text-capitalize
                                            ">
                                                        {{ $item->status->name }}
                                                    </span><br/>
                                                </td>
                                                <td>
                                                    <span class="badge text-uppercase {{ ($item->username->name ?? 'None') === 'None' ? 'badge-light-danger' : 'badge-info' }}">
                                                        {{ $item->username->name ?? 'None' }} 
                                                    </span><br/>
                                                </td>
                                                <td>
                                                    {{ $item->category->incoming_request_category_name }}-{{$item->category_no}}
                                                </td>
                                                <td>
                                                    {{ $item->office_barangay_organization }}
                                                </td>
                                                <td>
                                                    {{ $item->formatted_date_requested }}<br/>
                                                    @if($item->status->name!='completed')
                                                    <span class="badge badge-light-danger">{{ $item->request_age }}
                                                    </span>
                                                    @endif
                                                </td>
                                                <td class="text-center" wire:loading.class="pe-none">
                                                    <div class="btn-group" role="group" aria-label="Actions">
                                                        @can('incoming.requests.update')
                                                        <button type="button" class="btn btn-icon btn-sm btn-secondary" title="Edit" wire:click="editIncomingRequest({{ $item->id }})" @click.stop {{ ($is_office_admin == '1') ? '' : (($item->IsCompleted() || $item->IsCancelled()) ? 'disabled' : '') }}>
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
                                                        <button type="button" class="btn btn-icon btn-sm btn-warning" title="Forward" wire:click="$dispatch('show-forward-modal', { id: {{ $item->id }} })" @click.stop {{ ($item->IsCancelled() || $item->IsCompleted()) ? 'disabled' : '' }}>
                                                            <i class="bi bi-arrow-up-square"></i>
                                                        </button>
                                                        @endcan
                                                        <button type="button" class="btn btn-icon btn-sm btn-primary" title="Assign" wire:click="showAssignRequest({{ $item->id }})" @click.stop {{ ($item->IsCompleted() || $item->IsCancelled()) ? 'disabled' : '' }}>
                                                            <div wire:loading.remove wire:target="showAssignRequest({{ $item->id }})">
                                                                <i class="bi bi-person-check"></i>
                                                            </div>
                                                            <div wire:loading wire:target="showAssignRequest({{ $item->id }})">
                                                                <div class="spinner-border spinner-border-sm" role="status">
                                                                    <span class="visually-hidden">Loading...</span>
                                                                </div>
                                                            </div>
                                                        </button>
                                                        <button type="button" class="btn btn-icon btn-sm btn-info" title="Log" wire:click="activityLog({{ $item->id }})" @click.stop>
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
                            <div class="mb-10" style="display:{{ $editMode ? '' : 'none' }};">
                                <label class="form-label">Recommendation</label>
                                <textarea class="form-control" wire:model="remarks"></textarea>
                                @error('remarks')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10" style="display:{{ $editMode ? '' : 'none' }};">
                                <label class="form-label required">Document Type</label>
                                <select class="form-select" aria-label="Select document category" wire:model="ref_document_type_id">
                                    <option>-Select-</option>
                                    @foreach ($document_type as $item)
                                    <option value="{{ $item->id }}">{{ $item->document_name }}</option>
                                    @endforeach
                                </select>
                                @error('ref_incoming_request_category_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10" style="display:{{ $editMode ? '' : 'none' }};">
                                <label class="form-label">Memo No.</label>
                                <input type="text" class="form-control" wire:model="memo_no">
                                @error('memo_no')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">No.</label>
                                <input type="text" class="form-control" wire:model="no" disabled>
                                @error('no')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Category</label>
                                <select class="form-select" aria-label="Select document category" wire:model="ref_incoming_request_category_id" {{ $is_office_admin ? '' : 'xdisabled' }}>
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
                                <label class="form-label required">Category No.</label>
                                <input type="text" class="form-control" wire:model="category_no" {{ $is_office_admin ? '' : 'xdisabled' }}>
                                @error('category_no')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Office/Brgy/Org</label>
                                <input type="text" class="form-control" wire:model="office_barangay_organization" {{ $is_office_admin ? '' : 'xdisabled' }}>
                                @error('office_barangay_organization')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label ">Subject</label>
                                <textarea class="form-control" wire:model="description" {{ $is_office_admin ? '' : 'xdisabled' }}></textarea>
                                @error('description')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label ">Requestor Name</label>
                                <input type="text" class="form-control" wire:model="contact_person_name" {{ $is_office_admin ? '' : 'xdisabled' }}>
                                @error('contact_person_name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label ">Date Requested</label>
                                <input type="date" class="form-control" wire:model="date_requested" {{ $is_office_admin ? '' : 'xdisabled' }}>
                                @error('date_requested')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label ">Date and Time</label>
                                <input type="datetime-local" class="form-control" wire:model="date_time" {{ $is_office_admin ? '' : 'xdisabled' }}>
                                @error('date_time')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label ">Location</label>
                                <input type="text" class="form-control" wire:model="location" {{ $is_office_admin ? '' : 'xdisabled' }}>
                                @error('location')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label ">Contact Number</label>
                                <input type="text" class="form-control" wire:model="contact_person_number"
                                    maxlength="11"
                                    oninput="this.value = '09' + this.value.slice(2).replace(/\D/g, '');"
                                    placeholder="09XXXXXXXXX"
                                    {{ $is_office_admin ? '' : 'xdisabled' }}>
                                @error('contact_person_number')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label ">Contact Email</label>
                                <input type="text" class="form-control" wire:model="contact_person_email" {{ $is_office_admin ? '' : 'xdisabled' }}>
                                @error('contact_person_email')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10" style="display:{{ $editMode ? '' : 'none' }};">
                                <label class="form-label">Comment</label>
                                <textarea class="form-control" wire:model="comment"></textarea>
                                @error('comment')
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
                                    <input type="file" class="form-control files" name="files[]" multiple>
                                </div>
                                @error('files')
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
                                                <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                                    <a href="#" class="btn btn-icon btn-sm btn-light-info ms-2" wire:click="viewFile({{ $item->id }})"><i class="bi bi-eye"></i></a>
                                                    <button 
                                                            type="button" 
                                                            class="btn btn-icon btn-sm btn-light-danger ms-2"
                                                            title="Remove File"
                                                            wire:click.prevent="removeUploadedFile({{ $item->id }})"
                                                            wire:confirm="Are you sure you want to permanently delete this file from the request? This action cannot be undone."
                                                            wire:loading.attr="disabled"
                                                        ><i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </div>
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
                    <div wire:loading.remove wire:target="saveIncomingRequest, files">
                        <button 
                            type="submit" 
                            class="btn btn-primary" 
                            wire:loading.attr="disabled" 
                            wire:target="files" 
                        >{{ $editMode ? 'Update' : 'Create' }}</button>
                    </div>
                    <div wire:loading wire:target="saveIncomingRequest, files">
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
                        <div class="row">
                            <div class="col-5 fw-bold">Status:</div>
                            <div class="col-7">
                                <span class="badge 
                                @switch(strtolower($ref_status_id ?? '-'))
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
                                text-capitalize">
                                    {{ $ref_status_id ?? '-' }}
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-5 fw-bold">Forwarded to:</div>
                            <div class="col-7">
                                @foreach($forwarded_divisions as $item)
                                {{ $item['division_name'] }}@if(!$loop->last), @endif
                                @endforeach
                            </div>
                        </div>
                        {{-- <div class="row">
                            <div class="col-5 fw-bold">No.:</div>
                            <div class="col-7">{{ $no ?? '-' }}</div>
                        </div> --}}
                        <div class="row">
                            <div class="col-5 fw-bold">Category No:</div>
                            <div class="col-7">{{ $ref_incoming_request_category_id ?? '-' }}-{{ $category_no ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-5 fw-bold">Memo No.:</div>
                            <div class="col-7">{{ $memo_no ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-5 fw-bold">Office/Brgy/Org:</div>
                            <div class="col-7">{{ $office_barangay_organization ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-5 fw-bold">Subject:</div>
                            <div class="col-7">{{ $description ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-5 fw-bold">Requestor Name:</div>
                            <div class="col-7">{{ $contact_person_name ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-5 fw-bold">Request Received:</div>
                            <div class="col-7">{{ $date_requested ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-5 fw-bold">Requested Date and Time:</div>
                            <div class="col-7">{{ $date_time ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-5 fw-bold">Location:</div>
                            <div class="col-7">{{ $location ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-5 fw-bold">Contact person number:</div>
                            <div class="col-7">{{ $contact_person_number ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-5 fw-bold">Contact person email:</div>
                            <div class="col-7">{{ $contact_person_email ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-5 fw-bold">Recommendation:</div>
                            <div class="col-7">{{ $remarks ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-5 fw-bold">Comment:</div>
                            <div class="col-7">{{ $comment ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h5>Files</h5>
                        {{-- START COPY ATTACHMENT NAME --}}
                        <div class="row align-items-center" x-data="{ 
                            tooltip: 'Copy',
                            copyToClipboard() {
                                const textToCopy = $refs.attachmentValue.innerText.trim();
                                
                                // 1. Try Modern API (Works on localhost/HTTPS)
                                if (navigator.clipboard && window.isSecureContext) {
                                    navigator.clipboard.writeText(textToCopy).then(() => this.showSuccess());
                                } else {
                                    // 2. Fallback for HTTP (dms.test)
                                    // We create an input instead of textarea for better mobile/modal support
                                    const input = document.createElement('input');
                                    input.value = textToCopy;
                                    
                                    // Append it to the modal body specifically to bypass focus traps
                                    $refs.copyContainer.appendChild(input);
                                    
                                    input.select();
                                    input.setSelectionRange(0, 99999); // For mobile devices

                                    try {
                                        const successful = document.execCommand('copy');
                                        if (successful) {
                                            this.showSuccess();
                                        } else {
                                            console.error('ExecCommand returned false');
                                        }
                                    } catch (err) {
                                        console.error('Fallback copy failed', err);
                                    }

                                    // Clean up
                                    $refs.copyContainer.removeChild(input);
                                }
                            },
                            showSuccess() {
                                this.tooltip = 'Copied!';
                                setTimeout(() => this.tooltip = 'Copy', 2000);
                            }
                        }">
                            <div class="col-5 fw-bold">Attachment Name:</div>
                            <div class="col-7 d-flex align-items-center" x-ref="copyContainer">
                                <span x-ref="attachmentValue" class="me-2">
                                    {{ $ref_incoming_request_category_id ?? '-' }}-{{ $category_no ?? '-' }}_{{ $memo_no ?? '-' }}
                                </span>

                                <button 
                                    type="button" 
                                    class="btn btn-sm btn-outline-primary p-1" 
                                    @click="copyToClipboard()"
                                    title="Copy to clipboard"
                                >
                                    <i class="bi bi-clipboard" x-show="tooltip === 'Copy'"></i>
                                    <span style="font-size: 0.75rem;" x-text="tooltip"></span>
                                </button>
                            </div>
                        </div>
                        {{-- END COPY ATTACHMENT NAME --}}
                        <div class="row">
                            @forelse ($preview_file as $file)
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
                    <button 
                        type="button" 
                        class="btn btn-primary" 
                        wire:click="downloadMergedAttachments" 
                        wire:loading.attr="disabled"
                        wire:target="downloadMergedAttachments"
                    >
                        <span wire:loading.remove wire:target="downloadMergedAttachments">
                            <i class="fa fa-file-pdf"></i> Download Attachments
                        </span>
                        <span wire:loading wire:target="downloadMergedAttachments">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Merging & Downloading...
                        </span>
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="clear">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- showAssignModal -->
    <div class="modal fade" id="showAssignModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="showAssignModalLabel">Person Assign</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="clear"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-5 fw-bold">No.:</div>
                            <div class="col-7">{{ $no ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-5 fw-bold">Category No:</div>
                            <div class="col-7">{{ $ref_incoming_request_category_id ?? '-' }}-{{ $category_no ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-5 fw-bold">Memo No.:</div>
                            <div class="col-7">{{ $memo_no ?? '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-5 fw-bold">Person Assign: </div>
                            <div class="col-7">{{ $user_id ?? '-' }}</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div wire:loading.remove wire:click="setAssignRequest({{ $tempID }})">
                        <button 
                            type="submit" 
                            class="btn btn-primary" 
                            wire:loading.attr="disabled" 
                            wire:target="files" 
                        >{{ $assignThis ? 'Re-Assign' : 'Assign' }}</button>
                    </div>
                    <div wire:loading wire:target="setAssignRequest">
                        <button class="btn btn-primary" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                            <span role="status">Loading...</span>
                        </button>
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="clear">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('hide-incoming-request-modal', () => {
        $('#incomingRequestModal').modal('hide');
    });

    $wire.on('show-incoming-request-modal', () => {
        $('#incomingRequestModal').modal('show');
    });

    $wire.on('show-details-modal', () => {
        $('#detailsModal').modal('show');
    });
    
    $wire.on('show-assign-modal', () => {
        $('#showAssignModal').modal('show');
    });

    $wire.on('hide-assign-modal', () => {
        $('#showAssignModal').modal('hide');
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
        //acceptedFileTypes: ['image/jpeg', 'image/png', 'application/pdf'],
        acceptedFileTypes: ['application/pdf'],
        labelFileTypeNotAllowed: 'File of invalid type',
        allowFileSizeValidation: true,
        maxFileSize: '10MB',
        labelMaxFileSizeExceeded: 'File is too large', 
        server: {
            // This will assign the data to the files[] property.
            process: (fieldName, file, metadata, load, error, progress, abort) => {
                @this.upload('files', file, load, error, progress);
            },
            revert: (uniqueFileId, load, error) => {
                @this.removeUpload('files', uniqueFileId, load, error);
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