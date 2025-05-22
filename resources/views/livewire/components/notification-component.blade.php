<!-- begin::Notifications -->
<div class="d-flex align-items-center ms-1 ms-lg-3" wire:poll>
    <!--begin::Menu wrapper-->
    <div class="btn btn-icon btn-active-light-info position-relative w-30px h-30px w-md-40px h-md-40px" data-kt-menu-trigger="click" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end" wire:ignore.self>
        <!--begin::Svg Icon | path: icons/duotune/general/gen022.svg-->
        <i class="bi bi-bell"></i>
        <!--end::Svg Icon-->
        <span class="bullet bullet-dot bg-success h-6px w-6px position-absolute translate-middle top-0 start-50 animation-blink"
            style="display: {{ $notifications->isEmpty() ? 'none' : '' }};">
        </span>
    </div>

    <!--begin::Menu-->
    <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-375px" data-kt-menu="true" wire:ignore.self>
        <!--begin::Heading-->
        <div class="d-flex flex-column bgi-no-repeat rounded-top" style="background-color: #0E4A84;">
            <!--begin::Title-->
            <h3 class="text-white fw-bold px-9 mt-10 mb-6">Notifications
                <span class="fs-8 opacity-75 ps-3">{{ $notifications->count() }} reports</span>
            </h3>
            <!--end::Title-->
            <!--begin::Tabs-->
            <ul class="nav nav-line-tabs nav-line-tabs-2x nav-stretch fw-bold px-9">
                <li class="nav-item">
                    <a class="nav-link text-white opacity-75 opacity-state-100 pb-4 active" data-bs-toggle="tab" href="#kt_topbar_notifications_1">Alerts</a>
                </li>
            </ul>
            <!--end::Tabs-->
        </div>
        <!--end::Heading-->
        <!--begin::Tab content-->
        <div class="tab-content">
            <!--begin::Tab panel-->
            <div class="tab-pane fade active show" id="kt_topbar_notifications_1" role="tabpanel">
                <!--begin::Items-->
                <div class="scroll-y mh-325px my-5 px-8">
                    <!--begin::Item-->
                    @forelse($notifications as $item)
                    <div class="d-flex flex-stack py-4">
                        <!--begin::Section-->
                        <div class="d-flex align-items-center">
                            <!--begin::Symbol-->
                            <div class="symbol symbol-35px me-4">
                                <span class="symbol-label bg-light-primary">
                                    <!--begin::Svg Icon | path: icons/duotune/technology/teh008.svg-->
                                    <span class="svg-icon svg-icon-2 svg-icon-info">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path opacity="0.3" d="M10 4H21C21.6 4 22 4.4 22 5V7H10V4Z" fill="black"></path>
                                            <path opacity="0.3" d="M13 14.4V9C13 8.4 12.6 8 12 8C11.4 8 11 8.4 11 9V14.4H13Z" fill="black"></path>
                                            <path d="M10.4 3.60001L12 6H21C21.6 6 22 6.4 22 7V19C22 19.6 21.6 20 21 20H3C2.4 20 2 19.6 2 19V4C2 3.4 2.4 3 3 3H9.20001C9.70001 3 10.2 3.20001 10.4 3.60001ZM13 14.4V9C13 8.4 12.6 8 12 8C11.4 8 11 8.4 11 9V14.4H8L11.3 17.7C11.7 18.1 12.3 18.1 12.7 17.7L16 14.4H13Z" fill="black"></path>
                                        </svg>
                                    </span>
                                    <!--end::Svg Icon-->
                                </span>
                            </div>
                            <!--end::Symbol-->
                            <!--begin::Title-->
                            <div class="mb-0 me-2">
                                <a href="@switch($item['type'])
                                    @case('request')
                                    {{ route('incoming-requests') }}
                                    @break
                                    @case('document')
                                    {{ route('incoming-documents') }}
                                    @break
                                    @default
                                    @endswitch"
                                    class="fs-6 text-gray-800 text-hover-primary fw-bolder">
                                    <span class="text-capitalize">
                                        {{ $item['type'] }}&nbsp;<span class="badge badge-light">{{ $item['status'] }}</span>
                                    </span>
                                </a>
                                <div class="text-gray-400 fs-7">
                                    {{ $item['title'] }}
                                </div>
                            </div>
                            <!--end::Title-->
                        </div>
                        <!--end::Section-->
                        <!--begin::Label-->
                        <!-- With tooltip showing full date -->
                        <span class="badge bg-light text-dark" title="Created at: {{ $item['created_at'] }}">
                            {{ $item['human_time'] }}
                        </span>
                        <!--end::Label-->
                    </div>
                    @empty
                    <div>
                        It's still a blank slate.
                    </div>
                    @endforelse
                    <!--end::Item-->
                </div>
                <!--end::Items-->
            </div>
            <!--end::Tab panel-->
        </div>
        <!--end::Tab content-->
    </div>
    <!--end::Menu-->
    <!--end::Menu wrapper-->
</div>
<!--end::Notifications-->