<div>
    <button type="button" class="btn btn-icon btn-color-info btn-light-info" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
        <!--begin::Svg Icon | path: icons/duotune/general/gen024.svg-->
        <span class="svg-icon svg-icon-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel" viewBox="0 0 16 16">
                <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.308l4.372 4.858A.5.5 0 0 1 7 8.5v5.306l2-.666V8.5a.5.5 0 0 1 .128-.334L13.5 3.308V2z" />
            </svg>
        </span>
        <!--end::Svg Icon-->
    </button>
    <div class="menu menu-sub menu-sub-dropdown w-250px w-md-300px" data-kt-menu="true" id="kt_menu_6148527cbe7c2" wire:ignore.self>
        <!--begin::Header-->
        <div class="px-7 py-5">
            <div class="fs-5 text-dark fw-bolder">Filter Options</div>
        </div>
        <!--end::Header-->
        <!--begin::Menu separator-->
        <div class="border border-secondary-subtle"></div>
        <!--end::Menu separator-->
        <!--begin::Form-->
        <div class="px-7 py-5">
            <!--begin::Input group-->
            <div class="mb-10">
                <!--begin::Label-->
                <label class="form-label fw-bold">Date:</label>
                <!--end::Label-->
                <!--begin::Input-->
                <div wire:ignore>
                    <input type="text" class="form-control" id="filter_date" />
                </div>
                <!--end::Input-->
            </div>
            <!--end::Input group-->
            <!--begin::Input group-->
            <div class="mb-10">
                <!--begin::Label-->
                <label class="form-label fw-bold">Status:</label>
                <!--end::Label-->
                <!--begin::Input-->
                <select class="form-select text-uppercase" wire:model="status">
                    <option>-Select-</option>
                    @foreach ($status_dropdown as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
                <!--end::Input-->
            </div>
            <!--end::Input group-->
            <!--begin::Actions-->
            <div class="d-flex justify-content-end">
                <button type="reset" class="btn btn-sm btn-light btn-active-light-primary me-2" data-kt-menu-dismiss="true" wire:click="clear">Reset</button>
                <button type="button" class="btn btn-sm btn-primary" data-kt-menu-dismiss="true" wire:click="filter">Apply</button>
            </div>
            <!--end::Actions-->
        </div>
        <!--end::Form-->
    </div>
</div>

@script
<script>
    $('#filter_date').daterangepicker({
        "showDropdowns": true,
        "autoApply": true,
        "linkedCalendars": false,
        "showCustomRangeLabel": false,
        "alwaysShowCalendars": true,
        "opens": "center"
    });

    // When dates are selected
    $('#filter_date').on('apply.daterangepicker', function(ev, picker) {
        // const rangeText = picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD');
        // $(this).val(rangeText);

        // Directly set Livewire properties
        @this.set('start_date', picker.startDate.format('YYYY-MM-DD'));
        @this.set('end_date', picker.endDate.format('YYYY-MM-DD'));
        // @this.dateRangeDisplay = rangeText;

        // @this.updateFilterDate(
        //     picker.startDate.format('YYYY-MM-DD'),
        //     picker.endDate.format('YYYY-MM-DD')
        // );
    });

    $wire.on('clear-filter-date', () => {
        $('#filter_date').val('');
    });
</script>
@endscript