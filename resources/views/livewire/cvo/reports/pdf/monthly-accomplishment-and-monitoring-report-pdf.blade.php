<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        @page {
            /* Push all page content down 150px from the very top */
            margin: 150px 25px 80px 25px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Fixed header stays in that 150px top margin */
        #header {
            position: fixed;
            top: -150px;
            left: 0;
            right: 0;
            height: 140px;
            /* whatever your header actually needs */
            padding: 0px;
            z-index: 1000;
        }

        /* Fixed footer */
        #footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 50px;
            text-align: center;
            /* border-top: 1px solid #333; */
            padding-top: 10px;
            background-color: white;
            z-index: 1000;
        }

        /* Main content */
        /* No extra margin here — content starts right at the page margin */
        .content {
            /* you can still add inner padding if you like, but no top-margin */
            padding: 0;
            font-size: 10px;
        }

        /* Add page break class */
        .page-break {
            page-break-after: always;
            margin-top: 30px;
            /* Space before page break */
        }

        /* table */
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        /* header image */
        .header-image {
            width: 70px;
            height: auto;
        }

        /* Borders for tables with 'bordered' class only */
        table.bordered {
            border-collapse: collapse;
            width: 100%;
        }

        table.bordered th,
        table.bordered td {
            border: 1px solid black;
            padding: 8px;
        }

        table.bordered th {
            background-color: #f2f2f2;
            vertical-align: top;
        }

        .page-break {
            page-break-after: always;
        }

        .page-number:before {
            content: counter(page);
        }

        .page-count:before {
            content: counter(pages);
        }
    </style>
</head>

