<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

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
            background-image: radial-gradient(#639a56 1px, transparent 1px);
            background-size: 30px 30px;
            background-color: #f8f9fa;
            animation: dotsMove 60s linear infinite;
            min-height: 100vh;
        }

        /* Custom btn-primary */
        .btn.btn-primary {
            color: #fff;
            border-color: #639a56;
            background-color: #639a56;
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
            border-color: #4d6d46;
            background-color: #4d6d46 !important;
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