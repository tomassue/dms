<div>
    <!--begin::Footer-->
    <div class="footer py-4 d-flex flex-lg-column" id="kt_footer">
        <!--begin::Mixed Widget 5-->
        <div class="card card-xxl-stretch">
            <!--begin::Beader-->
            <div class="card-header border-0 py-5">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bolder fs-3 mb-1">Document Management System</span>
                </h3>
            </div>
            <!--end::Header-->
            <!--begin::Body-->
            <div class="card-body d-flex flex-column" style="position: relative; padding-top: 0px;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div id="footer-paragraph">
                            <p>
                                <span id="short-text">The Document Management System is designed to assist the Offices in managing client schedules and requests...</span>
                                <span id="full-text" style="display:none">The Document Management System is designed to assist the Offices in managing client schedules and requests. Additionally, the system helps in monitoring outgoing documents.</span>
                            </p>
                            <button class="btn btn-sm btn-secondary" id="toggle-button">Show more...</button>
                            <br><br>
                            <p>
                                <span class="fw-bold">Developed by:</span> CMISID TEAM / PM: Christine B. Daguplo / DEV: Rustom C. Abella
                            </p>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <p>If you have issues encountered and inquiries:</p>
                        <p><a href="https://services.cagayandeoro.gov.ph/helpdesk/" target="_blank" class="link-info link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover">CMISID Helpdesk</a></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="row mb-3">
                            <div class="col">
                                <div class="d-flex justify-content-between align-items-center w-100 gap-3">
                                    <div class="brand-container">
                                        <img class="w-70" src="https://services.cagayandeoro.gov.ph:8087/cvocert/imgs/seallogo.png">
                                    </div>
                                    <span>&nbsp;</span>
                                    <div class="d-flex justify-content-between gap-3">
                                        <a style="font-size: 9pt!important; background-color: #FACD06; box-shadow: 3px 3px #888888; border-radius: 20px;"
                                            class="btn btn-primary p-3 flex-grow-1 text-center">
                                            Visit Official Website
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="d-flex justify-content-between align-items-center w-100 gap-3">
                                    <div class="brand-container">
                                        <img class="w-70" src="https://services.cagayandeoro.gov.ph:8087/cvocert/imgs/risebig.png">
                                    </div>
                                    <span>&nbsp;</span>
                                    <div class="d-flex justify-content-between gap-3">
                                        <a style="font-size: 9pt!important; background-color: #5B9EB4; box-shadow: 3px 3px #888888; border-radius: 20px;"
                                            href="https://cagayandeoro.gov.ph/index.php/news/the-city-mayor/rise1.html"
                                            target="_blank"
                                            class="btn btn-primary link2 p-3 flex-grow-1 text-center">
                                            &nbsp; Learn RISE Platform
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-8 col-sm-8 col-xs-8 mt-3">
                                <div class="d-flex justify-content-start align-items-center">
                                    <div>
                                        <img src="https://services.cagayandeoro.gov.ph:8087/cvocert/imgs/ict.png">
                                    </div>
                                    <div>
                                        <div style="padding-left:10px;">Powered by: City Management Information Systems and Innovation Department</div>
                                        <div>
                                            <span>&nbsp;&nbsp;<div class="fb-like fb_iframe_widget" data-href="https://www.facebook.com/City-Management-Information-Systems-Office-LGU-CdeO-568493593557935/" data-width="" data-layout="button_count" data-action="like" data-size="small" data-share="true" data-show-faces="false"></div></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 version  mt-3">
                                <div class="text-center">
                                    <span>Version 1.0</span>
                                </div>
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
@assets
<style>
    .collapsible-content {
        display: none;
    }

    .expanded .collapsible-content {
        display: block;
    }
</style>
@endassets

@script
<script>
    document.getElementById('toggle-button').addEventListener('click', function() {
        const shortText = document.getElementById('short-text');
        const fullText = document.getElementById('full-text');
        const button = this;

        if (fullText.style.display === 'none') {
            shortText.style.display = 'none';
            fullText.style.display = 'inline';
            button.textContent = 'Show less...';
        } else {
            shortText.style.display = 'inline';
            fullText.style.display = 'none';
            button.textContent = 'Show more...';
        }
    });
</script>
@endscript