<div>
    <!--begin::Mixed Widget 5-->
    <div class="card card-xxl-stretch" wire:loading.class="opacity-50 pe-none">
        <!--begin::Header-->
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1">View Monthly Accomplishment and Monitoring Report</span>
                <span class="text-muted fw-bold fs-7"></span>
            </h3>
            <div class="card-toolbar" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" title="" data-bs-original-title="Go Back">
                <button class="btn btn-sm btn-info" wire:click="hideMonthlyAccomplishmentAndMonitoringReport">
                    <div wire:loading.remove wire:target="hideMonthlyAccomplishmentAndMonitoringReport">
                        Go Back
                    </div>
                    <div wire:loading wire:target="hideMonthlyAccomplishmentAndMonitoringReport">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </button>
            </div>
        </div>
        <!--end::Header-->

        <!--begin::Body-->
        <div class="card-body d-flex flex-column" style="position: relative; padding-top: unset;">
            <div class="collapse show">
                <div class="overflow-auto" style="max-height: 700px;">
                    <div class="mt-5">
                        <table class="table align-middle table-hover table-row-bordered border gy-7 gs-7">
                            <thead class="sticky-table-header">
                                <tr class="fw-bold fs-6 text-gray-800 bg-secondary text-center">
                                    <th class="align-middle" rowspan="2">ACTIVITIES/PROJECTS</th>
                                    <th class="align-middle">TARGET</th>
                                    <th class="align-middle">ACCOMPLISHMENT MONTH</th>
                                    <th class="align-middle">ACCOMPLISHMENT TO DATE</th>
                                    <th class="align-middle" rowspan="2">PERCENTAGE</th>
                                    <th class="align-middle" rowspan="2">REMARKS</th>
                                </tr>
                                <tr class="fw-bold fs-6 text-gray-800 bg-light text-center">
                                    <th class="align-middle">{{ $accomplishment->formatted_half_year_period }}</th>
                                    <th class="align-middle">
                                        <select class="form-select" aria-label="Month" wire:model.live="selectedAccomplishmentMonth">
                                            <option value="">-Select-</option>
                                            @php
                                            list($year, $half) = explode('-', $accomplishment->target ?? '2025-H1'); // Added null coalescing for safety 2025-H1 is just to avoid error messages to pop up.

                                            $startMonth = 1; // Default to January
                                            $endMonth = 12; // Default to December

                                            if ($half === 'H1') {
                                            $startMonth = 1; // January
                                            $endMonth = 6; // June
                                            } elseif ($half === 'H2') {
                                            $startMonth = 7; // July
                                            $endMonth = 12; // December
                                            }
                                            @endphp

                                            @for ($month = $startMonth; $month <= $endMonth; $month++)
                                                <option value="{{ $month }}">
                                                {{ date('F', mktime(0, 0, 0, $month, 10)) }}
                                                </option>
                                                @endfor
                                        </select>
                                        @error('selectedAccomplishmentMonth')
                                        <div class="text-start">
                                            <span class="text-danger">{{ $message }}</span>
                                        </div>

                                        @enderror
                                    </th>
                                    <th class="align-middle">{{ $accomplishment->accomplishment_to_date }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $categoryIndex => $category)
                                {{-- Category Row --}}
                                <tr>
                                    <td class="fw-bold bg-light">
                                        {{ \App\Helpers\RomanNumeralConverter::convertToRoman($categoryIndex + 1) }}. {{ $category['accomplishment_category_name'] }}
                                    </td>
                                    <td class="{{ $category['is_inputtable'] === 'Y' ? '' : 'bg-light' }}">
                                        <input type="text"
                                            class="form-control @error('entityTargetsInput.category.' . $category['id'] . '.target_value') is-invalid @enderror"
                                            style="display: {{ $category['is_inputtable'] === 'Y' ? '' : 'none' }};"
                                            wire:model.blur="entityTargetsInput.category.{{ $category['id'] }}.target_value"
                                            placeholder="Enter target"
                                            {{ auth()->user()->can('monthly-reporting.input-target-period') ? '' : 'disabled' }}>
                                        @error('entityTargetsInput.category.' . $category['id'] . '.target_value')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </td> {{-- Target (empty for sub-category row) --}}
                                    <td class="{{ $category['is_inputtable'] === 'Y' ? '' : 'bg-light' }}">
                                        @if ($selectedAccomplishmentMonth)
                                        <input type="text"
                                            class="form-control"
                                            style="display: {{ $category['is_inputtable'] === 'Y' ? '' : 'none' }};"
                                            wire:model.blur="entityMonthlyInputs.category.{{ $category['id'] }}.{{ $selectedAccomplishmentMonth }}.accomplished_value"
                                            placeholder="Enter month accomplishment"
                                            {{ auth()->user()->can('monthly-reporting.input-accomplishment-by-month') ? '' : 'disabled' }}>
                                        @error('entityMonthlyInputs.category' . $category['id'] . $selectedAccomplishmentMonth . '.accomplished_value')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        @endif
                                    </td> {{-- Accomplishment Month (empty) --}}
                                    <td class="{{ $category['is_inputtable'] === 'Y' ? '' : 'bg-light' }} text-center">
                                        <span style="display: {{ $category['is_inputtable'] === 'Y' ? '' : 'none' }};">
                                            {{ $this->accomplishmentToDateTotals['category'][$category['id']] ?? 0 }}
                                        </span>
                                    </td> {{-- Accomplishment To Date (empty) --}}
                                    <td class="{{ $category['is_inputtable'] === 'Y' ? '' : 'bg-light' }}">
                                        <div class="d-flex flex-column w-100 me-2">
                                            <div class="d-flex flex-stack mb-2">
                                                <span class="text-muted me-2 fs-7 fw-bold" style="display: {{ $category['is_inputtable'] === 'Y' ? '' : 'none' }};">{{ $this->accomplishmentToDatePercentages['category'][$category['id']] ?? 0 }}%</span>
                                            </div>
                                            <div class="progress h-6px w-100">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $this->accomplishmentToDatePercentages['category'][$category['id']] ?? 0 }}%" aria-valuenow="{{ $this->accomplishmentToDatePercentages['category'][$category['id']] ?? 0  }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="{{ $category['is_inputtable'] === 'Y' ? '' : 'bg-light' }}">
                                        @if ($selectedAccomplishmentMonth)
                                        <input type="text"
                                            class="form-control"
                                            style="display: {{ $category['is_inputtable'] === 'Y' ? '' : 'none' }};"
                                            wire:model.blur="entityRemarksInputs.category.{{ $category['id'] }}.{{ $selectedAccomplishmentMonth }}.remarks_value"
                                            placeholder="Enter remarks"
                                            {{ auth()->user()->can('monthly-reporting.input-remarks') ? '' : 'disabled' }}>
                                        @error('entityRemarksInputs.category' . $category['id'] . $selectedAccomplishmentMonth . '.remarks_value')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        @endif
                                    </td> {{-- Remarks (empty) --}}
                                </tr>

                                @forelse($category['sub_categories'] as $subCategoryIndex => $subCategory)
                                {{-- Direct Sub-category Row --}}
                                <tr>
                                    <td class="bg-light" style="padding-left: 20px;">
                                        {{ chr(65 + $subCategoryIndex) }}. {{ $subCategory['accomplishment_sub_category_name'] }}
                                    </td> {{-- Adjusted to use sub_category_name --}}
                                    <td class="{{ $subCategory['is_inputtable'] === 'Y' ? '' : 'bg-light' }}">
                                        <input type="text"
                                            class="form-control"
                                            style="display: {{ $subCategory['is_inputtable'] === 'Y' ? '' : 'none' }};"
                                            wire:model.blur="entityTargetsInput.subCategory.{{ $subCategory['id'] }}.target_value"
                                            placeholder="Enter target"
                                            {{ auth()->user()->can('monthly-reporting.input-target-period') ? '' : 'disabled' }}>
                                    </td> {{-- Target (empty for sub-category row) --}}
                                    <td class="{{ $subCategory['is_inputtable'] === 'Y' ? '' : 'bg-light' }}">
                                        @if ($selectedAccomplishmentMonth)
                                        <input type="text"
                                            class="form-control"
                                            style="display: {{ $subCategory['is_inputtable'] === 'Y' ? '' : 'none' }};"
                                            wire:model.blur="entityMonthlyInputs.subCategory.{{ $subCategory['id'] }}.{{ $selectedAccomplishmentMonth }}.accomplished_value"
                                            placeholder="Enter month accomplishment"
                                            {{ auth()->user()->can('monthly-reporting.input-accomplishment-by-month') ? '' : 'disabled' }}>
                                        @error('entityMonthlyInputs.subCategory' . $subCategory['id'] . $selectedAccomplishmentMonth . '.accomplished_value')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        @endif
                                    </td> {{-- Accomplishment Month (empty) --}}
                                    <td class="{{ $subCategory['is_inputtable'] === 'Y' ? '' : 'bg-light' }} text-center">
                                        <span style="display: {{ $subCategory['is_inputtable'] === 'Y' ? '' : 'none' }};">
                                            {{ $this->accomplishmentToDateTotals['subCategory'][$subCategory['id']] ?? 0 }}
                                        </span>
                                    </td> {{-- Accomplishment To Date (empty) --}}
                                    <td class="{{ $subCategory['is_inputtable'] === 'Y' ? '' : 'bg-light' }}">
                                        <div class="d-flex flex-column w-100 me-2">
                                            <div class="d-flex flex-stack mb-2">
                                                <span class="text-muted me-2 fs-7 fw-bold" style="display: {{ $subCategory['is_inputtable'] === 'Y' ? '' : 'none' }};">{{ $this->accomplishmentToDatePercentages['subCategory'][$subCategory['id']] ?? 0 }}%</span>
                                            </div>
                                            <div class="progress h-6px w-100">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $this->accomplishmentToDatePercentages['subCategory'][$subCategory['id']] ?? 0 }}%" aria-valuenow="{{ $this->accomplishmentToDatePercentages['subCategory'][$subCategory['id']] ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td> {{-- Percentage (empty) --}}
                                    <td class="{{ $subCategory['is_inputtable'] === 'Y' ? '' : 'bg-light' }}">
                                        @if ($selectedAccomplishmentMonth)
                                        <input type="text"
                                            class="form-control"
                                            style="display: {{ $subCategory['is_inputtable'] === 'Y' ? '' : 'none' }};"
                                            wire:model.blur="entityRemarksInputs.subCategory.{{ $subCategory['id'] }}.{{ $selectedAccomplishmentMonth }}.remarks_value"
                                            placeholder="Enter remarks"
                                            {{ auth()->user()->can('monthly-reporting.input-remarks') ? '' : 'disabled' }}>
                                        @error('entityRemarksInputs.subCategory' . $subCategory['id'] . $selectedAccomplishmentMonth . '.remarks_value')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        @endif
                                    </td> {{-- Remarks (empty) --}}
                                </tr>

                                @if ($subCategory['parent_id'] === null && !empty($subCategory['species']))
                                {{-- This condition implies it's a direct sub-category that has species --}}
                                @foreach ($subCategory['species'] as $speciesIndex => $species)
                                <tr>
                                    <td class="bg-light" style="padding-left: 40px;">
                                        {{ ($speciesIndex + 1) }}. {{ $species['species_name'] }}
                                    </td>
                                    <td>
                                        <input type="text"
                                            class="form-control"
                                            wire:model.blur="entityTargetsInput.species.{{ $species['id'] }}.target_value"
                                            placeholder="Enter target"
                                            {{ auth()->user()->can('monthly-reporting.input-target-period') ? '' : 'disabled' }}>
                                    </td> {{-- Target - This can be dynamic data later --}}
                                    <td>
                                        @if ($selectedAccomplishmentMonth)
                                        <input type="text"
                                            class="form-control"
                                            wire:model.blur="entityMonthlyInputs.species.{{ $species['id'] }}.{{ $selectedAccomplishmentMonth }}.accomplished_value"
                                            placeholder="Enter month accomplishment"
                                            {{ auth()->user()->can('monthly-reporting.input-accomplishment-by-month') ? '' : 'disabled' }}>
                                        @error('entityMonthlyInputs.species' . $species['id'] . $selectedAccomplishmentMonth . '.accomplished_value')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        @endif
                                    </td> {{-- Accomplishment Month --}}
                                    <td class="text-center">
                                        <span>
                                            {{ $this->accomplishmentToDateTotals['species'][$species['id']] ?? 0 }}
                                        </span>
                                    </td> {{-- Accomplishment To Date --}}
                                    <td>
                                        <div class="d-flex flex-column w-100 me-2">
                                            <div class="d-flex flex-stack mb-2">
                                                <span class="text-muted me-2 fs-7 fw-bold">{{ $this->accomplishmentToDatePercentages['species'][$species['id']] ?? 0 }}%</span>
                                            </div>
                                            <div class="progress h-6px w-100">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $this->accomplishmentToDatePercentages['species'][$species['id']] ?? 0 }}%" aria-valuenow="{{ $this->accomplishmentToDatePercentages['species'][$species['id']] ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td> {{-- Percentage (calculated or empty) --}}
                                    <td>
                                        @if ($selectedAccomplishmentMonth)
                                        <input type="text"
                                            class="form-control"
                                            wire:model.blur="entityRemarksInputs.species.{{ $species['id'] }}.{{ $selectedAccomplishmentMonth }}.remarks_value"
                                            placeholder="Enter remarks"
                                            {{ auth()->user()->can('monthly-reporting.input-remarks') ? '' : 'disabled' }}>
                                        @error('entityRemarksInputs.species' . $species['id'] . $selectedAccomplishmentMonth . '.remarks_value')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        @endif
                                    </td> {{-- Remarks --}}
                                </tr>
                                @endforeach
                                @endif

                                @if (!empty($subCategory['children']))
                                {{-- Parent Sub Category Row (if it has children) --}}
                                @foreach ($subCategory['children'] as $childSubCategoryIndex => $childSubCategory)
                                <tr>
                                    <td class="bg-light" style="padding-left: 40px;">
                                        {{ \App\Helpers\RomanNumeralConverter::convertToRoman($childSubCategoryIndex + 1, true) }}. {{ $childSubCategory['accomplishment_sub_category_name'] }}
                                    </td>
                                    <td class="bg-light"></td> {{-- Target (empty for parent sub-category row) --}}
                                    <td class="bg-light"></td> {{-- Accomplishment Month (empty) --}}
                                    <td class="bg-light"></td> {{-- Accomplishment To Date (empty) --}}
                                    <td class="bg-light"></td> {{-- Percentage (empty) --}}
                                    <td class="bg-light"></td> {{-- Remarks (empty) --}}
                                </tr>

                                @if (!empty($childSubCategory['species']))
                                {{-- Species belonging to the child sub-category --}}
                                @foreach ($childSubCategory['species'] as $nestedSpeciesIndex => $nestedSpecies)
                                <tr>
                                    <td class="bg-light" style="padding-left: 60px;">
                                        &ndash; {{ $nestedSpecies['species_name'] }}
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            class="form-control"
                                            wire:model.blur="entityTargetsInput.species.{{ $nestedSpecies['id'] }}.target_value"
                                            placeholder="Enter target"
                                            {{ auth()->user()->can('monthly-reporting.input-target-period') ? '' : 'disabled' }}>
                                    </td> {{-- Target - This can be dynamic data later --}}
                                    <td>
                                        @if ($selectedAccomplishmentMonth)
                                        <input type="text"
                                            class="form-control"
                                            wire:model.blur="entityMonthlyInputs.species.{{ $nestedSpecies['id'] }}.{{ $selectedAccomplishmentMonth }}.accomplished_value"
                                            placeholder="Enter month accomplishment"
                                            {{ auth()->user()->can('monthly-reporting.input-accomplishment-by-month') ? '' : 'disabled' }}>
                                        @error('entityMonthlyInputs.species' . $nestedSpecies['id'] . $selectedAccomplishmentMonth . '.accomplished_value')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        @endif
                                    </td> {{-- Accomplishment Month --}}
                                    <td class="text-center">
                                        <span>
                                            {{ $this->accomplishmentToDateTotals['species'][$nestedSpecies['id']] ?? 0 }}
                                        </span>
                                    </td> {{-- Accomplishment To Date --}}
                                    <td>
                                        <div class="d-flex flex-column w-100 me-2">
                                            <div class="d-flex flex-stack mb-2">
                                                <span class="text-muted me-2 fs-7 fw-bold">{{ $this->accomplishmentToDatePercentages['species'][$nestedSpecies['id']] ?? 0 }}%</span>
                                            </div>
                                            <div class="progress h-6px w-100">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $this->accomplishmentToDatePercentages['species'][$nestedSpecies['id']] ?? 0 }}%" aria-valuenow="{{ $this->accomplishmentToDatePercentages['species'][$nestedSpecies['id']] ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </td> {{-- Percentage (calculated or empty) --}}
                                    <td>
                                        @if ($selectedAccomplishmentMonth)
                                        <input type="text"
                                            class="form-control"
                                            wire:model.blur="entityRemarksInputs..species.{{ $nestedSpecies['id'] }}.{{ $selectedAccomplishmentMonth }}.remarks_value"
                                            placeholder="Enter remarks"
                                            {{ auth()->user()->can('monthly-reporting.input-remarks') ? '' : 'disabled' }}>
                                        @error('entityRemarksInputs..species' . $nestedSpecies['id'] . $selectedAccomplishmentMonth . '.remarks_value')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        @endif
                                    </td> {{-- Remarks --}}
                                </tr>
                                @endforeach
                                @endif
                                @endforeach
                                @endif

                                @empty
                                <tr style="display: none;">
                                    <td colspan="6" class="text-center">No Sub-categories for this Category.</td>
                                </tr>
                                @endforelse

                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No Categories found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!--begin::Pagination-->
                <div class="pt-3">
                    <!-- page-links -->
                </div>
                <!--end::Pagination-->
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

@script
<script>
    // window.addEventListener('save-target', event => {
    //     Livewire.dispatch('triggerSaveTarget', {
    //         key: event.detail.key
    //     });
    // });

    $wire.on('save-target', (key) => {
        $wire.dispatch('triggerSaveTarget', {
            key: key
        });
    });

    $wire.on('save-monthly-accomplishment', (key) => {
        $wire.dispatch('triggerSaveMonthlyAccomplishment', {
            key: key
        });
    });

    $wire.on('save-remarks', (key) => {
        $wire.dispatch('triggerSaveRemarks', {
            key: key
        });
    });
</script>
@endscript