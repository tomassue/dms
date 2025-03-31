<div>
    <!--begin::Mixed Widget 5-->
    <div class="card card-xxl-stretch">
        <!--begin::Beader-->
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1">Permissions</span>
                <span class="text-muted fw-bold fs-7">Roles</span>
            </h3>
            <div class="card-toolbar">
                <!--begin::Menu-->
                <a href="#" class="btn btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#permissionsModal"><i class="bi bi-plus-circle"></i></a>
                <!--end::Menu-->
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Body-->
        <div class="card-body d-flex flex-column" style="position: relative;">

            <div class="table-responsive">
                <table class="table table-hover table-rounded table-striped border gy-7 gs-7">
                    <thead>
                        <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                            <th>Permissions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="1" class="text-center">No permissions found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!--begin::Pagination-->
            <div class="pt-3">
                {{ $permissions->links() }}
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

    <!--begin::Modal - Permissions-->
    <div class="modal fade" tabindex="-1" id="permissionsModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Edit' : 'Add' }} Permission</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clear">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    <form wire:submit="{{ $editMode ? 'updatePermission' : 'createPermission' }}">
                        <div class="p-2">
                            <div>
                                <label class="form-label">Name</label>
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
    <!--end::Modal - Permissions-->
</div>

@script
<script>
    $wire.on('show-permissions-modal', () => {
        $('#permissionsModal').modal('show');
    });

    $wire.on('hide-permissions-modal', () => {
        $('#permissionsModal').modal('hide');
    });
</script>
@endscript