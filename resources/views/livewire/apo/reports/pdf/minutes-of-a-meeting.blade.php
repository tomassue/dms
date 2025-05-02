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
                <img src="{{ $cdo_seal }}" class="header-image" alt="CDO Seal" style="width: 90px;">
                @endif
            </td>
            <td style="vertical-align: middle; width: 320px;">
                <span style="font-weight: bolder; text-transform: uppercase;">
                    Republic of the Philippines
                    <br>
                    <span style="text-transform: capitalize;">City of Cagayan de Oro</span>
                    <br>
                    Office of the City Agriculturist
                </span>
            </td>
            <td style="vertical-align: middle; text-align: left; padding-right: 10px; width: 70px;">
                @if (!empty($bagong_pilipinas))
                <img src="{{ $bagong_pilipinas }}" class="header-image" alt="Bagong Pilipinas" style="width: 90px;">
                @endif
            </td>
            <td style="vertical-align: middle; text-align: left; padding-right: 10px;">
                @if(!empty($golden_cdo))
                <img src="{{ $golden_cdo }}" class="header-image" alt="Golden CDO Logo" style="width: 150px;">
                @endif
            </td>
        </tr>
    </table>

    <table style="margin-top: 20px;">
        <tr>
            <td colspan="3" style="text-align: center; font-weight: bold;">Office Mancom Meeting: {{ Carbon\Carbon::parse($apo_meeting->date)->format('F Y') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Date: {{ $apo_meeting->formatted_date }}</td>
            <td style="font-weight: bold;">Time: {{ $apo_meeting->time_range }}</td>
            <td style="font-weight: bold;">Venue: {{ $apo_meeting->venue }}</td>
        </tr>
    </table>

    <table style="margin-top: 10px; border: 1px solid #ddd;">
        <tr>
            <th style="border: 1px solid #ddd;" width="25%">Activities</th>
            <th style="border: 1px solid #ddd;" width="25%">Point Person</th>
            <th style="border: 1px solid #ddd;" width="25%">Expected Output</th>
            <th style="border: 1px solid #ddd;" width="25%">Agreements</th>
        </tr>
        @forelse($minutes_of_meeting as $index => $item)
        <tr>
            <td style="border: 1px solid #ddd;">{{ $index + 1 . '. ' . $item->activity }}</td>
            <td style="border: 1px solid #ddd;">{{ $item->point_person ?? '' }}</td>
            <td style="border: 1px solid #ddd;">{{ $item->expected_output ?? '' }}</td>
            <td style="border: 1px solid #ddd;">{{ $item->agreements ?? '' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="4" style="text-align: center;">No records.</td>
        </tr>
        @endforelse
    </table>

    <table style="margin-top: 20px;">
        <tr>
            <th style="vertical-align: top; width: 50%;">Prepared by:</th>
            <th style="vertical-align: top; width: 25%;">Approved by:</th>
            <th style="vertical-align: top; width: 25%;">Noted by:</th>
        </tr>
        <tr>
            <td style="height: 60px; vertical-align: bottom;">{{ $apo_meeting->preparedBy->name ?? '' }} <br> {{ $apo_meeting->preparedBy->user_metadata?->position->name ?? '' }}</td>
            <td style="height: 60px; vertical-align: bottom;">{{ $apo_meeting->approvedBy->name ?? '' }} <br> {{ $apo_meeting->approvedBy->user_metadata?->position->name ?? '' }}</td>
            <td style="height: 60px; vertical-align: bottom;">{{ $apo_meeting->notedBy->name ?? '' }} <br> {{ $apo_meeting->notedBy->user_metadata?->position->name ?? '' }}</td>
        </tr>
    </table>
</body>

</html>