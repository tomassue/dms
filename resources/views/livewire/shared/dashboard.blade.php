<div>
    <!--begin::Row-->
    <div class="row g-5 g-xl-8 justify-content-center">
        <!--begin::Col-->
        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3">
            <div class="card card-dashed">
                <div class="card-header">
                    <h3 class="card-title">Pending Request</h3>
                </div>
                <div class="card-body text-center" style="font-size: 50px;">
                    {{ $pending_incoming_requests->count() }}
                </div>
            </div>
        </div>
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3">
            <div class="card card-dashed">
                <div class="card-header">
                    <h3 class="card-title">Forwarded Request</h3>
                </div>
                <div class="card-body text-center" style="font-size: 50px;">
                    {{ $forwarded_incoming_requests->count() }}
                </div>
            </div>
        </div>
        <!--end::Col-->

        @php
        //* Hidden because completed requests are not needed at the moment. Just keeping this in case it will be needed in the future.
        @endphp
        <!--begin::Col-->
        <!-- <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3">
            <div class="card card-dashed">
                <div class="card-header">
                    <h3 class="card-title">Completed Request</h3>
                </div>
                <div class="card-body text-center" style="font-size: 50px;">
                    {{ $completed_incoming_requests->count() }}
                </div>
            </div>
        </div> -->
        <!--end::Col-->

        <!--begin::Col-->
        <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 col-xxl-3">
            <div class="card card-dashed">
                <div class="card-header">
                    <h3 class="card-title">Total Requests</h3>
                </div>
                <div class="card-body text-center" style="font-size: 50px;">
                    {{ $total_incoming_requests }}
                </div>
            </div>
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->

    <!--begin::Incoming Request-->
    <div class="row pt-5 g-5 g-xl-8">
        <!--begin::Col-->
        <div class="col-xxl-12">
            <div class="card card-dashed">
                <div class="card-header">
                    <h3 class="card-title">Incoming Requests <span class=""></span></h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-rounded table-striped border gy-7 gs-7 align-middle">
                            <thead>
                                <tr class="fw-bold fs-6 text-gray-800 border-bottom border-gray-200">
                                    <th>No.</th>
                                    <th>Date Requested</th>
                                    <th>Office/Brgy/Org</th>
                                    <th>Category</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($incoming_requests as $item)
                                <tr style="cursor: pointer" onclick="window.location='{{ route('incoming-requests') }}'">
                                    <td>{{ $item->no }}</td>
                                    <td>{{ $item->date_requested }}</td>
                                    <td>{{ $item->office_barangay_organization }}</td>
                                    <td>{{ $item->category->incoming_request_category_name }}</td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $item->request_age }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td class="text-center" colspan="5">No records found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-3 mb-5">
                            {{ $incoming_requests->links(data: ['scrollTo' => false]) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Col-->
    </div>
    <!--end::Incoming Request-->

    @role('CITY VETERINARY OFFICE')
    <!--begin::Incoming Documents-->
    <div class="row pt-5 g-5 g-xl-8">
        <!--begin::Col-->
        <div class="col-xxl-12">
            <div class="card card-dashed">
                <div class="card-header">
                    <h3 class="card-title">Incoming Documents <span class=""></span></h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-rounded table-striped border gy-7 gs-7 align-middle">
                            <thead>
                                <tr class="fw-bold fs-6 text-gray-800 border-bottom border-gray-200">
                                    <th>Document Category</th>
                                    <th>Info</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($incoming_documents as $item)
                                <tr style="cursor: pointer" onclick="window.location='{{ route('incoming-documents') }}'">
                                    <td>{{ $item->category->incoming_document_category_name }}</td>
                                    <td>{{ $item->document_info }}</td>
                                    <td>{{ $item->formatted_date }}</td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $item->document_age }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td class="text-center" colspan="4">No records found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-3 mb-5">
                            {{ $incoming_documents->links(data: ['scrollTo' => false]) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Col-->
    </div>
    <!--end::Incoming Documents-->
    @endrole
</div>