<div>
    <!--begin::Mixed Widget 5-->
    <div class="card card-xxl-stretch">
        <!--begin::Header-->
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1">Minutes of Meeting</span>
                <span class="text-muted fw-bold fs-7">Over -count- minutes of meetings</span>
            </h3>
            <div class="card-toolbar">
                <div class="d-flex align-items-center gap-2">
                    <!--begin::Menu Filter-->
                    <livewire:components.menu-filter-component />
                    <!--end::Menu Filter-->

                    <!--begin::Menu 2-->
                    @can('outgoing.create')
                    <div class="vr"></div> <!-- Vertical Divider -->
                    <a href="#" class="btn btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#outgoingModal"><i class="bi bi-plus-circle"></i></a>
                    @endcan
                    <!--end::Menu 2-->
                </div>
            </div>
        </div>
        <!--end::Header-->

        <!--begin::Body-->
        <div class="card-body d-flex flex-column" style="position: relative;">
            <!-- begin:search -->
            <div class="row py-5 justify-content-between">
                <div class="col-sm-12 col-md-12 col-lg-4">
                    <input type="search" wire:model.live="search" class="form-control" placeholder="Type a keyword..." aria-label="Type a keyword..." style="appearance: none; background-color: #fff; border: 1px solid #eff2f5; border-radius: 5px; font-size: 14px; line-height: 1.45; outline: 0; padding: 10px 13px;">
                </div>
            </div>
            <!-- end:search -->

            <div class="table-responsive" wire:loading.class="opacity-50" wire:target.except="saveOutgoing">
                <table class="table align-middle table-hover table-rounded border gy-7 gs-7">
                    <thead>
                        <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200 bg-light">
                            <th>Date</th>
                            <th>Time</th>
                            <th>Description</th>
                            <th>Prepared by</th>
                            <th>Approved by</th>
                            <th>Noted by</th>
                            @can('outgoing.update')
                            <th class="text-center">Actions</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="text-center">No records found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!--begin::Pagination-->
            <div class="pt-3">
                <!-- Links -->
            </div>
            <!--end::Pagination-->

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