<body>
    <!-- Fixed Header -->
    <table id="header" style="padding-top: 20px;">
        <tr>
            <td style="vertical-align: middle; text-align: left;">
                @if(!empty($cdofull))
                <img src="{{ $cdofull }}" class="header-image" alt="cdofull" style="width: 180px;">
                @endif
            </td>
            <td style="vertical-align: bottom; text-align: center; font-size: 17px; height: 100px;">
                Republic of the Philippines
                <br>
                City of Cagayan de Oro
                <br>
                <span style="font-weight: bolder; text-transform: uppercase;">CITY VETERINARY OFFICE</span>
            </td>
            <td style="vertical-align: middle; text-align: right;">
                @if(!empty($rise))
                <img src="{{ $rise }}" class="header-image" alt="RISE Logo" style="width: 120px;">
                @endif
            </td>
            <td style="vertical-align: middle; text-align: left;">
                @if(!empty($cvo_seal))
                <img src="{{ $cvo_seal }}" class="header-image" alt="cvo_seal" style="width: 70px;">
                @endif
            </td>
        </tr>
    </table>

    <table style="padding-bottom: 10px;">
        <tr>
            <td colspan="4" style="text-align: center; font-size: 16px; font-weight: bolder;">
                MONTHLY ACCOMPLISHMENT AND MONITORING REPORT
            </td>
        </tr>
        <tr>
            <td colspan="4" style="text-align: center; font-size: 14px;">
                As of <strong>{{ $selectedMonth }}</strong>
            </td>
        </tr>
    </table>

    <!-- Fixed Footer -->
    <div id="footer">

    </div>

    <!-- Main Content -->
    <div class="content">
        <table class="bordered">
            <thead>
                <tr>
                    <th rowspan="2" style="text-align: center;">ACTIVITIES/PROJECTS</th>
                    <th style="text-align: center;">TARGET</th>
                    <th style="text-align: center;">ACCOMPLISHMENT MONTH</th>
                    <th style="text-align: center;">ACCOMPLISHMENT TO DATE</th>
                    <th rowspan="2" style="text-align: center;">PERCENTAGE</th>
                    <th rowspan="2" style="text-align: center;">REMARKS</th>
                </tr>
                <tr>
                    <th style="text-align: center;">{{ $monthPeriod }}</th>
                    <th style="text-align: center;">{{ $selectedMonth }}</th>
                    <th style="text-align: center;">{{ $accomplishmentToDate }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $categoryIndex => $category)
                <tr>
                    <td colspan="{{ $category['is_inputtable'] === 'Y' ? '0' : '6' }}">
                        {{ \App\Helpers\RomanNumeralConverter::convertToRoman($categoryIndex + 1) }}.
                        {{ $category['accomplishment_category_name'] }}
                    </td>

                    @if ($category['is_inputtable'] === 'Y')
                    <td style="text-align: center;">
                        {{ $entityTargetsInput['category'][$category['id']]['target_value'] ?? '' }}
                    </td>
                    <td style="text-align: center;">
                        <strong>
                            {{ $totals['category'][$category['id']] ?? 0 }}
                        </strong>
                    </td>
                    <td style="text-align: center;">
                        {{ $totalsToDate['category'][$category['id']] ?? 0 }}
                    </td>
                    <td style="text-align: center;">
                        {{ $percentagesToDate['category'][$category['id']] ?? 0 }}%
                    </td>
                    <td style="text-align: start;">
                        <div class="mt-2">
                            @foreach ($remarksList['category'][$category['id']] ?? [] as $item)
                            {{ $item['remarks'] ?? '' }} <br>
                            @endforeach
                        </div>
                    </td>
                    @endif
                </tr>

                @forelse($category['sub_categories'] as $subCategoryIndex => $subCategory)
                <tr>
                    <td colspan="{{ $subCategory['is_inputtable'] === 'Y' ? '0' : '6' }}">
                        {{ chr(65 + $subCategoryIndex) }}. {{ $subCategory['accomplishment_sub_category_name'] }}
                    </td>

                    @if ($subCategory['is_inputtable'] === 'Y')
                    <td style="text-align: center;">{{ $entityTargetsInput['subCategory'][$subCategory['id']]['target_value'] ?? '' }}</td>
                    <td style="text-align: center;">
                        <strong>
                            {{ $totals['subCategory'][$subCategory['id']] ?? 0 }}
                        </strong>
                    </td>
                    <td style="text-align: center;">
                        {{ $totalsToDate['subCategory'][$subCategory['id']] ?? 0 }}
                    </td>
                    <td style="text-align: center;">
                        {{ $percentagesToDate['subCategory'][$subCategory['id']] ?? 0 }}%
                    </td>
                    <td style="text-align: start;">
                        <div class="mt-2">
                            @foreach ($remarksList['subCategory'][$subCategory['id']] ?? [] as $item)
                            {{ $item['remarks'] ?? '' }} <br>
                            @endforeach
                        </div>
                    </td>
                    @endif
                </tr>

                @if ($subCategory['parent_id'] === null && !empty($subCategory['species']))
                @foreach ($subCategory['species'] as $speciesIndex => $species)
                <tr>
                    <td>
                        {{ ($speciesIndex + 1) }}. {{ $species['species_name'] }}
                    </td>
                    <td style="text-align: center;">
                        {{ $entityTargetsInput['species'][$species['id']]['target_value'] ?? '' }}
                    </td>
                    <td style="text-align: center;">
                        <strong>
                            {{ $totals['species'][$species['id']] ?? 0 }}
                        </strong>
                    </td>
                    <td style="text-align: center;">
                        {{ $totalsToDate['species'][$species['id']] ?? 0 }}
                    </td>
                    <td style="text-align: center;">
                        {{ $percentagesToDate['species'][$species['id']] ?? 0 }}%
                    </td>
                    <td style="text-align: start;">
                        <div class="mt-2">
                            @foreach ($remarksList['species'][$species['id']] ?? [] as $item)
                            {{ $item['remarks'] ?? '' }} <br>
                            @endforeach
                        </div>
                    </td>
                </tr>
                @endforeach
                @endif

                @if (!empty($subCategory['children']))
                @foreach ($subCategory['children'] as $childSubCategoryIndex => $childSubCategory)
                <tr>
                    <td>
                        {{ \App\Helpers\RomanNumeralConverter::convertToRoman($childSubCategoryIndex + 1, true) }}. {{ $childSubCategory['accomplishment_sub_category_name'] }}
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>

                @if (!empty($childSubCategory['species']))
                @foreach ($childSubCategory['species'] as $nestedSpeciesIndex => $nestedSpecies)
                <tr>
                    <td>
                        &ndash; {{ $nestedSpecies['species_name'] }}
                    </td>
                    <td style="text-align: center;">
                        {{ $entityTargetsInput['species'][$nestedSpecies['id']]['target_value'] ?? '' }}
                    </td>
                    <td style="text-align: center;">
                        <strong>
                            {{ $totals['species'][$nestedSpecies['id']] ?? 0 }}
                        </strong>
                    </td>
                    <td style="text-align: center;">
                        {{ $totalsToDate['species'][$nestedSpecies['id']] ?? 0 }}
                    </td>
                    <td style="text-align: center;">
                        {{ $percentagesToDate['species'][$nestedSpecies['id']] ?? 0 }}%
                    </td>
                    <td style="text-align: start;">
                        <div class="mt-2">
                            @foreach ($remarksList['species'][$nestedSpecies['id']] ?? [] as $item)
                            {{ $item['remarks'] ?? '' }} <br>
                            @endforeach
                        </div>
                    </td>
                </tr>
                @endforeach
                @endif
                @endforeach
                @endif

                @empty
                <tr style=" display: none;">
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

    <div class="content" style="padding-top: 15px;">
        <table>
            <tr>
                <td>Submitted By:</td>
                <td></td>
                <td>Recommending Approval:</td>
                <td></td>
                <td>Approved By:</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="font-weight: bolder; text-align: center; border-bottom: 1px solid black;">{{ $submittedBy }}</td>
                <td></td>
                <td style="font-weight: bolder; text-align: center; border-bottom: 1px solid black;">{{ $recommendingApproval }}</td>
                <td></td>
                <td style=" font-weight: bolder; text-align: center; border-bottom: 1px solid black;">{{ $approvedBy }}</td>
            </tr>
            <tr>
                <td style="text-align: center;">{{ $submittedByTitle }}</td>
                <td></td>
                <td style="text-align: center;">{{ $recommendingApprovalTitle }}</td>
                <td></td>
                <td style="text-align: center;">{{ $approvedByTitle }}</td>
            </tr>
        </table>
    </div>
</body>

</html>