<div>
    <!--begin::Content-->
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Container-->
        <div class="container-xxl" id="kt_content_container">
            <div class="row g-5 g-xl-8">
                <div class="col-xxl-8">
                    <!--begin::Card-->
                    <div class="card card-xxl-stretch">
                        <!--begin::Header-->
                        <div class="card-header border-0 py-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bolder fs-3 mb-1">System Backup</span>
                                <span class="text-muted fw-bold fs-7">Export the database and uploaded files</span>
                            </h3>
                        </div>
                        <!--end::Header-->
                        <!--begin::Body-->
                        <div class="card-body d-flex flex-column">
                            <!--begin::Alert-->
                            <div class="alert alert-dismissible bg-light-warning border border-warning d-flex flex-column flex-sm-row p-5 mb-5">
                                <!--begin::Icon-->
                                <span class="svg-icon svg-icon-2hx svg-icon-warning me-4 mb-5 mb-sm-0">
                                    <i class="bi bi-exclamation-triangle-fill fs-2x"></i>
                                </span>
                                <!--end::Icon-->
                                <!--begin::Wrapper-->
                                <div class="d-flex flex-column pe-0 pe-sm-10">
                                    <!--begin::Title-->
                                    <h5 class="mb-1">Note</h5>
                                    <!--end::Title-->
                                    <!--begin::Content-->
                                    <span>This will generate a ZIP file containing a full database dump and all uploaded PDF files. Depending on the amount of data, this may take a while &mdash; please don't close this page while it's generating.</span>
                                    <!--end::Content-->
                                </div>
                                <!--end::Wrapper-->
                            </div>
                            <!--end::Alert-->

                            <div>
                                <button type="button" class="btn btn-primary" wire:click="download" wire:target="download" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="download">
                                        <i class="bi bi-cloud-download-fill me-1"></i> Download Backup
                                    </span>
                                    <span wire:loading wire:target="download">
                                        <span class="spinner-border spinner-border-sm align-middle me-2"></span> Generating backup, please wait...
                                    </span>
                                </button>
                            </div>
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Card-->
                </div>
            </div>
        </div>
        <!--end::Container-->
    </div>
    <!--end::Content-->
</div>
