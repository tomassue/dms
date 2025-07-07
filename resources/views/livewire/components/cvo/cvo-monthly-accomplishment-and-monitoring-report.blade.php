<div>
    <!--begin::Mixed Widget 5-->
    <div class="card card-xxl-stretch"> @dump($accomplishmentId)
        <!--begin::Header-->
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1">View Monthly Accomplishment and Monitoring Report (ADMIN VIEW)</span>
                <span class="text-muted fw-bold fs-7"></span>
            </h3>
            <div class="card-toolbar" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" title="" data-bs-original-title="Go Back">
                <button class="btn btn-sm btn-info" wire:click="hideMonthlyAccomplishmentAndMonitoringReport">
                    <div wire:loading.remove wire:target="hideMonthlyAccomplishmentAndMonitoringReport">
                        Go Back
                    </div>
                    <div wire:loading wire:target="hideMonthlyAccomplishmentAndMonitoringReport">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </button>
            </div>
        </div>
        <!--end::Header-->

        <!--begin::Body-->
        <div class="card-body d-flex flex-column" style="position: relative; padding-top: unset;">
            <div class="collapse show">
                <div class="row justify-content-between">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4">
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">Date</span>
                            <input type="text" class="form-control" placeholder="Date range here" aria-label="Username" aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4">
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">Category</span>
                            <select class="form-select" aria-label="Default select example">
                                <option value=" ">-Select-</option>
                                @foreach ($category_select as $item)
                                <option value="{{ $item->id }}">{{ $item->accomplishment_category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-between">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4">
                        <div class="input-group mb-3">
                            @php
                            //TODO based on the user (technician)
                            @endphp
                            <span class="input-group-text" id="basic-addon1">Technician</span>
                            <input type="text" class="form-control" placeholder="Select" aria-label="Username" aria-describedby="basic-addon1">
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4">
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">Sub-category</span>

                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-5">
                    <table class="table align-middle table-hover table-rounded border gy-7 gs-7">
                        <thead>
                            <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200 bg-light">
                                <th>Activities/Projects</th>
                                <th>Target</th>
                                <th>Accomplishment Month</th>
                                <th>Accomplishment to Date</th>
                                <th>Percentage</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold" colspan="6">Category 1</td>
                            </tr>
                            <tr>
                                <td class="fw-bold" colspan="6">Sub-category</td>
                            </tr>
                            <tr>
                                <td>Project Alpha Launch</td>
                                <td>Launch by July 2025</td>
                                <td>June 2025</td>
                                <td>90% Completed</td>
                                <td>
                                    <div class="d-flex align-items-center w-100">
                                        <div class="progress h-6px w-100 me-2 bg-light-warning">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: 90%;" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="text-gray-700 fw-bold fs-6">90%</span>
                                    </div>
                                </td>
                                <td><span class="badge badge-light-warning fw-bold">Pending Review</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold" colspan="6">Category 2</td>
                            </tr>
                            <tr>
                                <td class="fw-bold" colspan="6">Sub-category</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!--begin::Pagination-->
                <div class="pt-3">
                    <!-- page-links -->
                </div>
                <!--end::Pagination-->
            </div>

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