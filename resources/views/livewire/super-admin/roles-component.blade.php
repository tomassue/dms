<div>
    <!--begin::Mixed Widget 5-->
    <div class="card card-xxl-stretch">
        <!--begin::Beader-->
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1">Roles</span>
                <span class="text-muted fw-bold fs-7">Teams</span>
            </h3>
            <div class="card-toolbar">
                <!--begin::Menu-->
                <a href="#" class="btn btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#rolesModal"><i class="bi bi-plus-circle"></i></a>
                <!--end::Menu-->
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body d-flex flex-column" style="position: relative;">
            <!--begin::Items-->
            <div class="mt-5 mb-5">
                @forelse($roles as $item)
                <!--begin::Item-->
                <div class="d-flex flex-stack mb-5">
                    <!--begin::Section-->
                    <div class="d-flex align-items-center me-2">
                        <!--begin::Symbol-->
                        <div class="symbol symbol-50px me-3">
                            <div class="symbol-label bg-light">
                                <div class="profile-picture bg-color-{{ $item->id % 5 + 1 }}">
                                    {{ strtoupper(substr($item->name, 0, 1)) }}
                                </div>
                            </div>
                        </div>
                        <!--end::Symbol-->
                        <!--begin::Title-->
                        <div>
                            <a href="#" class="fs-6 text-gray-800 text-hover-primary fw-bolder">{{ $item->name }}</a>
                            <div class="fs-7 text-muted fw-bold mt-1">{{ $item->team->name ?? '' }}</div>
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Section-->
                    <!--begin::Label-->
                    <!-- <div class="badge badge-light fw-bold py-4 px-3">+82$</div> -->
                    <a href="#" class="btn btn-sm btn-secondary" wire:click="editRole({{ $item->id }})">Edit</a>
                    <!--end::Label-->
                </div>
                <!--end::Item-->
                @empty
                <div class="text-muted">No teams found.</div>
                @endforelse
            </div>
            <!--end::Items-->

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

    <!--begin::Modal - Roles-->
    <div class="modal fade" tabindex="-1" id="rolesModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Edit' : 'Add' }} Role</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clear">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    <form wire:submit="{{ $editMode ? 'updateRole' : 'createRole' }}">
                        <div class="p-2">
                            <div class="mb-10">
                                <label class="form-label required">Name</label>
                                <input type="text" class="form-control" wire:model="name">
                                @error('name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Team</label>
                                <select class="form-select" aria-label="Select example" wire:model="team_id">
                                    <option>-Select an office-</option>
                                    @foreach ($teams as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('team_id')
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
                                    <li class="d-flex align-items-center py-2">
                                        <span class="bullet me-5"></span> Dashboard
                                    </li>
                                    <div class="row py-2 ms-4">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="read dashboard" id="readDashboard" wire:model="permissions" />
                                            <label class="form-check-label" for="readDashboard"> Read </label>
                                        </div>
                                    </div>

                                    <li class="d-flex align-items-center py-2">
                                        <span class="bullet me-5"></span> Incoming Requests
                                    </li>
                                    <div class="row py-2 ms-4">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="create incoming requests" id="createIncomingRequests" wire:model="permissions" />
                                            <label class="form-check-label" for="createIncomingRequests"> Create </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-4">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="read incoming requests" id="readIncomingRequests" wire:model="permissions" />
                                            <label class="form-check-label" for="readIncomingRequests"> Read </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-4">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="update incoming requests" id="updateIncomingRequests" wire:model="permissions" />
                                            <label class="form-check-label" for="updateIncomingRequests"> Update </label>
                                        </div>
                                    </div>

                                    <li class="d-flex align-items-center py-2">
                                        <span class="bullet me-5"></span> Incoming Documents
                                    </li>
                                    <div class="row py-2 ms-4">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="create incoming documents" id="createIncomingDocuments" wire:model="permissions" />
                                            <label class="form-check-label" for="createIncomingDocuments"> Create </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-4">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="read incoming documents" id="readIncomingDocuments" wire:model="permissions" />
                                            <label class="form-check-label" for="readIncomingDocuments"> Read </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-4">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="update incoming documents" id="updateIncomingDocuments" wire:model="permissions" />
                                            <label class="form-check-label" for="updateIncomingDocuments"> Update </label>
                                        </div>
                                    </div>

                                    <li class="d-flex align-items-center py-2">
                                        <span class="bullet me-5"></span> Outgoing
                                    </li>
                                    <div class="row py-2 ms-4">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="create outgoing" id="createOutgoing" wire:model="permissions" />
                                            <label class="form-check-label" for="createOutgoing"> Create </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-4">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="read outgoing" id="readOutgoing" wire:model="permissions" />
                                            <label class="form-check-label" for="readOutgoing"> Read </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-4">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="update outgoing" id="updateIncomingRequests" wire:model="permissions" />
                                            <label class="form-check-label" for="updateIncomingRequests"> Update </label>
                                        </div>
                                    </div>

                                    <li class="d-flex align-items-center py-2">
                                        <span class="bullet me-5"></span> Calendar
                                    </li>
                                    <div class="row py-2 ms-4">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="read calendar" id="readCalendar" wire:model="permissions" />
                                            <label class="form-check-label" for="readCalendar"> Read </label>
                                        </div>
                                    </div>

                                    <li class="d-flex align-items-center py-2">
                                        <span class="bullet me-5"></span> Accomplishments
                                    </li>
                                    <div class="row py-2 ms-4">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="create accomplishments" id="createAccomplishments" wire:model="permissions" />
                                            <label class="form-check-label" for="createAccomplishments"> Create </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-4">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="read accomplishments" id="readAccomplishments" wire:model="permissions" />
                                            <label class="form-check-label" for="readAccomplishments"> Read </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-4">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="update accomplishments" id="updateAccomplishments" wire:model="permissions" />
                                            <label class="form-check-label" for="updateAccomplishments"> Update </label>
                                        </div>
                                    </div>

                                    <li class="d-flex align-items-center py-2">
                                        <span class="bullet me-5"></span> References
                                    </li>
                                    <div class="row py-2 ms-4">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="read references" id="readReferences" wire:model="permissions" />
                                            <label class="form-check-label" for="readReferences"> Read </label>
                                        </div>
                                    </div>

                                    <li class="d-flex align-items-center py-2 ms-4">
                                        <span class="bullet me-5"></span> User Management
                                    </li>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="create user" id="createUser" wire:model="permissions" />
                                            <label class="form-check-label" for="createUser"> Create </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="read user management" id="readUserManagement" wire:model="permissions" />
                                            <label class="form-check-label" for="readUserManagement"> Read </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="update user" id="updateUser" wire:model="permissions" />
                                            <label class="form-check-label" for="updateUser"> Update </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="delete user" id="deleteUser" wire:model="permissions" />
                                            <label class="form-check-label" for="deleteUser"> Delete </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="read user permissions" id="readUserPermissions" wire:model="permissions" />
                                            <label class="form-check-label" for="readUserPermissions"> Read user permissions </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="update user permissions" id="updateUserPermissions" wire:model="permissions" />
                                            <label class="form-check-label" for="updateUserPermissions"> Update user permissions </label>
                                        </div>
                                    </div>

                                    <!-- Reference: permissions -->
                                    <li class="d-flex align-items-center py-2 ms-4">
                                        <span class="bullet me-5"></span> User Permissions
                                    </li>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="create permissions" id="createPermissions" wire:model="permissions" />
                                            <label class="form-check-label" for="createPermissions"> Create </label>
                                        </div>
                                    </div>
                                    <div class="row py-2 ms-8">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="read permissions" id="readPermissions" wire:model="permissions" />
                                            <label class="form-check-label" for="readPermissions"> Read </label>
                                        </div>
                                    </div>
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
    <!--end::Modal - Roles-->
</div>

@script
<script>
    $wire.on('hide-roles-modal', () => {
        $('#rolesModal').modal('hide');
    });

    $wire.on('show-roles-modal', () => {
        $('#rolesModal').modal('show');
    });
</script>
@endscript