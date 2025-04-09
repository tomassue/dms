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
                            <span class="card-label fw-bolder fs-3 mb-1">Accomplishment</span>
                            <span class="text-muted fw-bold fs-7">Management</span>
                        </h3>
                        <div class="card-toolbar">
                            @can('accomplishments.create')
                            <!--begin::Menu-->
                            <a href="#" class="btn btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#accomplishmentModal"><i class="bi bi-plus-circle"></i></a>
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
                                        <th>Accomplishment Category</th>
                                        <th>Date</th>
                                        <th>Details</th>
                                        @role('APO')
                                        <th>Next Steps</th>
                                        @endrole
                                        @can('accomplishments.update')
                                        <th>Actions</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($accomplishments as $item)
                                    <tr>
                                        <td>
                                            {{ $item->accomplishment_category->name }}
                                        </td>
                                        <td>
                                            @role('APO')
                                            {{ $item->apo->start_date_formatted }}
                                            @else
                                            {{ $item->date }}
                                            @endrole
                                        </td>
                                        <td>
                                            {{ $item->details }}
                                        </td>
                                        @role('APO')
                                        <td>
                                            {{ $item->apo->next_steps }}
                                        </td>
                                        @endrole
                                        @can('accomplishments.update')
                                        <td>
                                            <a href="#" class="btn btn-icon btn-sm btn-secondary" title="Edit" wire:click="editAccomplishment({{ $item->id }})">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </td>
                                        @endcan
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
                            {{ $accomplishments->links(data: ['scrollTo' => false]) }}
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

    <!--begin::Modal - Accomplishment-->
    <div class="modal fade" tabindex="-1" id="accomplishmentModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Edit' : 'Add' }} Accomplishment</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clear">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    <form wire:submit="{{ $editMode ? 'updateAccomplishment' : 'createAccomplishment' }}">
                        <div class="p-2">
                            <div class="mb-10">
                                <label class="form-label required">Accomplishment Category</label>
                                <select class="form-select" aria-label="Select example" wire:model="ref_accomplishment_category_id">
                                    <option>Open this select menu</option>
                                    @foreach ($accomplishment_categories as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('ref_accomplishment_category_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            @role('APO')
                            <div class="mb-10">
                                <label class="form-label required">Start Date</label>
                                <input type="date" class="form-control" wire:model="start_date">
                                @error('start_date')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">End Date</label>
                                <input type="date" class="form-control" wire:model="end_date">
                                @error('end_date')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            @else
                            <div class="mb-10">
                                <label class="form-label required">Date</label>
                                <input type="date" class="form-control" wire:model="date">
                                @error('date')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            @endrole

                            <div class="mb-10">
                                <label class="form-label required">Details</label>
                                <!-- <input type="text" class="form-control" wire:model="details"> -->
                                <textarea class="form-control" wire:model="details"></textarea>
                                @error('details')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            @role('APO')
                            <div class="mb-10">
                                <label class="form-label required">Next Steps</label>
                                <!-- <input type="text" class="form-control" wire:model="next_steps"> -->
                                <textarea class="form-control" wire:model="next_steps"></textarea>
                                @error('next_steps')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            @endrole
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="clear">Close</button>
                            <button type="submit" class="btn btn-primary">{{ $editMode ? 'Update' : 'Create' }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - Accomplishment-->
</div>

@script
<script>
    $wire.on('hide-accomplishment-modal', () => {
        $('#accomplishmentModal').modal('hide');
    });

    $wire.on('show-accomplishment-modal', () => {
        $('#accomplishmentModal').modal('show');
    });
</script>
@endscript