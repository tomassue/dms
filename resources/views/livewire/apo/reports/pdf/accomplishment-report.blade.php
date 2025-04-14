<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        body {
            font-family: Arial, sans-serif;
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
            /* border: 1px solid #ddd; */
            /* the border is for debugging */
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
            /* Optional header background */
            vertical-align: top;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <td style="vertical-align: middle; text-align: left; padding-left: 10px;" width="70px">
                <!-- CDO Seal Image -->
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
                <!-- RISE Image -->
                @if(!empty($rise))
                <img src="{{ $rise }}" class="header-image" alt="RISE Logo" style="width: 150px;">
                @endif
            </td>
        </tr>
    </table>

    <table style="padding-top: 10px;">
        <tr>
            <td>
                <span style="font-weight: bolder;">
                    Division: <u>{{ $division ?? '-' }}</u>
                </span>
            </td>
        </tr>
    </table>

    <table style="padding-top: 5px;">
        <tr>
            <td style="vertical-align: middle; text-align: center;">
                <span style=" font-weight: bolder;">
                    <u>Weekly Division Report: {{ $filter_start_date || $filter_end_date ? Carbon\Carbon::parse($filter_start_date)->format('F d, Y') . ' - ' . Carbon\Carbon::parse($filter_end_date)->format('F d, Y') : 'ALL' }}</u>
                </span>
            </td>
        </tr>
    </table>

    <!-- Display here your accomplishments -->
    <table class="bordered" style="padding-top: 15px;">
        @php
        // Convert to collection if it's an array
        $accomplishments = is_array($accomplishments) ? collect($accomplishments) : $accomplishments;

        $accomplishment_category = $accomplishments->groupBy(function($item) {
        return $item['accomplishment_category']['name'] ??
        ($item->accomplishment_category->name ?? 'Uncategorized');
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

        @foreach($items as $item)
        <tr>
            <td>{{ $item['apo']['sub_category'] ?? ($item->apo->sub_category ?? 'N/A') }}</td>
            <td>{{ $item['details'] ?? ($item->details ?? '') }}</td>
            <td>{{ $item['apo']['next_steps'] ?? ($item->apo->next_steps ?? '') }}</td>
        </tr>
        @endforeach
        @endforeach
    </table>
</body>

</html>