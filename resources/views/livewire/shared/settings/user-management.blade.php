<div>
    <!--begin::Content-->
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Container-->
        <div class="container-xxl" id="kt_content_container">
            <!--begin::Row-->
            <div class="row g-5 g-xl-12">
                <!--begin::Mixed Widget 5-->
                <div class="card card-xxl-stretch" wire:loading.class="opacity-25 pe-none" wire:target.except="createUser, updateUser">
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
                                        <td>{{ $item->user_metadata->position->position_name ?? '-' }}</td>
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
                                                    <div wire:loading.remove wire:target="editUser({{ $item->id }})">
                                                        <i class="bi bi-pencil"></i>
                                                    </div>
                                                    <div wire:loading wire:target="editUser({{ $item->id }})">
                                                        <div class="spinner-border spinner-border-sm" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </div>
                                                </a>

                                                <a href="#" class="btn btn-icon btn-sm btn-warning" title="Reset Password" wire:click="resetPasswordUser({{ $item->id }})">
                                                    <div wire:loading.remove wire:target="resetPasswordUser({{ $item->id }})">
                                                        <i class="bi bi-key"></i>
                                                    </div>
                                                    <div wire:loading wire:target="resetPasswordUser({{ $item->id }})">
                                                        <div class="spinner-border spinner-border-sm" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </div>
                                                </a>

                                                <a
                                                    href="#"
                                                    class="btn btn-icon btn-sm {{ $item->deleted_at ? 'btn-info' : 'btn-danger' }}"
                                                    title="Delete"
                                                    wire:click="{{ $item->deleted_at ? 'restoreUser' : 'deleteUser' }}({{ $item->id }})">
                                                    <div wire:loading.remove wire:target="{{ $item->deleted_at ? 'restoreUser' : 'deleteUser' }}({{ $item->id }})">
                                                        <i class="bi {{ $item->deleted_at ? 'bi-arrow-counterclockwise' : 'bi-trash' }}"></i>
                                                    </div>
                                                    <div wire:loading wire:target="{{ $item->deleted_at ? 'restoreUser' : 'deleteUser' }}({{ $item->id }})">
                                                        <div class="spinner-border spinner-border-sm" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </div>
                                                </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No records found.</td>
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
                * TODO: Make the permissions associated to a specific role; hidden to other users from other roles
                * ! When office is selected, division and position options should be updated.
                */
                @endphp
                <div class="modal-body">
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <form wire:submit="{{ $editMode ? 'updateUser' : 'createUser' }}">
                        <div class="p-2">
                            <div class="mb-10" style="display: {{ $editMode ? '' : 'none' }};">
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

                            @role('Super Admin')
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
                            @endrole

                            <div class="mb-10">
                                <label class="form-label">Division / Title</label>
                                <select class="form-select" aria-label="Select example" wire:model="ref_division_id">
                                    <option>-Select a division-</option>
                                    @foreach ($divisions as $item)
                                    <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('ref_division_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-10">
                                <label class="form-label">Position</label>
                                <select class="form-select" aria-label="Select example" wire:model="ref_position_id">
                                    <option>-Select a position-</option>
                                    @foreach ($positions as $item)
                                    <option value="{{ $item->position_id }}">{{ $item->position_name }}</option>
                                    @endforeach
                                </select>
                                @error('ref_position_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            @role('Super Admin')
                            <div class="mb-10">
                                <label class="form-label required">Is Office Admin</label>
                                <select class="form-select" aria-label="Select example" wire:model="is_office_admin">
                                    <option>-Select-</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                @error('is_office_admin')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            @endrole

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

                                    <!-- References.Position -->
                                    <li class="d-flex align-items-center py-2 ms-8">
                                        <span class="bullet me-5"></span> Position
                                    </li>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.position.create" id="divisionsCreate" wire:model="permissions" />
                                            <label class="form-check-label" for="divisionsCreate"> Create </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.position.read" id="divisionsRead" wire:model="permissions" />
                                            <label class="form-check-label" for="divisionsRead"> Read </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-16">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="reference.position.update" id="divisionsUpdate" wire:model="permissions" />
                                            <label class="form-check-label" for="divisionsUpdate"> Update </label>
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

                                    @php
                                    //$roleName = null; // Ensure it's defined

                                    //if ($editMode && $userId) {
                                    //$user = App\Models\User::find($userId); // Avoid findOrFail to prevent crashing
                                    //$roleName = $user?->roles->pluck('name')->first(); // Use null-safe operator
                                    //}
                                    @endphp
                                    <div style="display: {{ $role_id == '1' ? '' : 'none' }};">
                                        <div class="separator my-10">APOO</div>

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

                                        <!-- Meeting -->
                                        <li class="d-flex align-items-center py-2">
                                            <span class="bullet me-5"></span> Meetings
                                        </li>
                                        <div class="row py-2 ms-8">
                                            <div class="form-check form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" value="meeting.create" id="meetingCreate" wire:model="permissions" />
                                                <label class="form-check-label" for="meetingCreate"> Create </label>
                                            </div>
                                        </div>
                                        <div class="row py-2 ms-8">
                                            <div class="form-check form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" value="meeting.read" id="meetingRead" wire:model="permissions" />
                                                <label class="form-check-label" for="meetingRead"> Read </label>
                                            </div>
                                        </div>
                                        <div class="row py-2 ms-8">
                                            <div class="form-check form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" value="meeting.update" id="meetingUpdate" wire:model="permissions" />
                                                <label class="form-check-label" for="meetingUpdate"> Update </label>
                                            </div>
                                        </div>

                                        <!-- Minutes of Meeting -->
                                        <li class="d-flex align-items-center py-2 ms-8">
                                            <span class="bullet me-5"></span> Min. of Meeting
                                        </li>
                                        <div class="row py-2 ms-16">
                                            <div class="form-check form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" value="minutesOfMeeting.create" id="minutesOfMeetingCreate" wire:model="permissions" />
                                                <label class="form-check-label" for="minutesOfMeetingCreate"> Create </label>
                                            </div>
                                        </div>
                                        <div class="row py-2 ms-16">
                                            <div class="form-check form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" value="minutesOfMeeting.read" id="minutesOfMeetingRead" wire:model="permissions" />
                                                <label class="form-check-label" for="minutesOfMeetingRead"> Read </label>
                                            </div>
                                        </div>
                                        <div class="row py-2 ms-16">
                                            <div class="form-check form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" value="minutesOfMeeting.update" id="minutesOfMeetingUpdate" wire:model="permissions" />
                                                <label class="form-check-label" for="minutesOfMeetingUpdate"> Update </label>
                                            </div>
                                        </div>

                                        <div class="separator my-10">APOO</div>
                                    </div>

                                    <div style="display: {{ $role_id == '2' ? '' : 'none' }};">
                                        <div class="separator my-10">City Veterinary</div>
                                        -- Nothing --
                                        <div class="separator my-10">City Veterinary</div>
                                    </div>

                                    <!-- Continue with other sections using the same pattern -->
                                </div>
                            </div>
                        </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="clear">
                        Close
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <div wire:loading.remove wire:target="{{ $editMode ? 'updateUser' : 'createUser' }}">
                            {{ $editMode ? 'Update' : 'Create' }}
                        </div>
                        <div wire:loading wire:target="{{ $editMode ? 'updateUser' : 'createUser' }}">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </button>
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