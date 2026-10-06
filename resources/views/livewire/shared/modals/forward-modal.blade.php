<!--begin::Modal - Forward-->
<div class="modal fade" tabindex="-1" id="forwardModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Forward</h5>
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clear">
                    <i class="bi bi-x-circle"></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <form wire:submit="forward">
                    <div class="p-2">
                        <div class="mb-10">
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="form-label required mb-0">Division</label>
                                <div class="form-check form-check-solid form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="manualForwardSwitch"
                                        wire:click="toggleManualForward"
                                        {{ $manualForward ? 'checked' : '' }}>
                                    <label class="form-check-label fs-7 text-muted" for="manualForwardSwitch">Select manually</label>
                                </div>
                            </div>
                            <div wire:ignore>
                                <div id="division-select"></div>
                            </div>
                            <div class="form-text">
                                {{ $manualForward ? 'Pick the division to forward this to.' : 'Automatically forwarding to the next division in order. Turn on "Select manually" to pick a different one.' }}
                            </div>
                            @error('selected_divisions')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="clear">Close</button>
                <div wire:loading.remove wire:target="forward">
                    <button type="submit" class="btn btn-primary">Forward</button>
                </div>
                <div wire:loading wire:target="forward">
                    <button class="btn btn-primary" type="button" disabled>
                        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <span role="status">Loading...</span>
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end::Modal - Forward-->

@script
<script>
    $wire.on('show-forward-modal', (id) => {
        // Lock the dropdown by default - prepareForwardDivision below will
        // enable it again only if it couldn't auto-pick a next division.
        document.querySelector('#division-select').disable();

        if ($wire.page == 'incoming documents') {
            $wire.prepareForwardDivision(id.id); // Auto-pick the next division in order; falls back to manual if it can't.

            @this.set('incomingDocumentId', id.id);
        }

        if ($wire.page == 'incoming requests') {
            $wire.prepareForwardDivision(id.id); // Auto-pick the next division in order; falls back to manual if it can't.

            @this.set('incomingRequestId', id.id);
        }

        $('#forwardModal').modal('show');
    });

    $wire.on('hide-forward-modal', (id) => {
        $('#forwardModal').modal('hide');
    });

    /* -------------------------------------------------------------------------- */

    VirtualSelect.init({
        ele: '#division-select',
        options: @json($divisions),
        maxWidth: '100%',
        dropboxWrapper: 'body', // Append to body instead of parent
        zIndex: 1060, // Higher than modal's z-index
    });

    // Single-select (radio-style) - a request/document can only be forwarded
    // to one division at a time, so we keep selected_divisions as a
    // one-item array to stay compatible with the existing forward() logic.
    let selected_divisions = document.querySelector('#division-select');
    selected_divisions.addEventListener('change', () => {
        let data = selected_divisions.value;
        @this.set('selected_divisions', data ? [data] : []);
    });

    $wire.on('reset-division-select', () => {
        // Dispatched when no next division could be auto-determined -
        // fall back to letting the user pick manually.
        document.querySelector('#division-select').reset();
        document.querySelector('#division-select').enable();
    });

    $wire.on('set-division-select', (data) => {
        // Dispatched with the auto-picked next division - keep the
        // dropdown locked unless the user turns on manual selection.
        document.querySelector('#division-select').setValue(data[0][0]);
        if (! $wire.manualForward) {
            document.querySelector('#division-select').disable();
        }
    });

    $wire.on('enable-division-select', () => {
        document.querySelector('#division-select').enable();
    });
</script>
@endscript