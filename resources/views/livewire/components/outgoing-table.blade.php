<div>
    <!--begin::Mixed Widget 5-->
    <div class="card card-xxl-stretch">
        <!--begin::Header-->
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1">Outgoing</span>
                <span class="text-muted fw-bold fs-7">Over {{ $outgoings->count() }} outgoing documents</span>
            </h3>
            <div class="card-toolbar">
                <div class="d-flex align-items-center gap-2">
                    <!--begin::Menu Filter-->
                    <livewire:components.menu-filter-component />
                    <!--end::Menu Filter-->

                    <!--begin::Menu 2-->
                    @can('outgoing.create')
                    <div class="vr"></div> <!-- Vertical Divider -->
                    <a href="#" class="btn btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#outgoingModal"><i class="bi bi-plus-circle"></i></a>
                    @endcan
                    <!--end::Menu 2-->
                </div>
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

            <div class="table-responsive" wire:loading.class="opacity-50" wire:target.except="saveAccomplishment">
                <table class="table align-middle table-hover table-rounded border gy-7 gs-7">
                    <thead>
                        <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200 bg-light">
                            <th>Document No.</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Document</th>
                            <th>Destination</th>
                            <th>Person Responsible</th>
                            <th>Status</th>
                            @can('outgoing.update')
                            <th class="text-center">Actions</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($outgoings as $item)
                        <tr>
                            @can('outgoing.update')
                            <td class="text-center" wire:loading.class="pe-none">
                                <button type="button" class="btn btn-icon btn-sm btn-secondary" title="Edit" wire:click="editAccomplishment({{ $item->id }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </td>
                            @endcan
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!--begin::Pagination-->
            <div class="pt-3">
                {{ $outgoings->links(data: ['scrollTo' => false]) }}
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

    <!--begin::Modal - Outgoing-->
    <div class="modal fade" tabindex="-1" id="outgoingModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Edit' : 'Add' }} Outgoing</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clear">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    <form wire:submit="saveAccomplishment">
                        <div class="p-2">
                            <div class="mb-10">
                                <label class="form-label required">Type</label>
                                <select class="form-select" aria-label="Type" wire:model.live="type">
                                    <option>--Select--</option>
                                    <option value="voucher">Voucher</option>
                                    <option value="ris">RIS</option>
                                    <option value="procurement">Procurement</option>
                                    <option value="payroll">Payroll</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('type')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div style="display: {{ empty($type) ? 'none' : '' }};">
                                <div class="mb-10" style="display: {{ $editMode ? '' : 'none' }};">
                                    <label class="form-label required">Status</label>
                                    <select class="form-select" aria-label="Type" wire:model="ref_status_id">
                                        <option>--Select--</option>
                                    </select>
                                    @error('type')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                @switch($type)
                                @case('voucher')
                                <div class="mb-10">
                                    <label class="form-label required">Voucher Name</label>
                                    <input type="text" class="form-control" wire:model="voucher">
                                    @error('voucher')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                @break
                                @case('ris')
                                <div class="mb-10">
                                    <label class="form-label required">Document name</label>
                                    <input type="text" class="form-control" wire:model="document_name">
                                    @error('document_name')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-10">
                                    <label class="form-label required">PPMP Code</label>
                                    <input type="text" class="form-control" wire:model="ppmp_code">
                                    @error('ppmp_code')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                @break
                                @case('procurement')
                                <div class="mb-10">
                                    <label class="form-label required">PR No.</label>
                                    <input type="text" class="form-control" wire:model="pr_no">
                                    @error('pr_no')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                @break
                                @case('payroll')
                                <div class="mb-10">
                                    <label class="form-label required">Payroll type</label>
                                    <select class="form-select" aria-label="Type" wire:model="payroll_type">
                                        <option>--Select--</option>
                                    </select>
                                    @error('payroll_type')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                @break
                                @case('other')
                                <div class="mb-10">
                                    <label class="form-label required">Document name</label>
                                    <input type="text" class="form-control" wire:model="document_name">
                                    @error('document_name')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                @break
                                @default
                                @endswitch

                                <div class="mb-10">
                                    <label class="form-label required">Date</label>
                                    <input type="date" class="form-control" wire:model="date">
                                    @error('date')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-10">
                                    <label class="form-label required">Details</label>
                                    <textarea class="form-control" wire:model="details"></textarea>
                                    @error('details')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-10">
                                    <label class="form-label required">Destination</label>
                                    <input type="text" class="form-control" wire:model="destination">
                                    @error('destination')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="mb-10">
                                    <label class="form-label required">Person responsible</label>
                                    <input type="text" class="form-control" wire:model="person_responsible">
                                    @error('person_responsible')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="clear">Close</button>
                    <div wire:loading.remove>
                        <button type="submit" class="btn btn-primary">{{ $editMode ? 'Update' : 'Create' }}</button>
                    </div>
                    </form>
                    <div wire:loading wire:target="saveAccomplishment">
                        <button class="btn btn-primary" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                            <span role="status">Loading...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Modal - Outgoing-->
    </div>

    @script
    <script>

    </script>
    @endscript