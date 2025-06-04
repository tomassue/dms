<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        @page {
            /* Increased top margin to accommodate header */
            margin: 120px 25px 80px 25px;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Fixed header */
        #header {
            position: fixed;
            top: -120px;
            /* Adjusted to match @page margin */
            left: 0;
            right: 0;
            height: 100px;
            /* Reduced height slightly */
            padding: 10px;
            background-color: white;
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
        .content {
            margin-top: 10px;
            /* Increased margin */
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
    <table id="header">
        <tr>
            <td style="vertical-align: middle; text-align: left; padding-left: 10px;" width="70px">
                @if(!empty($cdo_seal))
                <img src="{{ $cdo_seal }}" class="header-image" alt="CDO Seal">
                @endif
            </td>
            <td style="vertical-align: bottom;">
                Republic of the Philippines
                <br>
                City of Cagayan de Oro
                <br>
                <span style="font-weight: bolder; text-transform: uppercase;">Office of the City Agriculturist</span>
            </td>
            <td style="vertical-align: middle; text-align: right; padding-right: 10px;">
                @if(!empty($rise))
                <img src="{{ $rise }}" class="header-image" alt="RISE Logo" style="width: 150px;">
                @endif
            </td>
        </tr>
    </table>

    <!-- Fixed Footer -->
    <div id="footer">
        @role('APOO')
        <span style="font-weight: bold;">Vision:</span> <span style="font-style: italic;">"A Leading Hub for Resilient Agri-Technology and Innovation for Fishery and Farm Product"</span> <br>
        <span style="font-weight: bold;">Mission:</span> <span style="font-style: italic;">"To enhance Agri-Productivity Towards Better Household Income"</span>
        @endrole
    </div>

    <!-- Main Content -->
    <div class="content">
        <table>
            <tr>
                <td>
                    <span style="font-weight: bolder;">
                        Division: <u>{{ $division ?? '-' }}</u>
                    </span>
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td style="vertical-align: middle; text-align: center;">
                    <span style="font-weight: bolder;">
                        <u>Weekly Division Report: {{ $filter_start_date || $filter_end_date ? Carbon\Carbon::parse($filter_start_date)->format('F d, Y') . ' - ' . Carbon\Carbon::parse($filter_end_date)->format('F d, Y') : 'ALL' }}</u>
                    </span>
                </td>
            </tr>
        </table>

        <!-- Display here your accomplishments -->
        <table class="bordered" style="margin-top: 15px;">
            @php
            $accomplishments = is_array($accomplishments) ? collect($accomplishments) : $accomplishments;
            $accomplishment_category = $accomplishments->groupBy(function($item) {
            return $item['accomplishment_category']['accomplishment_category_name'] ??
            ($item->accomplishment_category->accomplishment_category_name ?? 'Uncategorized');
            });
            @endphp

            <tr>
                <th width="25%">OBJECT OF EXPENDITURE <br> (commodity, OMOE, Capital Outlay)</th>
                <th width="45%">Accomplished Activities/Status/Updates</th>
                <th width="30%">Next Steps</th>
            </tr>

            @foreach($accomplishment_category as $category => $items)
            <tr>
                <td colspan="3"><strong>{{ $category }}</strong></td>
            </tr>

            @foreach($items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}. {{ $item['apo']['sub_category'] ?? ($item->apo->sub_category ?? 'N/A') }}</td>
                <td>{{ $item['details'] ?? ($item->details ?? '') }}</td>
                <td>{{ $item['apo']['next_steps'] ?? ($item->apo->next_steps ?? '') }}</td>
            </tr>
            @endforeach
            @endforeach
        </table>

        @role('APOO')
        <table style="margin-top: 25px;">
            <tr style="font-weight: bold;">
                <td>Prepared by:</td>
                <td></td>
                <td>Conforme:</td>
                <td></td>
                <td>Approved:</td>
            </tr>
            <tr>
                <td style="height: 40px; font-weight: bold; text-transform: uppercase; vertical-align: bottom">{{ $prepared_by ?? '' }}</td>
                <td></td>
                <td style="height: 40px; font-weight: bold; text-transform: uppercase; vertical-align: bottom">{{ $conforme ?? '' }}</td>
                <td></td>
                <td style="height: 40px; font-weight: bold; text-transform: uppercase; vertical-align: bottom">{{ $approved ?? '' }}</td>
            </tr>
            <tr>
                <td style="vertical-align: top; border-top: 1px solid black; text-transform: uppercase;">
                    {{ trim(($prepared_by_position ?? '') . ($prepared_by_position && $prepared_by_division ? ', ' : '') . ($prepared_by_division ?? '')) }}
                </td>
                <td></td>
                <td style="vertical-align: top; border-top: 1px solid black; text-transform: uppercase;">
                    {{ trim(($conforme_position ?? '') . ($conforme_position && $conforme_division ? ', ' : '') . ($conforme_division ?? '')) }}
                </td>
                <td></td>
                <td style="vertical-align: top; border-top: 1px solid black; text-transform: uppercase;">
                    {{ trim(($approved_position ?? '') . ($approved_position && $approved_division ? ', ' : '') . ($approved_division ?? '')) }}
                </td>
            </tr>
        </table>
        @endrole
    </div>
</body>

</html>