@extends('layouts.app')

@section('content')
<div class="d-flex flex-column flex-column-fluid text-center p-10 py-lg-15">
    <!--begin::Logo-->
    <!-- <a href="../../demo4/dist/index.html" class="mb-10 pt-lg-10">
        <img alt="Logo" src="assets/media/logos/logo-1.svg" class="h-40px mb-5">
    </a> -->
    <!--end::Logo-->
    <!--begin::Wrapper-->
    <div class="pt-lg-10 mb-10">
        <!--begin::Logo-->
        <h1 class="fw-bolder fs-2qx text-gray-800 mb-7">Welcome, {{ auth()->user()->name }}</h1>
        <!--end::Logo-->
        <!--begin::Message-->
        <div class="fw-bold fs-3 text-muted mb-15">Manage and track your documents
            <br> efficiently.
        </div>
        <!--end::Message-->
        <!--begin::Action-->
        <div class="text-center">
            <a href="{{ route('dashboard') }}" class="btn btn-lg btn-primary fw-bolder">Go to dashboard</a>
        </div>
        <!--end::Action-->
    </div>
    <!--end::Wrapper-->
    <!--begin::Illustration-->
    <div class="d-flex flex-row-auto bgi-no-repeat bgi-position-x-center bgi-size-contain bgi-position-y-bottom min-h-100px min-h-lg-350px" style="background-image: url({{ asset('images/undraw_online-collaboration_xon8.svg') }})"></div>
    <!--end::Illustration-->
</div>
@endsection