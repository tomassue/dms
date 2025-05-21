<!--begin::Modal - PDF-->
<div class="modal fade" tabindex="-1" id="pdfModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">PDF</h5>
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clear">
                    <i class="bi bi-x-circle"></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                @if ($pdf)
                <iframe src="{{ $pdf }}" class="w-100" height="650px" frameborder="0"></iframe>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="clear">Close</button>
            </div>
        </div>
    </div>
</div>
<!--end::Modal - PDF-->

@script
<script>
    $wire.on('show-pdf-modal', () => {
        $('#pdfModal').modal('show');
    });
</script>
@endscript