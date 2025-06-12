<div>
    <!--begin::Content-->
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Container-->
        <div class="container-xxl" id="kt_content_container">
            <!--begin::Row-->
            <div class="row g-5 g-xl-12">
                <!--begin::Mixed Widget 5-->
                <div class="card card-xxl-stretch" wire:loading.class="opacity-50 pe-none">
                    <!--begin::Beader-->
                    <div class="card-header border-0 py-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder fs-3 mb-1">Positions</span>
                            <span class="text-muted fw-bold fs-7">Reference</span>
                        </h3>
                        <div class="card-toolbar">
                            @can('reference.divisions.create')
                            <!--begin::Menu-->
                            <a href="#" class="btn btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#positionModal"><i class="bi bi-plus-circle"></i></a>
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
                                        @role('Super Admin')
                                        <th>Office</th>
                                        @endrole
                                        <th>Name</th>
                                        <th>Status</th>
                                        @can('reference.position.update')
                                        <th>Actions</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($positions as $item)
                                    <tr>
                                        @role('Super Admin')
                                        <td>{{ $item->office_id }}</td>
                                        @endrole
                                        <td>{{ $item->position_name }}</td>
                                        <td><span class="badge {{ $item->deleted_at ? 'badge-light-danger' : 'badge-light-success' }}">{{ $item->deleted_at ? 'Inactive' : 'Active' }}</span></td>
                                        <td>
                                            @can('reference.position.update')
                                            <div class="btn-group" role="group" aria-label="Actions">
                                                <button class="btn btn-icon btn-sm btn-secondary" title="Edit" wire:click="editPosition({{ $item->id }})">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <button
                                                    class="btn btn-icon btn-sm {{ $item->deleted_at ? 'btn-info' : 'btn-danger' }}"
                                                    title="Delete"
                                                    wire:click="{{ $item->deleted_at ? 'restorePosition' : 'deletePosition' }}({{ $item->id }})">
                                                    <i class="bi {{ $item->deleted_at ? 'bi-arrow-counterclockwise' : 'bi-trash' }}"></i>
                                                </button>
                                            </div>
                                            @endcan
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
                            {{ $positions->links(data: ['scrollTo' => false]) }}
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

    <!--begin::Modal - Position-->
    <div class="modal fade" tabindex="-1" id="positionModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Edit' : 'Add' }} Position</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clear">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    <form wire:submit="savePosition">
                        <div class="p-2">
                            @role('Super Admin')
                            <!-- begin:: Alert -->
                            <div class="alert alert-dismissible bg-light-info border border-info border-3 border-dashed d-flex flex-column flex-sm-row w-100 p-5 mb-10">
                                <!--begin::Icon-->
                                <!--begin::Svg Icon | path: icons/duotune/general/gen007.svg-->
                                <span class="svg-icon svg-icon-2hx svg-icon-info me-4 mb-5 mb-sm-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path opacity="0.3" d="M12 22C13.6569 22 15 20.6569 15 19C15 17.3431 13.6569 16 12 16C10.3431 16 9 17.3431 9 19C9 20.6569 10.3431 22 12 22Z" fill="black"></path>
                                        <path d="M19 15V18C19 18.6 18.6 19 18 19H6C5.4 19 5 18.6 5 18V15C6.1 15 7 14.1 7 13V10C7 7.6 8.7 5.6 11 5.1V3C11 2.4 11.4 2 12 2C12.6 2 13 2.4 13 3V5.1C15.3 5.6 17 7.6 17 10V13C17 14.1 17.9 15 19 15ZM11 10C11 9.4 11.4 9 12 9C12.6 9 13 8.6 13 8C13 7.4 12.6 7 12 7C10.3 7 9 8.3 9 10C9 10.6 9.4 11 10 11C10.6 11 11 10.6 11 10Z" fill="black"></path>
                                    </svg>
                                </span>
                                <!--end::Svg Icon-->
                                <!--end::Icon-->
                                <!--begin::Content-->
                                <div class="d-flex flex-column pe-0 pe-sm-10">
                                    <h5 class="mb-1">Note</h5>
                                    <span>Select an office for a specific position for that office. If it's a general position, leave it blank.</span>
                                </div>
                                <!--end::Content-->
                            </div>
                            <!-- end:: Alert -->
                            <div class="mb-10">
                                <label class="form-label required">Role/Office</label>
                                <select class="form-select" aria-label="Select example" wire:model="office_id">
                                    <option>-Select an office-</option>
                                    @foreach ($roles as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('office_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            @endrole
                            <div class="mb-10">
                                <label class="form-label required">Name</label>
                                <input type="text" class="form-control" wire:model="position_name">
                                @error('position_name')
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
    <!--end::Modal - Position-->
</div>

@script
<script>
    $wire.on('hide-position-modal', () => {
        $('#positionModal').modal('hide');
    });

    $wire.on('show-position-modal', () => {
        $('#positionModal').modal('show');
    });
</script>
@endscript