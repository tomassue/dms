<div>
    <!--begin::Content-->
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Container-->
        <div class="container-xxl" id="kt_content_container">
            <!--begin::Row-->
            <div class="row g-5 g-xl-12">
                <!--begin::Mixed Widget 5-->
                <div class="card card-xxl-stretch">
                    <!--begin::Beader-->
                    <div class="card-header border-0 py-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder fs-3 mb-1">Accomplishment</span>
                            <span class="text-muted fw-bold fs-7">Management</span>
                        </h3>
                        <div class="card-toolbar">
                            @can('accomplishments.create')
                            <!--begin::Menu-->
                            <a href="#" class="btn btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#accomplishmentModal"><i class="bi bi-plus-circle"></i></a>
                            <!--end::Menu-->
                            @endcan
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

                        <div class="table-responsive">
                            <table class="table align-middle table-hover table-rounded table-striped border gy-7 gs-7">
                                <thead>
                                    <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                                        <th>Accomplishment Category</th>
                                        <th>Date</th>
                                        <th>Details</th>
                                        @can('accomplishments.update')
                                        <th>Actions</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>



                                    <tr>
                                        <td colspan="4" class="text-center">No records found.</td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>

                        <!--begin::Pagination-->
                        <div class="pt-3">

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
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Content-->
</div>