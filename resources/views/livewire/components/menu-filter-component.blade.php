@php
    // Pages with several filter fields get a wide, multi-column (landscape) dropdown; date-only pages stay narrow.
    $wide = in_array($page, ['outgoing', 'incoming']);
    $col = $wide ? 'col-12 col-md-4' : 'col-12';
@endphp
<div>
    <button type="button" class="btn btn-light-info d-flex align-items-center px-4" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" title="Filter">
        <i class="bi bi-funnel fs-4 me-2"></i>
        <span class="fw-bold">Filter</span>
        @if ($active_count > 0)
        <span class="badge badge-circle badge-info ms-2">{{ $active_count }}</span>
        @endif
    </button>
    <div class="menu menu-sub menu-sub-dropdown {{ $wide ? 'w-300px w-md-750px' : 'w-300px' }}" data-kt-menu="true" id="kt_menu_6148527cbe7c2" wire:ignore.self>
        <!--begin::Header-->
        <div class="px-7 py-4 d-flex align-items-center justify-content-between">
            <div>
                <div class="fs-5 text-dark fw-bolder">Filter Options</div>
                <div class="fs-7 text-muted">Narrow down the list</div>
            </div>
            @if ($active_count > 0)
            <span class="badge badge-light-info fs-8">{{ $active_count }} active</span>
            @endif
        </div>
        <!--end::Header-->
        <div class="separator border-gray-200"></div>
        <!--begin::Form-->
        <div class="px-7 py-5">
            <div class="row g-5">
                <!--begin::Input group-->
                <div class="{{ $col }}">
                    <label class="form-label fs-7 fw-bold text-gray-700" for="filter_date">Date</label>
                    <div wire:ignore class="position-relative">
                        <i class="bi bi-calendar3 position-absolute top-50 translate-middle-y ms-4 text-gray-500"></i>
                        <input type="text" class="form-control form-control-solid ps-12 cursor-pointer" id="filter_date" placeholder="Any date" readonly autocomplete="off" />
                    </div>
                </div>
                <!--end::Input group-->

                @if (in_array($page, ['outgoing', 'incoming']))
                    @if (in_array($context, ['requests']))
                    <div class="{{ $col }}">
                        <label class="form-label fs-7 fw-bold text-gray-700">Category</label>
                        <select class="form-select form-select-solid" wire:model="request_category">
                            <option value="">All categories</option>
                            @foreach ($category_request_dropdown as $item)
                            <option value="{{ $item->id }}">{{ $item->incoming_request_category_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="{{ $col }}">
                        <label class="form-label fs-7 fw-bold text-gray-700">Document Type</label>
                        <select class="form-select form-select-solid" wire:model="doctype">
                            <option value="">All document types</option>
                            @foreach ($doctype_dropdown as $item)
                            <option value="{{ $item->id }}">{{ $item->document_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    @if (in_array($context, ['documents']))
                    <div class="{{ $col }}">
                        <label class="form-label fs-7 fw-bold text-gray-700">Category</label>
                        <select class="form-select form-select-solid" wire:model="document_category">
                            <option value="">All categories</option>
                            @foreach ($category_document_dropdown as $item)
                            <option value="{{ $item->id }}">{{ $item->incoming_document_category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!--begin::Input group-->
                    <div class="{{ $col }}">
                        <label class="form-label fs-7 fw-bold text-gray-700">Status</label>
                        <select class="form-select form-select-solid text-capitalize" wire:model="status">
                            <option value="">All statuses</option>
                            @foreach ($status_dropdown as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!--end::Input group-->

                    @if (in_array($context, ['requests']))
                    <div class="col-12 col-md-8">
                        <label class="form-label fs-7 fw-bold text-gray-700">Attachment</label>
                        <label class="d-flex align-items-center justify-content-between bg-light rounded px-4 py-3 cursor-pointer" for="filter_no_attachment">
                            <span>
                                <span class="d-block fw-semibold text-gray-800">Without attachments only</span>
                                <span class="d-block fs-8 text-muted">Requests with no uploaded file</span>
                            </span>
                            <span class="form-check form-check-solid form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="filter_no_attachment" wire:model="no_attachment">
                            </span>
                        </label>
                    </div>
                    @endif
                @endif

                @if ($page == 'outgoing')
                <!--begin::Input group-->
                <div class="{{ $col }}">
                    <label class="form-label fs-7 fw-bold text-gray-700">Category</label>
                    <select class="form-select form-select-solid" wire:model="outgoing_category">
                        <option value="">All categories</option>
                        <option value="voucher">Voucher</option>
                        <option value="ris">RIS</option>
                        <option value="procurement">Procurement</option>
                        <option value="payroll">Payroll</option>
                        <option value="others">Others</option>
                    </select>
                </div>
                <!--end::Input group-->
                @endif
            </div>
        </div>
        <!--end::Form-->

        <!--begin::Actions-->
        <div class="separator border-gray-200"></div>
        <div class="px-7 py-4 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-sm btn-light" data-kt-menu-dismiss="true" wire:click="clear">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
            </button>
            <button type="button" class="btn btn-sm btn-primary" data-kt-menu-dismiss="true" wire:click="filter">
                <i class="bi bi-check2 me-1"></i> Apply
            </button>
        </div>
        <!--end::Actions-->
    </div>

    <style>
        /* Quick-range list of the filter's date picker (rendered outside the menu, at body level) */
        .filter-daterangepicker .ranges li { color: #000; }
        .filter-daterangepicker .ranges li.active { color: #fff; }
    </style>
</div>

@script
<script>
    $('#filter_date').daterangepicker({
        "showDropdowns": true,
        "autoApply": true,
        "autoUpdateInput": false, // keep the "Any date" placeholder until a range is picked
        "linkedCalendars": false,
        "alwaysShowCalendars": true,
        "opens": "center",
        "ranges": {
            'Today': [moment(), moment()],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'This Year': [moment().startOf('year'), moment().endOf('year')]
        }
    });
    $('#filter_date').data('daterangepicker').container.addClass('filter-daterangepicker');

    // When dates are selected
    $('#filter_date').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MMM D, YYYY') + ' – ' + picker.endDate.format('MMM D, YYYY'));

        // Directly set Livewire properties
        @this.set('start_date', picker.startDate.format('YYYY-MM-DD'));
        @this.set('end_date', picker.endDate.format('YYYY-MM-DD'));
    });

    $wire.on('clear-filter-date', () => {
        $('#filter_date').val('');
    });
</script>
@endscript
