<!--begin::Row-->
<div class="row g-5 g-xl-8 col-xxl-4">
    <div class="col-xxl-12">
        <!--begin::Mixed Widget 5-->
        <div class="card">
            <!--begin::Header-->
            <div class="card-header border-0 py-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bolder fs-3 mb-1">Recents</span>
                    <span class="text-muted fw-bold fs-7">Forwarded {{ $page }}</span>
                </h3>
            </div>
            <!--end::Header-->

            <!--begin::Body-->
            <div class="card-body d-flex flex-column" style="position: relative;">
                <!-- begin::Items -->
                <div class="timeline-label">
                    @foreach ($recent_forwards as $item)
                    @if($page == 'incoming requests')
                    @if(!empty($item->forwardable->no))
                    <!--begin::Item-->
                    <div class="timeline-item">
                        <!--begin::Label-->
                        <div class="timeline-label fw-bolder text-gray-800 fs-9">
                            {{ $item->updated_at->diffForHumans() }}
                        </div>
                        <!--end::Label-->

                        <!--begin::Text-->
                        <div class="fw-mormal timeline-content text-muted ps-3">
                            {{ $item->forwardable->no ?? '' }}
                            forwarded to
                            {{ $item->division->name ?? '' }}
                            &nbsp;
                            <span class="badge badge-light"
                                style="display:{{ $item->is_opened ? '' : 'none' }}">
                                Opened
                            </span>
                        </div>
                        <!--end::Text-->
                    </div>
                    <!--end::Item-->
                    @endif
                    @elseif($page == 'incoming documents')
                    <!--begin::Item-->
                    <div class="timeline-item">
                        <!--begin::Label-->
                        <div class="timeline-label fw-bolder text-gray-800 fs-9">
                            {{ $item->updated_at->diffForHumans() }}
                        </div>
                        <!--end::Label-->

                        <!--begin::Text-->
                        <div class="fw-mormal timeline-content text-muted ps-3">
                            {{ $item->forwardable->category->name ?? '' }}
                            forwarded to
                            {{ $item->division->name ?? '' }}
                            &nbsp;
                            <span class="badge badge-light"
                                style="display:{{ $item->is_opened ? '' : 'none' }}">
                                Opened
                            </span>
                        </div>
                        <!--end::Text-->
                    </div>
                    <!--end::Item-->
                    @endif

                    @endforeach
                </div>
                <!-- end::Items -->

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
</div>
<!--end::Row-->