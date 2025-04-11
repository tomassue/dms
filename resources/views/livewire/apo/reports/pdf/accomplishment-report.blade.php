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
            border: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        /* header image */
        .header-image {
            width: 70px;
            height: auto;
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
                    Division: <u>{{ $division }}</u>
                </span>
            </td>
        </tr>
    </table>

    <table style="padding-top: 5px;">
        <tr>
            <td style="vertical-align: middle; text-align: center;">
                <span style=" font-weight: bolder;">
                    <u>Weekly Division Report: date range hereee like 01-5 November 2025</u>
                </span>
            </td>
        </tr>
    </table>

    <!-- Display here your accomplishments -->
    @dump($accomplishments)

    <table style="padding-top: 15px;">
        <tr>
            <th>One</th>
            <th>Two</th>
            <th>Three</th>
        </tr>
        <tr>
            <td>sdas</td>
            <td>sdasdsd</td>
            <td>sdasdasd</td>
        </tr>
    </table>
</body>

</html>