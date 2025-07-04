<div>
    <!--begin::Content-->
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Container-->
        <div class="container-xxl" id="kt_content_container">
            <!--begin::Row-->
            <div class="row g-5 g-xl-12">
                <!--begin::Mixed Widget 5-->
                <div class="card card-xxl-stretch" wire:loading.class="opacity-50 pe-none" wire:target.except="addSpeciesInput, removeSpeciesInput">
                    <!--begin::Beader-->
                    <div class="card-header border-0 py-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder fs-3 mb-1">Accomplishment Sub-category</span>
                            <span class="text-muted fw-bold fs-7">Management</span>
                        </h3>
                        <div class="card-toolbar">
                            @can('reference.accomplishmentSubCategory.create')
                            <!--begin::Menu-->
                            <a href="#" class="btn btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#accomplishmentSubcategoryModal" wire:click="getLatestOrder"><i class="bi bi-plus-circle"></i></a>
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
                                        <th>Category</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        @can('reference.accomplishmentSubCategory.update')
                                        <th>Actions</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($accomplishment_subcategories as $item)
                                    <tr>
                                        @role('Super Admin')
                                        <td>{{ $item->office->name ?? 'System' }}</td>
                                        @endrole
                                        <td>
                                            <div class="d-flex justify-content-start flex-column">
                                                <span class="text-gray-800 fw-bold mb-1 fs-6">{{ $item->category->accomplishment_category_name }}</span>
                                                <span class="text-gray-500 fw-semibold d-block fs-7">
                                                    {{ $item->species()->pluck('species_name')->implode(', ') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-start flex-column">
                                                <span class="text-gray-800 fw-bold mb-1 fs-6">{{ $item->parent->accomplishment_sub_category_name ?? '' }}</span>
                                                <span class="text-gray-500 fw-semibold d-block fs-7">
                                                    {{ $item->accomplishment_sub_category_name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            @if(!$item->deleted_at)
                                            <span class="badge badge-light-success">Active</span>
                                            @else
                                            <span class="badge badge-light-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="Actions">
                                                @can('reference.accomplishmentSubCategory.update')
                                                <button
                                                    class="btn btn-icon btn-sm btn-secondary"
                                                    title="Edit"
                                                    wire:click="editAccomplishmentSubcategory({{ $item->id }})">
                                                    <div wire:loading.remove wire:target="editAccomplishmentSubcategory({{ $item->id }})">
                                                        <i class="bi bi-pencil"></i>
                                                    </div>
                                                    <div wire:loading wire:target="editAccomplishmentSubcategory({{ $item->id }})">
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
                            {{ $accomplishment_subcategories->links(data: ['scrollTo' => false]) }}
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

    <!--begin::Modal - Accomplishment Subcategory-->
    <div class="modal fade" tabindex="-1" id="accomplishmentSubcategoryModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Edit' : 'Add' }} Sub-category</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clear">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <!--end::Close-->
                </div>

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
                    <form wire:submit="saveAccomplishmentSubcategory">
                        <div class="p-2">
                            {{-- Category Dropdown --}}
                            <div class="mb-10">
                                <label class="form-label required">Category</label>
                                {{-- Use wire:model.live to update options for parent_sub_category_id dynamically --}}
                                <select class="form-select" aria-label="Select Category" wire:model.live="ref_accomplishment_category_id">
                                    <option value="">Open this select menu</option>
                                    @foreach($accomplishment_categories as $item)
                                    <option value="{{ $item->id }}">{{ $item->accomplishment_category_name }}</option>
                                    @endforeach
                                </select>
                                @error('ref_accomplishment_category_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- NEW: Parent Sub-category Dropdown --}}
                            {{-- Show this dropdown only if a main category is selected --}}
                            @if($ref_accomplishment_category_id)
                            <div class="mb-10">
                                <label class="form-label">Parent Sub-category (Optional)</label>
                                <select class="form-select" aria-label="Select Parent Sub-category" wire:model="parent_sub_category_id">
                                    <option value="">No Parent (Top-level Sub-category)</option>
                                    @foreach($parent_sub_categories as $item)
                                    <option value="{{ $item->id }}">{{ $item->accomplishment_sub_category_name }}</option>
                                    @endforeach
                                </select>
                                @error('parent_sub_category_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            @endif

                            {{-- Sub-category Name --}}
                            <div class="mb-10">
                                <label class="form-label required">Name</label>
                                <input type="text" class="form-control" wire:model="accomplishment_sub_category_name">
                                @error('accomplishment_sub_category_name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            @role('CITY VETERINARY OFFICE')
                            {{-- Order --}}
                            <div class="mb-10">
                                <label class="form-label required">Order</label>
                                <input type="text" class="form-control" oninput="this.value = this.value.replace(/[^0-9]/g, '')" wire:model="order">
                                @error('order')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Species Inputs --}}
                            <div class="mb-10">
                                <label class="form-label d-flex justify-content-between align-items-center">
                                    <span>Species</span>
                                </label>
                                {{-- Loop through dynamic species input fields --}}
                                @forelse ($speciesInputs as $index => $speciesInput)
                                <div class="input-group mb-3" wire:key="species-{{ $index }}">
                                    <input type="text" class="form-control" placeholder="Species Name" wire:model.live="speciesInputs.{{ $index }}.species_name">
                                    <button type="button" class="btn btn-icon btn-danger" wire:click="removeSpeciesInput({{ $index }})">
                                        <div wire:loading.remove wire:target="removeSpeciesInput({{ $index }})">
                                            <i class="bi bi-x"></i>
                                        </div>
                                        <div wire:loading wire:target="removeSpeciesInput({{ $index }})">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </button>
                                    @error('speciesInputs.' . $index . '.species_name') {{-- Correct path for dynamic input validation --}}
                                    <span class="text-danger mt-1 d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                @empty
                                <p class="text-muted">Click "Add Species" to add the first species.</p>
                                @endforelse

                                <button type="button" class="btn btn-sm btn-light-primary" wire:click="addSpeciesInput">
                                    <div wire:loading.remove wire:target="addSpeciesInput">
                                        Add Species
                                    </div>
                                    <div wire:loading wire:target="addSpeciesInput">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                </button>
                            </div>
                            @endrole

                            @role('Super Admin')
                            {{-- Office Dropdown --}}
                            <div class="mb-10">
                                <label class="form-label required">Office</label>
                                <select class="form-select" aria-label="Select Office" wire:model="office_id">
                                    <option value="">Open this select menu</option>
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
    <!--end::Modal - Accomplishment Subcategory-->
</div>

@script
<script>
    $wire.on('hide-accomplishment-subcategory-modal', () => {
        $('#accomplishmentSubcategoryModal').modal('hide');
    });

    $wire.on('show-accomplishment-subcategory-modal', () => {
        $('#accomplishmentSubcategoryModal').modal('show');
    });
</script>
@endscript