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
                            <span class="card-label fw-bolder fs-3 mb-1">Accomplishment Category</span>
                            <span class="text-muted fw-bold fs-7">Management</span>
                        </h3>
                        <div class="card-toolbar">
                            @can('reference.accomplishmentCategory.create')
                            <!--begin::Menu-->
                            <a href="#" class="btn btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#accomplishmentCategoryModal"><i class="bi bi-plus-circle"></i></a>
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
                                        @can('reference.accomplishmentCategory.update')
                                        <th>Actions</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($accomplishment_categories as $item)
                                    <tr>
                                        @role('Super Admin')
                                        <td>{{ $item->office->name ?? 'System' }}</td>
                                        @endrole
                                        <td>{{ $item->accomplishment_category_name }}</td>
                                        <td>
                                            @if(!$item->deleted_at)
                                            <span class="badge badge-light-success">Active</span>
                                            @else
                                            <span class="badge badge-light-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="Actions">
                                                @can('reference.accomplishmentCategory.update')
                                                <button class=" btn btn-icon btn-sm btn-secondary" title="Edit" wire:click="editAccomplishmentCategory({{ $item->id }})">
                                                    <div wire:loading.remove wire:target="editAccomplishmentCategory({{ $item->id }})">
                                                        <i class="bi bi-pencil"></i>
                                                    </div>
                                                    <div wire:loading wire:target="editAccomplishmentCategory({{ $item->id }})">
                                                        <div class="spinner-border spinner-border-sm" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </div>
                                                </button>

                                                <button
                                                    class="btn btn-icon btn-sm {{ $item->deleted_at ? 'btn-info' : 'btn-danger' }}"
                                                    title="Delete"
                                                    wire:click="{{ $item->deleted_at ? 'restoreAccomplishmentCategory' : 'deleteAccomplishmentCategory' }}({{ $item->id }})">
                                                    <div wire:loading.remove wire:target="deleteAccomplishmentCategory({{ $item->id }})">
                                                        <i class="bi {{ $item->deleted_at ? 'bi-arrow-counterclockwise' : 'bi-trash' }}"></i>
                                                    </div>
                                                    <div wire:loading wire:target="deleteAccomplishmentCategory({{ $item->id }})">
                                                        <div class="spinner-border spinner-border-sm" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </div>
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No records found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!--begin::Pagination-->
                        <div class="pt-3">
                            {{ $accomplishment_categories->links(data: ['scrollTo' => false]) }}
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

    <!--begin::Modal - Accomplishment Category-->
    <div class="modal fade" tabindex="-1" id="accomplishmentCategoryModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Edit' : 'Add' }} Category</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clear">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    <form wire:submit="{{ $editMode ? 'updateAccomplishmentCategory' : 'createAccomplishmentCategory' }}">
                        <div class="p-2">
                            <div class="mb-10">
                                <label class="form-label required">Name</label>
                                <input type="text" class="form-control" wire:model="accomplishment_category_name">
                                @error('accomplishment_category_name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            @role('Super Admin')
                            <div class="mb-10">
                                <label class="form-label required">Office</label>
                                <select class="form-select" aria-label="Select example" wire:model="office_id">
                                    <option>Open this select menu</option>
                                    @foreach($offices as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('office_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            @endrole
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="clear">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <div wire:loading.remove wire:target="{{ $editMode ? 'updateAccomplishmentCategory' : 'createAccomplishmentCategory' }}">
                            {{ $editMode ? 'Update' : 'Create' }}
                        </div>
                        <div wire:loading wire:target="{{ $editMode ? 'updateAccomplishmentCategory' : 'createAccomplishmentCategory' }}">
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
    <!--end::Modal - Accomplishment Category-->
</div>

@script
<script>
    $wire.on('hide-accomplishment-category-modal', () => {
        $('#accomplishmentCategoryModal').modal('hide');
    });

    $wire.on('show-accomplishment-category-modal', () => {
        $('#accomplishmentCategoryModal').modal('show');
    });
</script>
@endscript