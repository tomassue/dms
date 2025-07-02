<div>
    <!--begin::Mixed Widget 5-->
    <div class="card card-xxl-stretch">
        <!--begin::Beader-->
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1">Roles</span>
                <span class="text-muted fw-bold fs-7">Offices</span>
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
                            <div class="fs-7 text-muted fw-bold mt-1"> </div>
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Section-->
                    <!--begin::Label-->
                    <!-- <div class="badge badge-light fw-bold py-4 px-3">+82$</div> -->
                    <!-- EDIT IS HIDDEN FOR NOW -->
                    <a href="#" class="btn btn-sm btn-secondary d-none" wire:click="editRole({{ $item->id }})">Edit</a>
                    <!--end::Label-->
                </div>
                <!--end::Item-->
                @empty
                <div class="text-muted">No roles found.</div>
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