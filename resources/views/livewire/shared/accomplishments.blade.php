<div>
    <!--begin::Content-->
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Container-->
        <div class="container-xxl" id="kt_content_container">
            <!--begin::Row-->
            <div class="row g-5 g-xl-12">
                <div class=" col-lg-4">
                    <!--begin::Mixed Widget 5-->
                    <div class="card">
                        <!--begin::Header-->
                        <div class="card-header border-0 py-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bolder fs-3 mb-1">Add Accomplishment</span>
                                <span class="text-muted fw-bold fs-7"></span>
                            </h3>
                        </div>
                        <!--end::Header-->

                        <!--begin::Body-->
                        <div class="card-body d-flex flex-column" style="position: relative; padding-top: unset;">
                            <div id="kt_customer_view_details" class="collapse show">
                                <form wire:submit="saveAccomplishment">
                                    <div class="p-2">
                                        <div class="mb-10">
                                            @role('APOO')
                                            <div class="row g-5">
                                                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-12 col-xl-6">
                                                    <label class="form-label required">Start</label>
                                                    <input type="date" class="form-control" wire:model="start_date">
                                                    @error('start_date')
                                                    <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-12 col-xl-6">
                                                    <label class="form-label required">End</label>
                                                    <input type="date" class="form-control" wire:model="end_date">
                                                    @error('end_date')
                                                    <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            @else
                                            <!-- DEFAULT -->
                                            <div class="mb-10">
                                                <label class="form-label required">Date</label>
                                                <input type="date" class="form-control" wire:model="date">
                                                @error('date')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            @endrole
                                        </div>
                                        <div class="mb-10">
                                            <label class="form-label required">Accomplishment Category</label>
                                            <select class="form-select" aria-label="Select example" wire:model="ref_accomplishment_category_id">
                                                <option>Open this select menu</option>
                                                @foreach ($accomplishment_categories as $item)
                                                <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                                                @endforeach
                                            </select>
                                            @error('ref_accomplishment_category_id')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        @role('APOO')
                                        <div class="mb-10">
                                            <label class="form-label required">Sub-category</label>
                                            <input type="text" class="form-control" wire:model="sub_category">
                                            @error('sub_category')
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

                                        @role('APOO')
                                        <div class="mb-10">
                                            <label class="form-label">Next Steps</label>
                                            <!-- <input type="text" class="form-control" wire:model="next_steps"> -->
                                            <textarea class="form-control" wire:model="next_steps"></textarea>
                                        </div>
                                        @endrole

                                        <div class="d-flex flex-column flex-sm-row justify-content-center gap-3 gap-sm-5 mt-10">
                                            <button type="button" class="btn btn-danger" wire:click="cancel">
                                                <span>Cancel</span>
                                            </button>
                                            @can('accomplishments.create')
                                            <button type="submit" class="btn btn-primary"
                                                wire:loading.attr="disabled" wire:target="saveAccomplishment">
                                                <span wire:loading.remove wire:target="saveAccomplishment">Save</span>
                                                <div class="spinner-border spinner-border-sm" role="status" wire:loading wire:target="saveAccomplishment">
                                                    <span class="sr-only">Loading...</span>
                                                </div>
                                            </button>
                                            @endcan
                                        </div>
                                    </div>
                                </form>
                            </div>

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
                <div class="col-lg-8">
                    <!--begin::Mixed Widget 5-->
                    <div class="card">
                        <!--begin::Header-->
                        <div class="card-header border-0 py-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bolder fs-3 mb-1">Accomplishment</span>
                                <span class="text-muted fw-bold fs-7">Over {{ $accomplishments->count() }} accomplishments</span>
                            </h3>
                            <div class="card-toolbar">
                                <div class="d-flex align-items-center gap-2">
                                    <!-- begin::Generate PDF -->
                                    <button type="button" class="btn btn-icon btn-color-warning btn-light-warning" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end"
                                        @role('APOO')
                                        wire:click="$dispatch('show-accomplishment-signatories-modal')"
                                        @else
                                        wire:click="generatePDF"
                                        @endrole>
                                        <div wire:loading.remove wire:target="generatePDF">
                                            <!--begin::Svg Icon | path: icons/duotune/general/gen024.svg-->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-filetype-pdf" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5zM1.6 11.85H0v3.999h.791v-1.342h.803q.43 0 .732-.173.305-.175.463-.474a1.4 1.4 0 0 0 .161-.677q0-.375-.158-.677a1.2 1.2 0 0 0-.46-.477q-.3-.18-.732-.179m.545 1.333a.8.8 0 0 1-.085.38.57.57 0 0 1-.238.241.8.8 0 0 1-.375.082H.788V12.48h.66q.327 0 .512.181.185.183.185.522m1.217-1.333v3.999h1.46q.602 0 .998-.237a1.45 1.45 0 0 0 .595-.689q.196-.45.196-1.084 0-.63-.196-1.075a1.43 1.43 0 0 0-.589-.68q-.396-.234-1.005-.234zm.791.645h.563q.371 0 .609.152a.9.9 0 0 1 .354.454q.118.302.118.753a2.3 2.3 0 0 1-.068.592 1.1 1.1 0 0 1-.196.422.8.8 0 0 1-.334.252 1.3 1.3 0 0 1-.483.082h-.563zm3.743 1.763v1.591h-.79V11.85h2.548v.653H7.896v1.117h1.606v.638z" />
                                            </svg>
                                            <!--end::Svg Icon-->
                                        </div>
                                        <div wire:loading wire:target="generatePDF">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </button>
                                    <!-- end::Generate PDF -->

                                    <!--begin::Menu Filter-->
                                    <livewire:components.menu-filter-component />
                                    <!--end::Menu Filter-->

                                    <!--begin::Menu 2-->
                                    @can('accomplishments.create')
                                    <!-- <div class="vr"></div> Vertical Divider -->
                                    <!-- <a href="#" class="btn btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#accomplishmentModal"><i class="bi bi-plus-circle"></i></a> -->
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
                                            <th>Accomplishment Category</th>
                                            <th class="min-w-200px">Date</th>
                                            <th>Details</th>
                                            @role('APOO')
                                            <th>Next Steps</th>
                                            @endrole
                                            @can('accomplishments.update')
                                            <th class="text-center">Actions</th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($accomplishments as $item)
                                        <tr>
                                            <td>
                                                {{ $item->accomplishment_category->accomplishment_category_name }}
                                                @role('APOO')
                                                <span class="text-muted d-block">{{ $item->apo->sub_category ?? '' }}</span>
                                                @endrole
                                            </td>
                                            <td>
                                                @role('APOO')
                                                {{ $item->apo ? ($item->apo->start_date_formatted . ' - ' . $item->apo->end_date_formatted) : '' }}
                                                @else
                                                {{ $item->formatted_date }}
                                                @endrole
                                            </td>
                                            <td>
                                                {{ $item->details }}
                                            </td>
                                            @role('APOO')
                                            <td>
                                                {{ $item->apo->next_steps ?? '' }}
                                            </td>
                                            @endrole
                                            @can('accomplishments.update')
                                            <td class="text-center" wire:loading.class="pe-none">
                                                <button type="button" class="btn btn-icon btn-sm btn-secondary" title="Edit" wire:click="editAccomplishment({{ $item->id }})">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
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
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Content-->

    @include('livewire.shared.modals.pdf-modal')

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
                    <form wire:submit="saveAccomplishment">
                        <div class="p-2">
                            @role('APOO')
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
                                <label class="form-label required">Accomplishment Category</label>
                                <select class="form-select" aria-label="Select example" wire:model="ref_accomplishment_category_id">
                                    <option>Open this select menu</option>
                                    @foreach ($accomplishment_categories as $item)
                                    <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('ref_accomplishment_category_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            @role('APOO')
                            <div class="mb-10">
                                <label class="form-label required">Sub-category</label>
                                <input type="text" class="form-control" wire:model="sub_category">
                                @error('sub_category')
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

                            @role('APOO')
                            <div class="mb-10">
                                <label class="form-label">Next Steps</label>
                                <!-- <input type="text" class="form-control" wire:model="next_steps"> -->
                                <textarea class="form-control" wire:model="next_steps"></textarea>
                            </div>
                            @endrole
                        </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="clear">Close</button>
                    <div wire:loading.remove>
                        <button type="submit" class="btn btn-primary">{{ $editMode ? 'Update' : 'Create' }}</button>
                    </div>
                    <div wire:loading wire:target="saveAccomplishment">
                        <button class="btn btn-primary" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                            <span role="status">Loading...</span>
                        </button>
                    </div>
                </div>
                </form>
            </div>
        </div>
        <!--end::Modal - Accomplishment-->
    </div>

    @role('APOO')
    <!--begin::Modal - Accomplishment Signatory-->
    <div class="modal fade" tabindex="-1" id="accomplishmentSignatoriesModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Accomplishment</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clear">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    <form wire:submit="generatePDF">
                        <div class="p-2">
                            <div class="mb-10">
                                <label class="form-label required">Prepared by</label>
                                <input type="text" class="form-control" wire:model="prepared_by" disabled>
                                @error('prepared_by')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Conforme</label>
                                <select class="form-select" wire:model="conforme">
                                    <option value="">-Select-</option>
                                    @foreach($conformees_signatories as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('conforme')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-10">
                                <label class="form-label required">Approved</label>
                                <select class="form-select" wire:model="approved">
                                    <option value="">-Select-</option>
                                    @foreach($approved_by_signatories as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('approved')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="clear">Close</button>
                    <div wire:loading.remove>
                        <button type="submit" class="btn btn-primary">Generate PDF</button>
                    </div>
                    <div wire:loading wire:target="generatePDF">
                        <button class="btn btn-primary" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                            <span role="status">Loading...</span>
                        </button>
                    </div>
                </div>
                </form>
            </div>
        </div>
        <!--end::Modal - Accomplishment Signatory-->
    </div>
    @endrole

    @script
    <script>
        $wire.on('hide-accomplishment-modal', () => {
            $('#accomplishmentModal').modal('hide');
        });

        $wire.on('show-accomplishment-modal', () => {
            $('#accomplishmentModal').modal('show');
        });

        $wire.on('show-accomplishment-signatories-modal', () => {
            $('#accomplishmentSignatoriesModal').modal('show');
        });

        $wire.on('hide-accomplishment-signatories-modal', () => {
            $('#accomplishmentSignatoriesModal').modal('hide');
        });

        /* -------------------------------------------------------------------------- */
    </script>
    @endscript