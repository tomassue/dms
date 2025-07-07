<div>
    <!--begin::Mixed Widget 5-->
    <div class="card card-xxl-stretch" style="display: {{ $showMonthlyAccomplishmentAndMonitoringReport ? 'none' : '' }}">
        <!--begin::Header-->
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bolder fs-3 mb-1">Monthly Accomplishment and Monitoring Report</span>
                <span class="text-muted fw-bold fs-7">Over --count-- accomplishments</span>
            </h3>
            <div class="card-toolbar">
                <div class="d-flex align-items-center gap-2">
                    <!--begin::Menu 2-->
                    @can('accomplishments.create')
                    <a href="#" class="btn btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#accomplishmentModal"><i class="bi bi-plus-circle"></i></a>
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
                            @can('accomplishments.update')
                            <th class="text-center">Actions</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accomplishments as $item)
                        <tr>
                            <td>{{ $item->formatted_half_year_period }}</td>
                            <td class="text-center" wire:loading.class="pe-none">
                                @can('accomplishments.update')
                                <div class="btn-group" role="group" aria-label="Actions">
                                    <button type="button" class="btn btn-icon btn-sm btn-secondary" title="Edit" wire:click="viewMonthlyAccomplishmentAndMonitoringReport({{ $item->id }})" @click.stop>
                                        <div wire:loading.remove wire:target="viewMonthlyAccomplishmentAndMonitoringReport({{ $item->id }})">
                                            <i class="bi bi-eye"></i>
                                        </div>
                                        <div wire:loading wire:target="viewMonthlyAccomplishmentAndMonitoringReport({{ $item->id }})">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </button>
                                    <button type="button" class="btn btn-icon btn-sm btn-warning" title="Edit" wire:click="editAccomplishment({{ $item->id }})" @click.stop>
                                        <div wire:loading.remove wire:target="editAccomplishment({{ $item->id }})">
                                            <i class="bi bi-pencil"></i>
                                        </div>
                                        <div wire:loading wire:target="editAccomplishment({{ $item->id }})">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </button>
                                    <button type="button" class="btn btn-icon btn-sm btn-info" title="Log" wire:click="activityLog({{ $item->id }})" @click.stop>
                                        <div wire:loading.remove wire:target="activityLog({{ $item->id }})">
                                            <i class="bi bi-clock-history"></i>
                                        </div>
                                        <div wire:loading wire:target="activityLog({{ $item->id }})">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </button>
                                </div>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="text-center">No records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!--begin::Pagination-->
            <div class="pt-3">
                <!-- page-links -->
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

    <!--begin::Modal - Accomplishments-->
    <div class="modal fade" tabindex="-1" id="accomplishmentModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Edit' : 'Add' }} Accomplishment</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clear">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <!--end::Close-->
                </div>
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <div class="modal-body">
                    <form wire:submit="saveAccomplishment">
                        <div class="p-2">
                            <div class="mb-3">
                                <label for="halfYearSelection" class="form-label required">Select Half-Year Period</label>
                                <select class="form-select" id="halfYearSelection" name="selected_half_year" wire:model="target">
                                    <option value="">Select Period</option>
                                    @php
                                    $currentYear = date('Y'); // Get the current year (e.g., 2025)
                                    $endYear = $currentYear + 2; // Display current year + next 2 years
                                    @endphp

                                    @for ($year = $currentYear; $year <= $endYear; $year++)
                                        {{-- First Half: January to June --}}
                                        <option value="{{ $year }}-H1">January to June {{ $year }}</option>

                                        {{-- Second Half: July to December --}}
                                        <option value="{{ $year }}-H2">July to December {{ $year }}</option>
                                        @endfor
                                </select>
                                @error('target')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="clear">Close</button>
                    <button type="submit" class="btn btn-primary">{{ $editMode ? 'Update' : 'Create' }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - Accomplishments-->

    <div style="display: {{ $showMonthlyAccomplishmentAndMonitoringReport ? '' : 'none' }}">
        <livewire:components.cvo.cvo-monthly-accomplishment-and-monitoring-report :accomplishmentId="$accomplishmentId" />
    </div>

</div>

@script
<script>
    $wire.on('hide-accomplishment-modal', () => {
        $('#accomplishmentModal').modal('hide');
    });

    $wire.on('show-accomplishment-modal', () => {
        $('#accomplishmentModal').modal('show');
    });
</script>
@endscript