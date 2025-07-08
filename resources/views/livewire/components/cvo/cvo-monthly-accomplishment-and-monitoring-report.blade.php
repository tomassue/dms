<div>
    <!--begin::Mixed Widget 5-->
    <div class="card card-xxl-stretch">
        <!--begin::Header-->
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1">View Monthly Accomplishment and Monitoring Report (ADMIN VIEW)</span>
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
                <div class="row justify-content-between">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4">
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">Date</span>
                            <input type="text" class="form-control" placeholder="Date range here" aria-label="Username" aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4">
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">Category</span>
                            <select class="form-select" aria-label="Default select example" wire:model.live="ref_accomplishment_category_id">
                                <option value=" ">-Select-</option>
                                @foreach ($category_select as $item)
                                <option value="{{ $item->id }}">{{ $item->accomplishment_category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-between">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4">
                        <div class="input-group mb-3">
                            @php
                            //TODO based on the user (technician)
                            @endphp
                            <span class="input-group-text" id="basic-addon1">Technician</span>
                            <input type="text" class="form-control" placeholder="Select" aria-label="Username" aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4">
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">Sub-category</span>
                            <select class="form-select" aria-label="Default select example" wire:model.live="ref_accomplishment_sub_category_id">
                                <option value=" ">-Select-</option>
                                @foreach ($sub_category_select as $item)
                                <option value="{{ $item->id }}">{{ $item->accomplishment_sub_category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="overflow-auto" style="max-height: 500px;">
                    <div class="mt-5">
                        <table class="table align-middle table-hover table-rounded border gy-7 gs-7">
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
                                        <select class="form-select" aria-label="Month">
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
                                    </th>
                                    <th class="align-middle">{{ $accomplishment->accomplishment_to_date }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $categoryIndex => $category)
                                {{-- Category Row --}}
                                <tr>
                                    <td class="fw-bold bg-light" colspan="6">{{ \App\Helpers\RomanNumeralConverter::convertToRoman($categoryIndex + 1) }}. {{ $category['accomplishment_category_name'] }}</td>
                                </tr>

                                @forelse($category['sub_categories'] as $subCategoryIndex => $subCategory)
                                {{-- Direct Sub-category Row --}}
                                <tr>
                                    <td class="bg-light" style="padding-left: 20px;">{{ chr(65 + $subCategoryIndex) }}. {{ $subCategory['accomplishment_sub_category_name'] }}</td> {{-- Adjusted to use sub_category_name --}}
                                    <td class="bg-light"></td> {{-- Target (empty for sub-category row) --}}
                                    <td class="bg-light"></td> {{-- Accomplishment Month (empty) --}}
                                    <td class="bg-light"></td> {{-- Accomplishment To Date (empty) --}}
                                    <td class="bg-light"></td> {{-- Percentage (empty) --}}
                                    <td class="bg-light"></td> {{-- Remarks (empty) --}}
                                </tr>

                                @if ($subCategory['parent_id'] === null && !empty($subCategory['species']))
                                {{-- This condition implies it's a direct sub-category that has species --}}
                                @foreach ($subCategory['species'] as $speciesIndex => $species)
                                <tr>
                                    <td class="bg-light" style="padding-left: 40px;">
                                        {{ ($speciesIndex + 1) }}. {{ $species['species_name'] }}
                                    </td>
                                    <td><input type="text" class="form-control"></td> {{-- Target - This can be dynamic data later --}}
                                    <td><input type="text" class="form-control"></td> {{-- Accomplishment Month --}}
                                    <td><input type="text" class="form-control"></td> {{-- Accomplishment To Date --}}
                                    <td></td> {{-- Percentage (calculated or empty) --}}
                                    <td><input type="text" class="form-control"></td> {{-- Remarks --}}
                                </tr>
                                @endforeach
                                @endif

                                @if (!empty($subCategory['children']))
                                {{-- Parent Sub Category Row (if it has children) --}}
                                @foreach ($subCategory['children'] as $childSubCategoryIndex => $childSubCategory)
                                <tr>
                                    <td class="bg-light" style="padding-left: 40px;">
                                        {{ \App\Helpers\RomanNumeralConverter::convertToRoman($childSubCategoryIndex + 1, true) }}. {{ $childSubCategory['accomplishment_sub_category_name'] }} {{-- Adjusted to use sub_category_name --}}
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
                                    <td><input type="text" class="form-control"></td> {{-- Target - This can be dynamic data later --}}
                                    <td><input type="text" class="form-control"></td> {{-- Accomplishment Month --}}
                                    <td><input type="text" class="form-control"></td> {{-- Accomplishment To Date --}}
                                    <td></td> {{-- Percentage (calculated or empty) --}}
                                    <td><input type="text" class="form-control"></td> {{-- Remarks --}}
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