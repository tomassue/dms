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
                <div class="d-grid gap-2 d-sm-block">
                    <button class="btn btn-sm btn-warning" wire:click="$dispatch('show-monthly-accomplishment-and-monitoring-report-modal')">
                        <div wire:loading.remove wire:target="generateMonthlyAccomplishmentAndMonitoringReportPdf">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-filetype-pdf" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M14 4.5V14a2 2 0 0 1-2 2h-1v-1h1a1 1 0 0 0 1-1V4.5h-2A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v9H2V2a2 2 0 0 1 2-2h5.5zM1.6 11.85H0v3.999h.791v-1.342h.803q.43 0 .732-.173.305-.175.463-.474a1.4 1.4 0 0 0 .161-.677q0-.375-.158-.677a1.2 1.2 0 0 0-.46-.477q-.3-.18-.732-.179m.545 1.333a.8.8 0 0 1-.085.38.57.57 0 0 1-.238.241.8.8 0 0 1-.375.082H.788V12.48h.66q.327 0 .512.181.185.183.185.522m1.217-1.333v3.999h1.46q.602 0 .998-.237a1.45 1.45 0 0 0 .595-.689q.196-.45.196-1.084 0-.63-.196-1.075a1.43 1.43 0 0 0-.589-.68q-.396-.234-1.005-.234zm.791.645h.563q.371 0 .609.152a.9.9 0 0 1 .354.454q.118.302.118.753a2.3 2.3 0 0 1-.068.592 1.1 1.1 0 0 1-.196.422.8.8 0 0 1-.334.252 1.3 1.3 0 0 1-.483.082h-.563zm3.743 1.763v1.591h-.79V11.85h2.548v.653H7.896v1.117h1.606v.638z" />
                            </svg>
                        </div>
                        <div wire:loading wire:target="generateMonthlyAccomplishmentAndMonitoringReportPdf">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </button>
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
                                    <td class="{{ $category['is_inputtable'] === 'Y' ? '' : 'bg-light' }} text-center">
                                        @if ($category['is_inputtable'] === 'Y' && $selectedAccomplishmentMonth)
                                        @can('monthly-reporting.input-accomplishment-by-month')
                                        <input type="text"
                                            class="form-control"
                                            wire:model.blur="entityMonthlyInputs.category.{{ $category['id'] }}.{{ $selectedAccomplishmentMonth }}.accomplished_value"
                                            placeholder="Enter month accomplishment"
                                            {{ auth()->user()->can('monthly-reporting.input-accomplishment-by-month') ? '' : 'disabled' }}>
                                        @error('entityMonthlyInputs.category' . $category['id'] . $selectedAccomplishmentMonth . '.accomplished_value')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        @endcan
                                        @cannot('monthly-reporting.input-accomplishment-by-month')
                                        <div class="mt-2">
                                            <strong>
                                                {{ $this->getTotalMonthlyAccomplishmentValues('category', $category['id'], $selectedAccomplishmentMonth) }}
                                            </strong>
                                        </div>
                                        @endcannot
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
                                        @if ($category['is_inputtable'] === 'Y' && $selectedAccomplishmentMonth)
                                        @can('monthly-reporting.input-accomplishment-by-month')
                                        <input type="text"
                                            class="form-control"
                                            style="display: {{ $category['is_inputtable'] === 'Y' ? '' : 'none' }};"
                                            wire:model.blur="entityRemarksInputs.category.{{ $category['id'] }}.{{ $selectedAccomplishmentMonth }}.remarks_value"
                                            placeholder="Enter remarks"
                                            {{ auth()->user()->can('monthly-reporting.input-remarks') ? '' : 'disabled' }}>
                                        @error('entityRemarksInputs.category' . $category['id'] . $selectedAccomplishmentMonth . '.remarks_value')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        @endcan
                                        @cannot('monthly-reporting.input-accomplishment-by-month')
                                        <div class="mt-2">
                                            @foreach ($this->getMonthlyAccomplishmentRemarksList('category', $category['id'], $selectedAccomplishmentMonth) as $item)
                                            {{ $item['remarks'] ?? '' }} <br>
                                            @endforeach
                                        </div>
                                        @endcannot
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
                                    <td class="{{ $subCategory['is_inputtable'] === 'Y' ? '' : 'bg-light' }} text-center">
                                        @if ($subCategory['is_inputtable'] === 'Y' && $selectedAccomplishmentMonth)
                                        @can('monthly-reporting.input-accomplishment-by-month')
                                        <input type="text"
                                            class="form-control"
                                            wire:model.blur="entityMonthlyInputs.subCategory.{{ $subCategory['id'] }}.{{ $selectedAccomplishmentMonth }}.accomplished_value"
                                            placeholder="Enter month accomplishment"
                                            {{ auth()->user()->can('monthly-reporting.input-accomplishment-by-month') ? '' : 'disabled' }}>
                                        @error('entityMonthlyInputs.subCategory' . $subCategory['id'] . $selectedAccomplishmentMonth . '.accomplished_value')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        @endcan
                                        @cannot('monthly-reporting.input-accomplishment-by-month')
                                        <div class="mt-2">
                                            <strong>
                                                {{ $this->getTotalMonthlyAccomplishmentValues('subCategory', $subCategory['id'], $selectedAccomplishmentMonth) }}
                                            </strong>
                                        </div>
                                        @endcannot
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
                                        @if ($subCategory['is_inputtable'] === 'Y' && $selectedAccomplishmentMonth)
                                        @can('monthly-reporting.input-accomplishment-by-month')
                                        <input type="text"
                                            class="form-control"
                                            style="display: {{ $subCategory['is_inputtable'] === 'Y' ? '' : 'none' }};"
                                            wire:model.blur="entityRemarksInputs.subCategory.{{ $subCategory['id'] }}.{{ $selectedAccomplishmentMonth }}.remarks_value"
                                            placeholder="Enter remarks"
                                            {{ auth()->user()->can('monthly-reporting.input-remarks') ? '' : 'disabled' }}>
                                        @error('entityRemarksInputs.subCategory' . $subCategory['id'] . $selectedAccomplishmentMonth . '.remarks_value')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        @endcan
                                        @cannot('monthly-reporting.input-accomplishment-by-month')
                                        <div class="mt-2">
                                            @foreach ($this->getMonthlyAccomplishmentRemarksList('subCategory', $subCategory['id'], $selectedAccomplishmentMonth) as $item)
                                            {{ $item['remarks'] ?? '' }} <br>
                                            @endforeach
                                        </div>
                                        @endcannot
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
                                    <td class="text-center">
                                        @if ($selectedAccomplishmentMonth)
                                        @can('monthly-reporting.input-accomplishment-by-month')
                                        <input type="text"
                                            class="form-control"
                                            wire:model.blur="entityMonthlyInputs.species.{{ $species['id'] }}.{{ $selectedAccomplishmentMonth }}.accomplished_value"
                                            placeholder="Enter month accomplishment"
                                            {{ auth()->user()->can('monthly-reporting.input-accomplishment-by-month') ? '' : 'disabled' }}>
                                        @endcan
                                        @cannot('monthly-reporting.input-accomplishment-by-month')
                                        <div class="mt-2">
                                            <strong>
                                                {{ $this->getTotalMonthlyAccomplishmentValues('species', $species['id'], $selectedAccomplishmentMonth) }}
                                            </strong>
                                        </div>
                                        @endcannot
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
                                        @can('monthly-reporting.input-accomplishment-by-month')
                                        <input type="text"
                                            class="form-control"
                                            wire:model.blur="entityRemarksInputs.species.{{ $species['id'] }}.{{ $selectedAccomplishmentMonth }}.remarks_value"
                                            placeholder="Enter remarks"
                                            {{ auth()->user()->can('monthly-reporting.input-remarks') ? '' : 'disabled' }}>
                                        @error('entityRemarksInputs.species' . $species['id'] . $selectedAccomplishmentMonth . '.remarks_value')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        @endcan
                                        @cannot('monthly-reporting.input-accomplishment-by-month')
                                        <div class="mt-2">
                                            @foreach ($this->getMonthlyAccomplishmentRemarksList('species', $species['id'], $selectedAccomplishmentMonth) as $item)
                                            {{ $item['remarks'] ?? '' }} <br>
                                            @endforeach
                                        </div>
                                        @endcannot
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
                                    <td class="text-center">
                                        @if ($selectedAccomplishmentMonth)
                                        @can('monthly-reporting.input-accomplishment-by-month')
                                        <input type="text"
                                            class="form-control"
                                            wire:model.blur="entityMonthlyInputs.species.{{ $nestedSpecies['id'] }}.{{ $selectedAccomplishmentMonth }}.accomplished_value"
                                            placeholder="Enter month accomplishment"
                                            {{ auth()->user()->can('monthly-reporting.input-accomplishment-by-month') ? '' : 'disabled' }}>
                                        @error('entityMonthlyInputs.species' . $nestedSpecies['id'] . $selectedAccomplishmentMonth . '.accomplished_value')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        @endcan
                                        @cannot('monthly-reporting.input-accomplishment-by-month')
                                        <div class="mt-2">
                                            <strong>
                                                {{ $this->getTotalMonthlyAccomplishmentValues('species', $nestedSpecies['id'], $selectedAccomplishmentMonth) }}
                                            </strong>
                                        </div>
                                        @endcannot
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
                                        @can('monthly-reporting.input-accomplishment-by-month')
                                        <input type="text"
                                            class="form-control"
                                            wire:model.blur="entityRemarksInputs..species.{{ $nestedSpecies['id'] }}.{{ $selectedAccomplishmentMonth }}.remarks_value"
                                            placeholder="Enter remarks"
                                            {{ auth()->user()->can('monthly-reporting.input-remarks') ? '' : 'disabled' }}>
                                        @error('entityRemarksInputs..species' . $nestedSpecies['id'] . $selectedAccomplishmentMonth . '.remarks_value')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        @endcan
                                        @cannot('monthly-reporting.input-accomplishment-by-month')
                                        <div class="mt-2">
                                            @foreach ($this->getMonthlyAccomplishmentRemarksList('species', $nestedSpecies['id'], $selectedAccomplishmentMonth) as $item)
                                            {{ $item['remarks'] ?? '' }} <br>
                                            @endforeach
                                        </div>
                                        @endcannot
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

    <!--begin::Modal - Filter-->
    <div class="modal fade" tabindex="-1" id="generateMonthlyAccomplishmentAndMonitoringReportPdfModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">PDF</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clear">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    <form wire:submit="generateMonthlyAccomplishmentAndMonitoringReportPdf">
                        <div class="p-2">
                            <div class="mb-10">
                                <label class="form-label">Type</label>
                                <select class="form-select" aria-label="Month" wire:model="filter_selected_accomplishment_month">
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
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <div wire:loading.remove>
                        <button type="submit" class="btn btn-primary">Generate</button>
                    </div>
                    </form>
                    <div wire:loading wire:target="generateMonthlyAccomplishmentAndMonitoringReportPdf">
                        <button class="btn btn-primary" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                            <span role="status">Loading...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - Filter-->
</div>

@script
<script>
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

    $wire.on('show-monthly-accomplishment-and-monitoring-report-modal', () => {
        $('#generateMonthlyAccomplishmentAndMonitoringReportPdfModal').modal('show');
    });
</script>
@endscript