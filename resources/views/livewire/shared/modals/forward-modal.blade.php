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
                            <label class="form-label required">Division</label>
                            <div wire:ignore>
                                <div id="division-select"></div>
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
        if ($wire.page == 'incoming documents') {
            @this.set('incomingDocumentId', id.id);
        }

        if ($wire.page == 'incoming requests') {
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
        multiple: true,
        maxWidth: '100%',
        dropboxWrapper: 'body', // Append to body instead of parent
        zIndex: 1060, // Higher than modal's z-index
    });

    let selected_divisions = document.querySelector('#division-select');
    selected_divisions.addEventListener('change', () => {
        let data = selected_divisions.value;
        @this.set('selected_divisions', data);
    });

    $wire.on('reset-division-select', () => {
        document.querySelector('#division-select').reset();
    });
</script>
@endscript