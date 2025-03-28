<div>
    <!--begin::Mixed Widget 5-->
    <div class="card card-xxl-stretch">
        <!--begin::Beader-->
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1">Teams</span>
                <span class="text-muted fw-bold fs-7">Offices</span>
            </h3>
            <div class="card-toolbar">
                <!-- begin:: Button -->
                <a href="#" class="btn btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#teamsModal"><i class="bi bi-plus-circle"></i></a>
                <!-- end:: Button -->
                <!-- begin::Menu -->
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-bold w-200px py-3" data-kt-menu="true">
                    <!--begin::Heading-->
                    <div class="menu-item px-3">
                        <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">Actions</div>
                    </div>
                    <!--end::Heading-->
                    <!--begin::Menu item-->
                    <div class="menu-item px-3">
                        <a href="#" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#teamsModal">Create</a>
                    </div>
                    <!--end::Menu item-->
                </div>
                <!--end::Menu 3-->
                <!--end::Menu-->
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body d-flex flex-column" style="position: relative;">
            <!--begin::Items-->
            <div class="mt-5 mb-5">
                @forelse($teams as $item)
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
                            <div class="fs-7 text-muted fw-bold mt-1">Team</div>
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Section-->
                    <!--begin::Label-->
                    <!-- <div class="badge badge-light fw-bold py-4 px-3">+82$</div> -->
                    <a href="#" class="btn btn-sm btn-secondary" wire:click="editTeam({{ $item->id }})">Edit</a>
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

    <!--begin::Modal - Teams-->
    <div class="modal fade" tabindex="-1" id="teamsModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Edit' : 'Add' }} Team</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clear">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    <form wire:submit="{{ $editMode ? 'updateTeam' : 'createTeam' }}">
                        <div class="p-2">
                            <div>
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" placeholder="Office" wire:model="name">
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
    <!--end::Modal - Teams-->
</div>

@script
<script>
    $wire.on('hide-teams-modal', () => {
        $('#teamsModal').modal('hide');
    });

    $wire.on('show-teams-modal', () => {
        $('#teamsModal').modal('show');
    });
</script>
@endscript