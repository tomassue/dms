<div>
    <!--begin::Footer-->
    <div class="footer py-4 d-flex flex-lg-column" id="kt_footer">
        <!--begin::Mixed Widget 5-->
        <div class="card card-xxl-stretch">
            <!--begin::Beader-->
            <div class="card-header border-0 py-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bolder fs-3 mb-1">CLENRO Document Management System</span>
                </h3>
            </div>
            <!--end::Header-->
            <!--begin::Body-->
            <div class="card-body d-flex flex-column" style="position: relative; padding-top: 0px;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div id="footer-paragraph" class="collapsed">
                            <p>
                                The CLENRO Management System is designed to assist the City Local Environment and Natural Resources Office in managing client schedules and requests. Additionally, the system helps CLENRO monitor outgoing documents.
                            </p>
                            <button id="toggle-button">Show more...</button>
                            <br>
                            <br>
                            <p>
                                <span class="fw-bold">Developed by:</span> CMISID TEAM / PM: Christine B. Daguplo / DEV: Rustom C. Abella
                            </p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <p>If you have issues encountered and inquiries:</p>
                        <p><a href="https://services.cagayandeoro.gov.ph/helpdesk/" target="_blank" class="link-info link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">CMISID Helpdesk</a></p>
                    </div>
                    <div class="col-md-4">
                        <div class="row mb-3">
                            <div class="col-12 col-lg-auto d-flex align-items-center">
                                <img src="{{ asset('images/footer/cdofull.png') }}" class="img-fluid mb-2 mb-lg-0" alt="cdo-full" width="150px">
                            </div>
                            <div class="col-12 col-lg col-md-12 col-sm-5 d-flex justify-content-lg-end align-items-center">
                                <a href="https://cagayandeoro.gov.ph/" role="button" style="white-space: nowrap;" class="btn btn-warning btn-rounded btn-fw" target="_blank">Visit Official Website</a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-lg-auto d-flex align-items-center">
                                <img src="{{ asset('images/footer/risev2.png') }}" class="img-fluid mb-2 mb-lg-0" alt="cdo-full" width="150px">
                            </div>
                            <div class="col-12 col-lg col-md-12 col-sm-5 d-flex justify-content-lg-end align-items-center">
                                <a href="https://cagayandeoro.gov.ph/index.php/news/the-city-mayor/rise1.html" role="button" style="white-space: nowrap;" class="btn btn-info btn-rounded btn-fw">Learn RISE Platform</a>
                            </div>
                        </div>
                    </div>

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
    <!--end::Footer-->
</div>

@script
<script>
    document.getElementById('toggle-button').addEventListener('click', function() {
        const paragraph = document.getElementById('footer-paragraph');
        const button = this;

        if (paragraph.classList.contains('collapsed')) {
            paragraph.classList.remove('collapsed');
            paragraph.classList.add('expanded');
            button.textContent = 'Show less...';
        } else {
            paragraph.classList.remove('expanded');
            paragraph.classList.add('collapsed');
            button.textContent = 'Show more...';
        }
    });
</script>
@endscript