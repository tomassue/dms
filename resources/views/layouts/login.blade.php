<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="DMS" />
    <meta name="keywords" content="Local Government Unit of Cagayan de Oro, City Management Information Systems and Innovation Department" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="DMS" />
    <meta property="og:url" content="https://services.cagayandeoro.gov.ph:8087/" />
    <meta property="og:site_name" content="DMS" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" href="{{ asset('images/cdo-seal.png') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->

    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="{{ asset('plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <!--end::Global Stylesheets Bundle-->

    <style>
        body {
            background: linear-gradient(to top, #0E4A84 10%, #FFFFFF 90%);
            min-height: 100vh;
        }

        /* Custom btn-primary */
        .btn.btn-primary {
            color: #fff;
            border-color: #0E4A84;
            background-color: #0E4A84;
        }

        .btn-check:active+.btn.btn-primary,
        .btn-check:checked+.btn.btn-primary,
        .btn.btn-primary.active,
        .btn.btn-primary.show,
        .btn.btn-primary:active:not(.btn-active),
        .btn.btn-primary:focus:not(.btn-active),
        .btn.btn-primary:hover:not(.btn-active),
        .show>.btn.btn-primary {
            color: #fff;
            border-color: rgb(20, 54, 85);
            background-color: rgb(20, 54, 85) !important;
        }

        @media (max-width: 795px) {
            .header-images {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div id="app">
        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <livewire:templates.footer />
</body>

</html>