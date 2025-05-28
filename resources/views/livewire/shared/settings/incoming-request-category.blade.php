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
                            <span class="card-label fw-bolder fs-3 mb-1">Incoming Request Category</span>
                            <span class="text-muted fw-bold fs-7">Over {{ $incoming_request_categories->count() }} categories</span>
                        </h3>
                        <div class="card-toolbar">
                            @can('reference.incomingRequestCategory.create')
                            <!--begin::Menu-->
                            <a href="#" class="btn btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#incomingRequestCategoryModal"><i class="bi bi-plus-circle"></i></a>
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

                        <div class="table-responsive" wire:loading.class="opacity-50" wire:target.except="saveIncomingDocumentCategory">
                            <table class="table align-middle table-hover table-rounded table-striped border gy-7 gs-7">
                                <thead>
                                    <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                                        @role('Super Admin')
                                        <th>Office</th>
                                        @endrole
                                        <th>Name</th>
                                        <th>Status</th>
                                        @can('reference.incomingRequestCategory.update')
                                        <th>Actions</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($incoming_request_categories as $item)
                                    <tr>
                                        @role('Super Admin')
                                        <td>{{ $item->office->name }}</td>
                                        @endrole
                                        <td>{{ $item->incoming_request_category_name }}</td>
                                        <td>
                                            @if(!$item->deleted_at)
                                            <span class="badge badge-light-success">Active</span>
                                            @else
                                            <span class="badge badge-light-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                @can('reference.incomingRequestCategory.update')
                                                <button type="button" class="btn btn-icon btn-sm btn-secondary" title="Edit" wire:click="editIncomingRequestCategory({{ $item->id }})">
                                                    <div wire:loading.remove wire:target="editIncomingRequestCategory({{ $item->id }})">
                                                        <i class="bi bi-pencil"></i>
                                                    </div>

                                                    <div wire:loading wire:target="editIncomingRequestCategory({{ $item->id }})">
                                                        <div class="spinner-border spinner-border-sm" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </div>
                                                </button>

                                                <button
                                                    type="button"
                                                    class="btn btn-icon btn-sm {{ $item->deleted_at ? 'btn-info' : 'btn-danger' }}"
                                                    title="{{ $item->deleted_at ? 'Restore' : 'Delete' }}"
                                                    wire:click="{{ $item->deleted_at ? 'restoreIncomingRequestCategory' : 'deleteIncomingRequestCategory' }}({{ $item->id }})">

                                                    <!-- Show icon when NOT loading -->
                                                    <div wire:loading.remove
                                                        wire:target="{{ $item->deleted_at ? 'restoreIncomingRequestCategory' : 'deleteIncomingRequestCategory' }}({{ $item->id }})">
                                                        <i class="bi {{ $item->deleted_at ? 'bi-arrow-counterclockwise' : 'bi-trash' }}"></i>
                                                    </div>

                                                    <!-- Show spinner when loading -->
                                                    <div wire:loading
                                                        wire:target="{{ $item->deleted_at ? 'restoreIncomingRequestCategory' : 'deleteIncomingRequestCategory' }}({{ $item->id }})">
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
                                        <td colspan="3" class="text-center">No records found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!--begin::Pagination-->
                        <div class="pt-3">
                            {{ $incoming_request_categories->links(data: ['scrollTo' => false]) }}
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

    <!--begin::Modal - Incoming Request Category-->
    <div class="modal fade" tabindex="-1" id="incomingRequestCategoryModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
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
                    <form wire:submit="saveIncomingRequestCategory">
                        <div class="p-2">
                            <div class="mb-10">
                                <label class="form-label required">Name</label>
                                <input type="text" class="form-control" wire:model="incoming_request_category_name">
                                @error('incoming_request_category_name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            @role('Super Admin')
                            <div class="mb-10">
                                <label class="form-label required">Office</label>
                                <select class="form-select" wire:model="office_id">
                                    <option value="">--Select an office--</option>
                                    @foreach ($offices as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('office_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            @endrole
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="clear">Close</button>
                            <div wire:loading.remove>
                                <button type="submit" class="btn btn-primary">{{ $editMode ? 'Update' : 'Create' }}</button>
                            </div>
                            <div wire:loading wire:target="saveIncomingRequestCategory">
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
    <!--end::Modal - Incoming Request Category-->
</div>

@script
<script>
    $wire.on('hide-incoming-request-category-modal', () => {
        $('#incomingRequestCategoryModal').modal('hide');
    });

    $wire.on('show-incoming-request-category-modal', () => {
        $('#incomingRequestCategoryModal').modal('show');
    });
</script>
@endscript