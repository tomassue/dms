<div>
    <!--begin::Content-->
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Container-->
        <div class="container-xxl" id="kt_content_container">
            <!--begin::Row-->
            <div class="row g-5 g-xl-12">
                <!--begin::Mixed Widget 5-->
                <div class="card card-xxl-stretch" wire:loading.class="opacity-50 pe-none">
                    <!--begin::Beader-->
                    <div class="card-header border-0 py-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder fs-3 mb-1">Incoming Request Category</span>
                            <span class="text-muted fw-bold fs-7">Over {{ $incoming_request_categories->count() }} categories</span>
                        </h3>
                        <div class="card-toolbar">
                            @can('reference.incomingRequestCategory.create')
                            <!--begin::Menu-->
                            <a href="#" class="btn btn-icon btn-secondary" data-bs-toggle="modal" data-bs-target="#incomingRequestCategoryModal"><i class="bi bi-plus-circle"></i></a>
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

                        <div class="table-responsive" wire:loading.class="opacity-50" wire:target.except="saveIncomingDocumentCategory">
                            <table class="table align-middle table-hover table-rounded table-striped border gy-7 gs-7">
                                <thead>
                                    <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                                        @role('Super Admin')
                                        <th>Office</th>
                                        @endrole
                                        <th>Name</th>
                                        <th>Status</th>
                                        @can('reference.incomingRequestCategory.update')
                                        <th>Actions</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($incoming_request_categories as $item)
                                    <tr>
                                        @role('Super Admin')
                                        <td>{{ $item->office->name }}</td>
                                        @endrole
                                        <td>{{ $item->incoming_request_category_name }}</td>
                                        <td>
                                            @if(!$item->deleted_at)
                                            <span class="badge badge-light-success">Active</span>
                                            @else
                                            <span class="badge badge-light-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                @can('reference.incomingRequestCategory.update')
                                                <button type="button" class="btn btn-icon btn-sm btn-info" title="Settings" wire:click="openSettings({{ $item->id }})">
                                                    <div wire:loading.remove wire:target="openSettings({{ $item->id }})">
                                                        <i class="bi bi-gear"></i>
                                                    </div>
                                                    <div wire:loading wire:target="openSettings({{ $item->id }})">
                                                        <div class="spinner-border spinner-border-sm" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </div>
                                                </button>

                                                <button type="button" class="btn btn-icon btn-sm btn-warning" title="Edit PDF" wire:click="openPdfEditor({{ $item->id }})">
                                                    <div wire:loading.remove wire:target="openPdfEditor({{ $item->id }})">
                                                        <i class="bi bi-file-earmark-pdf"></i>
                                                    </div>
                                                    <div wire:loading wire:target="openPdfEditor({{ $item->id }})">
                                                        <div class="spinner-border spinner-border-sm" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </div>
                                                </button>

                                                <button type="button" class="btn btn-icon btn-sm btn-secondary" title="Edit" wire:click="editIncomingRequestCategory({{ $item->id }})">
                                                    <div wire:loading.remove wire:target="editIncomingRequestCategory({{ $item->id }})">
                                                        <i class="bi bi-pencil"></i>
                                                    </div>

                                                    <div wire:loading wire:target="editIncomingRequestCategory({{ $item->id }})">
                                                        <div class="spinner-border spinner-border-sm" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </div>
                                                </button>

                                                <button
                                                    type="button"
                                                    class="btn btn-icon btn-sm {{ $item->deleted_at ? 'btn-info' : 'btn-danger' }}"
                                                    title="{{ $item->deleted_at ? 'Restore' : 'Delete' }}"
                                                    wire:click="{{ $item->deleted_at ? 'restoreIncomingRequestCategory' : 'deleteIncomingRequestCategory' }}({{ $item->id }})">

                                                    <!-- Show icon when NOT loading -->
                                                    <div wire:loading.remove
                                                        wire:target="{{ $item->deleted_at ? 'restoreIncomingRequestCategory' : 'deleteIncomingRequestCategory' }}({{ $item->id }})">
                                                        <i class="bi {{ $item->deleted_at ? 'bi-arrow-counterclockwise' : 'bi-trash' }}"></i>
                                                    </div>

                                                    <!-- Show spinner when loading -->
                                                    <div wire:loading
                                                        wire:target="{{ $item->deleted_at ? 'restoreIncomingRequestCategory' : 'deleteIncomingRequestCategory' }}({{ $item->id }})">
                                                        <div class="spinner-border spinner-border-sm" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </div>
                                                </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No records found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!--begin::Pagination-->
                        <div class="pt-3">
                            {{ $incoming_request_categories->links(data: ['scrollTo' => false]) }}
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

    <!--begin::Modal - Incoming Request Category-->
    <div class="modal fade" tabindex="-1" id="incomingRequestCategoryModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Edit' : 'Add' }} Category</h5>
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clear">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    <form wire:submit="saveIncomingRequestCategory">
                        <div class="p-2">
                            <div class="mb-10">
                                <label class="form-label required">Name</label>
                                <input type="text" class="form-control" wire:model="incoming_request_category_name">
                                @error('incoming_request_category_name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            @role('Super Admin')
                            <div class="mb-10">
                                <label class="form-label required">Office</label>
                                <select class="form-select" wire:model="office_id">
                                    <option value="">--Select an office--</option>
                                    @foreach ($offices as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('office_id')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            @endrole
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="clear">Close</button>
                    <div wire:loading.remove>
                        <button type="submit" class="btn btn-primary">{{ $editMode ? 'Update' : 'Create' }}</button>
                    </div>
                    <div wire:loading wire:target="saveIncomingRequestCategory">
                        <button class="btn btn-primary" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                            <span role="status">Loading...</span>
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
        <!--end::Modal - Incoming Request Category-->
    </div>

    <!--begin::Modal - Category Settings-->
    <div class="modal fade" tabindex="-1" id="categorySettingsModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <i class="bi bi-gear text-info fs-4"></i>
                        Settings &mdash; <span class="text-muted fw-normal">{{ $settingsCategoryName }}</span>
                    </h5>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clearSettings">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>

                <div class="modal-body">

                    {{-- Section: PDF Header Image --}}
                    <div class="mb-8">
                        <h6 class="fw-bold text-gray-700 border-bottom pb-2 mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-image text-primary"></i> PDF Header Image
                        </h6>
                        <p class="text-muted fs-7 mb-4">Upload a letterhead or logo image that will appear at the top of the generated PDF for this category. Recommended: PNG/JPG, max 2 MB.</p>

                        {{-- Current image preview --}}
                        @if ($existing_header_image)
                        <div class="d-flex align-items-start gap-3 mb-4 p-3 border border-dashed rounded bg-light">
                            <img src="{{ Storage::disk('public')->url($existing_header_image) }}"
                                 alt="Current header"
                                 class="rounded border"
                                 style="max-width: 100%; display: block;">
                            <div class="flex-grow-1">
                                <p class="mb-1 fw-semibold fs-7">Current header image</p>
                                <p class="mb-2 text-muted fs-8">{{ basename($existing_header_image) }}</p>
                                <button type="button" class="btn btn-sm btn-light-danger" wire:click="removeHeaderImage"
                                        wire:loading.attr="disabled" wire:target="removeHeaderImage">
                                    <span wire:loading.remove wire:target="removeHeaderImage">
                                        <i class="bi bi-trash me-1"></i> Remove
                                    </span>
                                    <span wire:loading wire:target="removeHeaderImage">
                                        <span class="spinner-border spinner-border-sm"></span> Removing…
                                    </span>
                                </button>
                            </div>
                        </div>
                        @endif

                        {{-- Upload new image --}}
                        <div>
                            <label class="form-label fw-semibold">{{ $existing_header_image ? 'Replace Header Image' : 'Upload Header Image' }}</label>
                            <input type="file" id="headerImageInput" class="form-control" accept="image/*">
                            @error('pdf_header_image')
                            <span class="text-danger fs-7 mt-1 d-block">{{ $message }}</span>
                            @enderror

                            <div id="newHeaderPreview" class="mt-3 p-3 border border-dashed rounded bg-light-primary d-none">
                                <p class="fw-semibold fs-7 text-primary mb-2">Preview:</p>
                                <img id="newHeaderPreviewImg" src="" alt="New header preview"
                                     class="rounded border"
                                     style="max-height: 100px; max-width: 100%; object-fit: contain;">
                            </div>
                        </div>
                    </div>

                    {{-- Section: Signatories --}}
                    <div>
                        <h6 class="fw-bold text-gray-700 border-bottom pb-2 mb-4 d-flex align-items-center justify-content-between">
                            <span class="d-flex align-items-center gap-2">
                                <i class="bi bi-pen text-primary"></i> Signatories
                            </span>
                            <button type="button" class="btn btn-sm btn-light-primary" wire:click="addSignatoryRow">
                                <i class="bi bi-plus-circle me-1"></i> Add Row
                            </button>
                        </h6>
                        <p class="text-muted fs-7 mb-4">Define the signatories whose names and titles will appear at the bottom of the PDF for this category.</p>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle gs-3 gy-3">
                                <thead class="table-light">
                                    <tr class="fw-bold fs-7 text-gray-700">
                                        <th style="width:40%">Name</th>
                                        <th style="width:40%">Title / Position</th>
                                        <th style="width:20%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($categorySignatories as $i => $sig)
                                    <tr>
                                        <td>
                                            <input type="text"
                                                   class="form-control form-control-sm @error('categorySignatories.'.$i.'.name') is-invalid @enderror"
                                                   wire:model="categorySignatories.{{ $i }}.name"
                                                   placeholder="e.g. Juan Dela Cruz">
                                            @error('categorySignatories.'.$i.'.name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="form-control form-control-sm @error('categorySignatories.'.$i.'.title') is-invalid @enderror"
                                                   wire:model="categorySignatories.{{ $i }}.title"
                                                   placeholder="e.g. City Agriculturist">
                                            @error('categorySignatories.'.$i.'.title')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                    class="btn btn-icon btn-sm btn-light-danger"
                                                    wire:click="removeSignatoryRow({{ $i }})"
                                                    title="Remove">
                                                <i class="bi bi-trash fs-6"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted fst-italic py-4">
                                            No signatories yet. Click "Add Row" to start.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="clearSettings">Close</button>
                    <button type="button" id="saveSettingsBtn" class="btn btn-primary">
                        <i class="bi bi-floppy me-1"></i> Save Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - Category Settings-->

    <!--begin::Modal - PDF Editor-->
    <div class="modal fade" tabindex="-1" id="pdfEditorModal" data-bs-backdrop="static" data-bs-keyboard="false" wire:ignore.self>
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-pdf text-warning fs-4"></i>
                        Edit PDF Template &mdash; <span class="text-muted fw-normal" id="pdfEditorCategoryLabel">{{ $pdfEditorCategoryName }}</span>
                    </h5>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close" wire:click="clearPdfEditor">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>

                <div class="modal-body p-0 d-flex" style="overflow: hidden; height: calc(100vh - 130px);">

                    {{-- Left: HTML editor --}}
                    <div class="d-flex flex-column flex-grow-1" style="min-width: 0;">
                        <div class="px-4 py-2 bg-light border-bottom d-flex align-items-center justify-content-between">
                            <span class="fw-bold fs-7 text-muted text-uppercase letter-spacing-1">HTML Template</span>
                            <button type="button" class="btn btn-sm btn-light-success" id="pdfPrintPreviewBtn">
                                <i class="bi bi-printer me-1"></i> Print Preview
                            </button>
                        </div>
                        <textarea
                            id="pdfTemplateEditor"
                            class="form-control border-0 rounded-0 font-monospace flex-grow-1"
                            style="resize: none; height: 100%; font-size: 12px; line-height: 1.6; tab-size: 4;"
                            wire:model="pdf_template"
                            placeholder="Enter HTML template here…"
                            spellcheck="false"
                        ></textarea>
                    </div>

                    {{-- Divider --}}
                    <div class="border-start"></div>

                    {{-- Right: Variables reference panel --}}
                    <div style="width: 280px; min-width: 280px; overflow-y: auto;" class="bg-light">
                        <div class="px-4 py-2 border-bottom">
                            <span class="fw-bold fs-7 text-muted text-uppercase">Available Variables</span>
                        </div>
                        <div class="p-3">
                            <p class="text-muted fs-8 mb-3">Click any variable to copy it. Wrap it in <code>@{{key}}</code> in your template.</p>
                            @foreach([
                                'header_image'                 => 'Header Image (from Settings)',
                                'signatories'                  => 'Signatories Block (from Settings)',
                                'category_no'                  => 'Control / Category No.',
                                'date_requested'               => 'Date Requested',
                                'description'                  => 'Subject / Description',
                                'office_barangay_organization' => 'Office / Organization',
                                'location'                     => 'Location',
                                'contact_person_name'          => 'Contact Person',
                                'contact_person_number'        => 'Contact Number',
                                'contact_person_email'         => 'Email',
                                'memo_no'                      => 'Memo No.',
                                'category_name'                => 'Category Name',
                                'generated_at'                 => 'Generated At (timestamp)',
                            ] as $var => $label)
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fs-8 text-gray-600">{{ $label }}</span>
                                <button
                                    type="button"
                                    class="btn btn-xs btn-light font-monospace fs-8 py-1 px-2 copy-var-btn"
                                    data-var="{{ '{{' . $var . '}' . '}' }}"
                                    title="Copy to clipboard"
                                >{{ '{{' . $var . '}' . '}' }}</button>
                            </div>
                            @endforeach
                        </div>

                        <div class="px-4 py-2 border-top border-bottom mt-2">
                            <span class="fw-bold fs-7 text-muted text-uppercase">Tips</span>
                        </div>
                        <div class="p-3 fs-8 text-muted">
                            <ul class="ps-3 mb-0">
                                <li class="mb-1">Use standard HTML &amp; inline CSS — this is rendered by DomPDF.</li>
                                <li class="mb-1">For fonts, use <code>DejaVu Sans</code> (bundled with DomPDF).</li>
                                <li class="mb-1">Avoid external images; embed them as base64 if needed.</li>
                                <li>Use <code>display:table</code> / <code>display:table-cell</code> for columns (Flexbox is limited in DomPDF).</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="clearPdfEditor">Close</button>
                    <div wire:loading.remove wire:target="savePdfTemplate">
                        <button type="button" class="btn btn-primary" wire:click="savePdfTemplate">
                            <i class="bi bi-floppy me-1"></i> Save Template
                        </button>
                    </div>
                    <div wire:loading wire:target="savePdfTemplate">
                        <button class="btn btn-primary" type="button" disabled>
                            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                            <span role="status">Saving…</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - PDF Editor-->

    @script
    <script>
        $wire.on('hide-incoming-request-category-modal', () => {
            $('#incomingRequestCategoryModal').modal('hide');
        });

        $wire.on('show-incoming-request-category-modal', () => {
            $('#incomingRequestCategoryModal').modal('show');
        });

        // Pending header file chosen but not yet uploaded (upload deferred to Save click)
        let _pendingHeaderFile = null;

        $wire.on('show-settings-modal', () => {
            $('#categorySettingsModal').modal('show');
        });

        $wire.on('hide-settings-modal', () => {
            $('#categorySettingsModal').modal('hide');
            _pendingHeaderFile = null;
            const preview = document.getElementById('newHeaderPreview');
            const img     = document.getElementById('newHeaderPreviewImg');
            if (preview) preview.classList.add('d-none');
            if (img) img.src = '';
        });

        // File selected → show preview instantly via FileReader only (no upload yet, so no re-render)
        document.addEventListener('change', function (e) {
            if (e.target.id !== 'headerImageInput') return;
            const file = e.target.files[0];
            if (!file || !file.type.startsWith('image/')) { _pendingHeaderFile = null; return; }
            _pendingHeaderFile = file;
            const reader = new FileReader();
            reader.onload = ev => {
                const preview = document.getElementById('newHeaderPreview');
                const img     = document.getElementById('newHeaderPreviewImg');
                if (!preview || !img) return;
                img.src = ev.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });

        // Save button: upload file first if one was selected, then call saveSettings
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#saveSettingsBtn')) return;
            const btn = document.getElementById('saveSettingsBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving…';

            const finish = () => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-floppy me-1"></i> Save Settings';
            };

            if (_pendingHeaderFile) {
                $wire.upload('pdf_header_image', _pendingHeaderFile,
                    () => { _pendingHeaderFile = null; $wire.saveSettings(); finish(); },
                    ()  => { finish(); }
                );
            } else {
                $wire.saveSettings();
                finish();
            }
        });

        $wire.on('show-pdf-editor-modal', () => {
            const label = document.getElementById('pdfEditorCategoryLabel');
            if (label) label.textContent = $wire.pdfEditorCategoryName;
            $('#pdfEditorModal').modal('show');
        });

        $wire.on('hide-pdf-editor-modal', () => {
            $('#pdfEditorModal').modal('hide');
        });

        // Copy variable to clipboard and insert at cursor position in the textarea
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.copy-var-btn');
            if (!btn) return;
            const varText = btn.dataset.var;
            const editor = document.getElementById('pdfTemplateEditor');

            if (editor && document.activeElement === editor) {
                const start = editor.selectionStart;
                const end   = editor.selectionEnd;
                const value = editor.value;
                editor.value = value.slice(0, start) + varText + value.slice(end);
                editor.selectionStart = editor.selectionEnd = start + varText.length;
                editor.dispatchEvent(new Event('input'));
                editor.focus();
            } else {
                navigator.clipboard.writeText(varText).then(() => {
                    const orig = btn.textContent;
                    btn.textContent = 'Copied!';
                    setTimeout(() => btn.textContent = orig, 1200);
                });
            }
        });

        // Shared: build rendered HTML from the current template with sample data substituted
        function buildPdfPreviewHtml() {
            const editor = document.getElementById('pdfTemplateEditor');
            if (!editor) return null;

            const headerUrl = $wire.pdfEditorHeaderImageUrl;
            const headerImageHtml = headerUrl
                ? `<div style="text-align:center;margin-top:-40px;"><img src="${headerUrl}" style="margin-left:-80px;max-height:200px;object-fit:contain;"></div>`
                : '';

            const sigs = $wire.pdfEditorSignatories || [];
            let signatoriesHtml = '';
            if (sigs.length > 0) {
                const cellW = Math.floor(100 / sigs.length) + '%';
                const cells = sigs.map(s =>
                    `<div style="display:table-cell;width:${cellW};text-align:center;padding:0 20px;vertical-align:top;">` +
                    `<div style="margin-top:50px;border-top:1px solid #333;padding-top:8px;">` +
                    `<strong style="font-size:12px;display:block;">${s.name}</strong>` +
                    `<span style="font-size:11px;color:#555;">${s.title}</span>` +
                    `</div></div>`
                ).join('');
                signatoriesHtml = `<div style="display:table;width:100%;margin-top:40px;">${cells}</div>`;
            }

            const sampleData = {
                header_image:                 headerImageHtml,
                signatories:                  signatoriesHtml,
                category_no:                  'RC-2025-001',
                date_requested:               'January 15, 2025',
                description:                  'Sample subject or description of the request.',
                office_barangay_organization: 'Barangay Sample / Sample Office',
                location:                     'Sample Location, City',
                contact_person_name:          'Juan Dela Cruz',
                contact_person_number:        '+63 912 345 6789',
                contact_person_email:         'juan@example.com',
                memo_no:                      'MEMO-2025-042',
                category_name:                $wire.pdfEditorCategoryName,
                generated_at:                 new Date().toLocaleString(),
            };

            const hadHeaderVar = editor.value.includes('{' + '{header_image}}');
            let html = editor.value;
            for (const [key, val] of Object.entries(sampleData)) {
                html = html.replaceAll('{' + '{' + key + '}}', val);
            }

            if (headerImageHtml && !hadHeaderVar) {
                if (html.includes('<body>')) {
                    html = html.replace('<body>', '<body>' + headerImageHtml);
                } else {
                    html = headerImageHtml + html;
                }
            }

            // Inject A4 framing: gray background, centered white page with shadow on screen;
            // @page size for print so the browser prints on A4.
            const a4Css = `<style>
                @media screen {
                    html { background:#808080; margin:0; padding:24px 0; }
                    body {
                        width: 210mm !important;
                        min-height: 297mm !important;
                        margin: 0 auto !important;
                        background: #fff !important;
                        box-shadow: 0 4px 24px rgba(0,0,0,.4) !important;
                        box-sizing: border-box !important;
                    }
                }
                @media print {
                    @page { size: A4 portrait; margin: 0; }
                    html { background: white !important; padding: 0 !important; }
                    body {
                        width: auto !important;
                        min-height: auto !important;
                        margin: 0 !important;
                        box-shadow: none !important;
                    }
                }
            </style>`;

            if (html.includes('</head>')) {
                html = html.replace('</head>', a4Css + '</head>');
            } else {
                html = a4Css + html;
            }

            return html;
        }


        // Print Preview: open rendered template with a print toolbar + auto-trigger print dialog
        document.getElementById('pdfPrintPreviewBtn')?.addEventListener('click', function () {
            let html = buildPdfPreviewHtml();
            if (!html) return;

            const printBarCss = `<style>
                @media print {
                    #__printBar { display:none!important; }
                    body { padding-top:0!important; }
                }
                #__printBar {
                    position:fixed; top:0; left:0; right:0; z-index:9999;
                    background:#fff; border-bottom:2px solid #dee2e6;
                    padding:8px 16px; display:flex; align-items:center; gap:8px;
                    font-family:system-ui,sans-serif; font-size:13px;
                    box-shadow:0 2px 8px rgba(0,0,0,.12);
                }
                #__printBar button {
                    border:none; padding:6px 14px; border-radius:4px;
                    cursor:pointer; font-size:13px; display:flex; align-items:center; gap:5px;
                }
                #__printBar .__btn-print { background:#198754; color:#fff; }
                #__printBar .__btn-close { background:#6c757d; color:#fff; }
                body { padding-top:52px!important; }
            </style>`;

            const printBar = `<div id="__printBar">
                <button class="__btn-print" onclick="window.print()">
                    <svg width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/></svg>
                    Print
                </button>
                <button class="__btn-close" onclick="window.close()">
                    &#10005; Close
                </button>
                <span style="color:#888;margin-left:8px;font-size:12px;">Print Preview — sample data shown</span>
            </div>`;

            if (html.includes('</head>')) {
                html = html.replace('</head>', printBarCss + '</head>');
            } else if (html.includes('<head>')) {
                html = html.replace('<head>', '<head>' + printBarCss);
            } else {
                html = printBarCss + html;
            }

            if (/<body[\s>]/.test(html)) {
                html = html.replace(/<body([^>]*)>/, '<body$1>' + printBar);
            } else {
                html = printBar + html;
            }

            const win = window.open('', '_blank', 'width=900,height=700,scrollbars=yes');
            win.document.write(html);
            win.document.close();
        });
    </script>
    @endscript