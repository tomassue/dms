<div>
    <!--begin::Content-->
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Container-->
        <div class="container-xxl" id="kt_content_container">
            <!--begin::Row-->
            <div class="row g-5 g-xl-12">
                <!--begin::Mixed Widget 5-->
                <div class="card card-xxl-stretch">
                    <!--begin::Beader-->
                    <div class="card-header border-0 py-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder fs-3 mb-1">Users</span>
                            <span class="text-muted fw-bold fs-7">Management</span>
                        </h3>
                        <div class="card-toolbar">
                            @can('reference.userManagement.create')
                            <!--begin::Menu-->
                            <a href="#" class="btn btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#usersModal"><i class="bi bi-plus-circle"></i></a>
                            <!--end::Menu-->
                            @endcan
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
                            <table class="table align-middle table-hover table-rounded table-striped border gy-7 gs-7">
                                <thead>
                                    <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                                        <th>Name</th>
                                        <th>Office</th>
                                        <th>Division / Title</th>
                                        <th>Position</th>
                                        <th>Username</th>
                                        <th>Status</th>
                                        @can('reference.userManagement.update')
                                        <th>Actions</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $item)
                                    <tr>
                                        <td>{{ $item->name }}</td>
                                        <td>{{ $item->roles()->first()->name ?? '-' }}</td>
                                        <td>{{ $item->user_metadata->division->name ?? '-' }}</td>
                                        <td>{{ $item->user_metadata->position->name ?? '-' }}</td>
                                        <td>{{ $item->username }}</td>
                                        <td>
                                            @if(!$item->deleted_at)
                                            <span class="badge badge-light-success">Active</span>
                                            @else
                                            <span class="badge badge-light-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="Actions">
                                                @can('reference.userManagement.update')
                                                <a href="#" class="btn btn-icon btn-sm btn-secondary" title="Edit" wire:click="editUser({{ $item->id }})">
                                                    <i class="bi bi-pencil"></i>
                                                </a>

                                                <a href="#" class="btn btn-icon btn-sm btn-warning" title="Reset Password" wire:click="resetPasswordUser({{ $item->id }})">
                                                    <i class="bi bi-key"></i>
                                                </a>

                                                <a
                                                    href="#"
                                                    class="btn btn-icon btn-sm {{ $item->deleted_at ? 'btn-info' : 'btn-danger' }}"
                                                    title="Delete"
                                                    wire:click="{{ $item->deleted_at ? 'restoreUser' : 'deleteUser' }}({{ $item->id }})">
                                                    <i class="bi {{ $item->deleted_at ? 'bi-arrow-counterclockwise' : 'bi-trash' }}"></i>
                                                </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No records found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!--begin::Pagination-->
                        <div class="pt-3">
                            {{ $users->links(data: ['scrollTo' => false]) }}
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
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Content-->

    <!--begin::Modal - Users-->
    <div class="modal fade" tabindex="-1" id="usersModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Edit' : 'Add' }} User</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clear">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <!--end::Close-->
                </div>
                @php
                /**
                * TODO: Add the user.name in modal title
                * ! When office is selected, division and position options should be updated.
                */
                @endphp
                <div class="modal-body">
                    <form wire:submit="{{ $editMode ? 'updateUser' : 'createUser' }}">
                        <div class="p-2">
                            <div class="mb-10">
                                <label class="form-label required">Email</label>
                                <input type="email" class="form-control" wire:model="email">
                                @error('email')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Name</label>
                                <input type="text" class="form-control" wire:model="name">
                                @error('name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Username</label>
                                <input type="text" class="form-control" wire:model="username">
                                @error('username')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Office</label>
                                <select class="form-select" aria-label="Select example" wire:model.live="role_id">
                                    <option>-Select an office-</option>
                                    @foreach ($roles as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!--begin::Alert-->
                            <div class="alert alert-dismissible bg-light-info border border-info border-3 border-dashed d-flex flex-column flex-sm-row w-100 p-5 mb-10">
                                <!--begin::Icon-->
                                <span class="svg-icon svg-icon-2hx svg-icon-info me-4 mb-5 mb-sm-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path opacity="0.3" d="M12 22C13.6569 22 15 20.6569 15 19C15 17.3431 13.6569 16 12 16C10.3431 16 9 17.3431 9 19C9 20.6569 10.3431 22 12 22Z" fill="black"></path>
                                        <path d="M19 15V18C19 18.6 18.6 19 18 19H6C5.4 19 5 18.6 5 18V15C6.1 15 7 14.1 7 13V10C7 7.6 8.7 5.6 11 5.1V3C11 2.4 11.4 2 12 2C12.6 2 13 2.4 13 3V5.1C15.3 5.6 17 7.6 17 10V13C17 14.1 17.9 15 19 15ZM11 10C11 9.4 11.4 9 12 9C12.6 9 13 8.6 13 8C13 7.4 12.6 7 12 7C10.3 7 9 8.3 9 10C9 10.6 9.4 11 10 11C10.6 11 11 10.6 11 10Z" fill="black"></path>
                                    </svg>
                                </span>
                                <!--end::Icon-->

                                <!--begin::Wrapper-->
                                <div class="d-flex flex-column pe-0 pe-sm-10">
                                    <!--begin::Title-->
                                    <h5 class="mb-1">Note</h5>
                                    <!--end::Title-->
                                    <!--begin::Content-->
                                    <span>If the user you are about to add is the <b> office admin </b>, don't select any division and position.</span>
                                    <!--end::Content-->
                                </div>
                                <!--end::Wrapper-->
                            </div>
                            <!--end::Alert-->

                            <div class="mb-10">
                                <label class="form-label">Division / Title</label>
                                <select class="form-select" aria-label="Select example" wire:model="ref_division_id">
                                    <option value="">-Select a division-</option>
                                    @foreach ($divisions as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('ref_division_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label">Position</label>
                                <select class="form-select" aria-label="Select example" wire:model="ref_position_id">
                                    <option value="">-Select a position-</option>
                                    @foreach ($positions as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('ref_position_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Permissions</label>
                                <br>
                                @error('permissions')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                                <div class="d-flex flex-column">

                                    <!-- Dashboard -->
                                    <li class="d-flex align-items-center py-2">
                                        <span class="bullet me-5"></span> Dashboard
                                    </li>
                                    <div class="row py-2 ms-4">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="dashboard.read" id="dashboardRead" wire:model="permissions" />
                                            <label class="form-check-label" for="dashboardRead"> Read </label>
                                        </div>
                                    </div>

                                    <!-- Incoming Requests -->
                                    <li class="d-flex align-items-center py-2">
                                        <span class="bullet me-5"></span> Incoming Requests
                                    </li>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="incoming.requests.create" id="incomingRequestsCreate" wire:model="permissions" />
                                            <label class="form-check-label" for="incomingRequestsCreate"> Create </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="incoming.requests.read" id="incomingRequestsRead" wire:model="permissions" />
                                            <label class="form-check-label" for="incomingRequestsRead"> Read </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="incoming.requests.forward" id="incomingRequestsForward" wire:model="permissions" />
                                            <label class="form-check-label" for="incomingRequestsForward"> Forward </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="incoming.requests.update" id="incomingRequestsUpdate" wire:model="permissions" />
                                            <label class="form-check-label" for="incomingRequestsUpdate"> Update </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="incoming.requests.update.status" id="incomingRequestsUpdateStatus" wire:model="permissions" />
                                            <label class="form-check-label" for="incomingRequestsUpdateStatus"> Status </label>
                                        </div>
                                    </div>

                                    <!-- Incoming Documents -->
                                    <li class="d-flex align-items-center py-2">
                                        <span class="bullet me-5"></span> Incoming Documents
                                    </li>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="incoming.documents.create" id="incomingDocumentsCreate" wire:model="permissions" />
                                            <label class="form-check-label" for="incomingDocumentsCreate"> Create </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="incoming.documents.read" id="incomingDocumentsRead" wire:model="permissions" />
                                            <label class="form-check-label" for="incomingDocumentsRead"> Read </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="incoming.documents.forward" id="incomingDocumentsForward" wire:model="permissions" />
                                            <label class="form-check-label" for="incomingDocumentsForward"> Forward </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="incoming.documents.update" id="incomingDocumentsUpdate" wire:model="permissions" />
                                            <label class="form-check-label" for="incomingDocumentsUpdate"> Update </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="incoming.documents.update.status" id="incomingDocumentsUpdateStatus" wire:model="permissions" />
                                            <label class="form-check-label" for="incomingDocumentsUpdateStatus"> Status </label>
                                        </div>
                                    </div>

                                    <!-- Outgoing -->
                                    <li class="d-flex align-items-center py-2">
                                        <span class="bullet me-5"></span> Outgoing
                                    </li>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="outgoing.create" id="outgoingCreate" wire:model="permissions" />
                                            <label class="form-check-label" for="outgoingCreate"> Create </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="outgoing.read" id="outgoingRead" wire:model="permissions" />
                                            <label class="form-check-label" for="outgoingRead"> Read </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="outgoing.update" id="outgoingUpdate" wire:model="permissions" />
                                            <label class="form-check-label" for="outgoingUpdate"> Update </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="outgoing.update.status" id="outgoingUpdateStatus" wire:model="permissions" />
                                            <label class="form-check-label" for="outgoingUpdateStatus"> Status </label>
                                        </div>
                                    </div>

                                    <!-- Calendar -->
                                    <li class="d-flex align-items-center py-2">
                                        <span class="bullet me-5"></span> Calendar
                                    </li>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="calendar.read" id="calendarRead" wire:model="permissions" />
                                            <label class="form-check-label" for="calendarRead"> Read </label>
                                        </div>
                                    </div>

                                    <!-- Accomplishments -->
                                    <li class="d-flex align-items-center py-2">
                                        <span class="bullet me-5"></span> Accomplishments
                                    </li>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="accomplishments.create" id="accomplishmentsCreate" wire:model="permissions" />
                                            <label class="form-check-label" for="accomplishmentsCreate"> Create </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="accomplishments.read" id="accomplishmentsRead" wire:model="permissions" />
                                            <label class="form-check-label" for="accomplishmentsRead"> Read </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="accomplishments.update" id="accomplishmentsUpdate" wire:model="permissions" />
                                            <label class="form-check-label" for="accomplishmentsUpdate"> Update </label>
                                        </div>
                                    </div>

                                    <!-- References -->
                                    <li class="d-flex align-items-center py-2">
                                        <span class="bullet me-5"></span> References
                                    </li>

                                    <!-- References.User Management -->
                                    <li class="d-flex align-items-center py-2 ms-8">
                                        <span class="bullet me-5"></span> User Management
                                    </li>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.userManagement.create" id="userManagementCreate" wire:model="permissions" />
                                            <label class="form-check-label" for="userManagementCreate"> Create </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.userManagement.read" id="userManagementRead" wire:model="permissions" />
                                            <label class="form-check-label" for="userManagementRead"> Read </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.userManagement.update" id="userManagementUpdate" wire:model="permissions" />
                                            <label class="form-check-label" for="userManagementUpdate"> Update </label>
                                        </div>
                                    </div>

                                    <!-- References.Incoming Request Category -->
                                    <li class="d-flex align-items-center py-2 ms-8">
                                        <span class="bullet me-5"></span> Incoming Request Category
                                    </li>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.incomingRequestCategory.create" id="incomingRequestCategoryCreate" wire:model="permissions" />
                                            <label class="form-check-label" for="incomingRequestCategoryCreate"> Create </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.incomingRequestCategory.read" id="incomingRequestCategoryRead" wire:model="permissions" />
                                            <label class="form-check-label" for="incomingRequestCategoryRead"> Read </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.incomingRequestCategory.update" id="incomingRequestCategoryUpdate" wire:model="permissions" />
                                            <label class="form-check-label" for="incomingRequestCategoryUpdate"> Update </label>
                                        </div>
                                    </div>

                                    <!-- References.Incoming Document Category -->
                                    <li class="d-flex align-items-center py-2 ms-8">
                                        <span class="bullet me-5"></span> Incoming Document Category
                                    </li>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.incomingDocumentCategory.create" id="incomingDocumentCategoryCreate" wire:model="permissions" />
                                            <label class="form-check-label" for="incomingDocumentCategoryCreate"> Create </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.incomingDocumentCategory.read" id="incomingDocumentCategoryRead" wire:model="permissions" />
                                            <label class="form-check-label" for="incomingDocumentCategoryRead"> Read </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.incomingDocumentCategory.update" id="incomingDocumentCategoryUpdate" wire:model="permissions" />
                                            <label class="form-check-label" for="incomingDocumentCategoryUpdate"> Update </label>
                                        </div>
                                    </div>

                                    <!-- References.Outgoing Category -->
                                    <li class="d-flex align-items-center py-2 ms-8">
                                        <span class="bullet me-5"></span> Outgoing Category
                                    </li>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.outgoingCategory.create" id="outgoingCategoryCreate" wire:model="permissions" />
                                            <label class="form-check-label" for="outgoingCategoryCreate"> Create </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.outgoingCategory.read" id="outgoingCategoryRead" wire:model="permissions" />
                                            <label class="form-check-label" for="outgoingCategoryRead"> Read </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.outgoingCategory.update" id="outgoingCategoryUpdate" wire:model="permissions" />
                                            <label class="form-check-label" for="outgoingCategoryUpdate"> Update </label>
                                        </div>
                                    </div>

                                    <!-- References.Divisions -->
                                    <li class="d-flex align-items-center py-2 ms-8">
                                        <span class="bullet me-5"></span> Divisions
                                    </li>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.divisions.create" id="divisionsCreate" wire:model="permissions" />
                                            <label class="form-check-label" for="divisionsCreate"> Create </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.divisions.read" id="divisionsRead" wire:model="permissions" />
                                            <label class="form-check-label" for="divisionsRead"> Read </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.divisions.update" id="divisionsUpdate" wire:model="permissions" />
                                            <label class="form-check-label" for="divisionsUpdate"> Update </label>
                                        </div>
                                    </div>

                                    <!-- References.Accomplishment Category -->
                                    <li class="d-flex align-items-center py-2 ms-8">
                                        <span class="bullet me-5"></span> Accomplishment Category
                                    </li>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.accomplishmentCategory.create" id="accomplishmentCategoryCreate" wire:model="permissions" />
                                            <label class="form-check-label" for="accomplishmentCategoryCreate"> Create </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.accomplishmentCategory.read" id="accomplishmentCategoryRead" wire:model="permissions" />
                                            <label class="form-check-label" for="accomplishmentCategoryRead"> Read </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.accomplishmentCategory.update" id="accomplishmentCategoryUpdate" wire:model="permissions" />
                                            <label class="form-check-label" for="accomplishmentCategoryUpdate"> Update </label>
                                        </div>
                                    </div>

                                    <!-- References.Signatories -->
                                    <li class="d-flex align-items-center py-2 ms-8">
                                        <span class="bullet me-5"></span> Signatories
                                    </li>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.signatories.create" id="signatoriesCreate" wire:model="permissions" />
                                            <label class="form-check-label" for="signatoriesCreate"> Create </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.signatories.read" id="signatoriesRead" wire:model="permissions" />
                                            <label class="form-check-label" for="signatoriesRead"> Read </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.signatories.update" id="signatoriesUpdate" wire:model="permissions" />
                                            <label class="form-check-label" for="signatoriesUpdate"> Update </label>
                                        </div>
                                    </div>

                                    <!-- Continue with other sections using the same pattern -->
                                </div>
                            </div>
                        </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="clear">Close</button>
                    <button type="submit" class="btn btn-primary">{{ $editMode ? 'Update' : 'Create' }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - Users-->
</div>

@script
<script>
    $wire.on('hide-users-modal', () => {
        $('#usersModal').modal('hide');
    });

    $wire.on('show-users-modal', () => {
        $('#usersModal').modal('show');
    });
</script>
@endscript