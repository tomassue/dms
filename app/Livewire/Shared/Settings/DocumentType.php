<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefDocumentType;
use App\Models\RefDocumentTypeSignatory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Title('Document Type')]
class DocumentType extends Component
{
    use WithPagination, WithFileUploads;

    public $editMode;
    public $search;
    public $document_id;
    public $document_name;
    public $office_id;

    // PDF editor state
    public $pdfEditorDocTypeId;
    public $pdfEditorDocTypeName;
    public $pdf_template;
    public $pdfEditorHeaderImageUrl;
    public $pdfEditorSignatories = [];
    public $pdfEditorSignatoryFontSize = 12;

    // Settings modal state
    public $settingsDocTypeId;
    public $settingsDocTypeName;
    public $pdf_header_image;
    public $existing_header_image;
    public $docTypeSignatories = [];
    public $signatory_font_size = 12;

    public function rules()
    {
        $rules = [
            'document_name' => 'required|unique:ref_document_type,document_name,' . $this->document_id,
        ];

        if (Auth::user()->hasRole('Super Admin')) {
            $rules['office_id'] = 'required';
        }

        return $rules;
    }

    public function clear()
    {
        $this->reset();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.shared.settings.document-type', [
            'document_types' => $this->loadDocumentTypes(),
            'offices'        => $this->loadOffices(),
        ]);
    }

    public function loadOffices()
    {
        return Role::all();
    }

    public function loadDocumentTypes()
    {
        return RefDocumentType::query()
            ->when($this->search, function ($query) {
                $query->where('document_name', 'like', '%' . $this->search . '%');
            })
            ->withTrashed()
            ->paginate(10);
    }

    public function saveIncomingRequestCategory()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $data = ['document_name' => $this->document_name];

                if (!Auth::user()->hasRole('Super Admin')) {
                    $data['office_id'] = auth()->user()->roles()->first()->id;
                } else {
                    $data['office_id'] = $this->office_id;
                }

                RefDocumentType::updateOrCreate(['id' => $this->document_id], $data);

                $this->clear();
                $this->dispatch('hide-incoming-request-category-modal');
                $this->dispatch('success', message: 'Document Type saved successfully.');
            });
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editIncomingRequestCategory(RefDocumentType $RefDocumentType)
    {
        try {
            $this->document_name = $RefDocumentType->document_name;
            $this->document_id   = $RefDocumentType->id;
            if (Auth::user()->hasRole('Super Admin')) {
                $this->office_id = $RefDocumentType->office_id;
            }
            $this->editMode = true;
            $this->dispatch('show-incoming-request-category-modal');
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function deleteIncomingRequestCategory(RefDocumentType $RefDocumentType)
    {
        try {
            $RefDocumentType->delete();
            $this->dispatch('success', message: 'Document Type deleted successfully.');
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function restoreIncomingRequestCategory($RefDocumentType)
    {
        try {
            $RefDocumentType = RefDocumentType::withTrashed()->findOrFail($RefDocumentType);
            $RefDocumentType->restore();
            $this->dispatch('success', message: 'Document Type restored successfully.');
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    // ── PDF Editor ───────────────────────────────────────────────────────────

    public function openPdfEditor(RefDocumentType $docType)
    {
        $this->pdfEditorDocTypeId      = $docType->id;
        $this->pdfEditorDocTypeName    = $docType->document_name;
        $this->pdf_template            = $docType->pdf_template ?? $this->defaultPdfTemplate($docType);
        $this->pdfEditorHeaderImageUrl = $docType->pdf_header_image
            ? Storage::disk('public')->url($docType->pdf_header_image)
            : null;
        $this->pdfEditorSignatories        = $docType->signatories
            ->map(fn ($s) => ['name' => $s->name, 'title' => $s->title])
            ->toArray();
        $this->pdfEditorSignatoryFontSize  = $docType->signatory_font_size ?? 12;
        $this->dispatch('show-doc-type-pdf-editor-modal');
    }

    public function savePdfTemplate()
    {
        $this->validate(['pdf_template' => 'nullable|string']);

        try {
            RefDocumentType::withTrashed()
                ->findOrFail($this->pdfEditorDocTypeId)
                ->update(['pdf_template' => $this->pdf_template]);

            $this->dispatch('success', message: 'PDF template saved successfully.');
            $this->dispatch('hide-doc-type-pdf-editor-modal');
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function clearPdfEditor()
    {
        $this->reset(['pdfEditorDocTypeId', 'pdfEditorDocTypeName', 'pdf_template', 'pdfEditorHeaderImageUrl', 'pdfEditorSignatories', 'pdfEditorSignatoryFontSize']);
    }

    // ── Settings (header image + signatories) ────────────────────────────────

    public function openSettings(RefDocumentType $docType)
    {
        $this->settingsDocTypeId     = $docType->id;
        $this->settingsDocTypeName   = $docType->document_name;
        $this->existing_header_image = $docType->pdf_header_image;
        $this->pdf_header_image      = null;

        $this->docTypeSignatories = $docType->signatories
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'title' => $s->title])
            ->toArray();

        if (empty($this->docTypeSignatories)) {
            $this->docTypeSignatories = [['id' => null, 'name' => '', 'title' => '']];
        }

        $this->signatory_font_size = $docType->signatory_font_size ?? 12;

        $this->dispatch('show-doc-type-settings-modal');
    }

    public function addSignatoryRow()
    {
        $this->docTypeSignatories[] = ['id' => null, 'name' => '', 'title' => ''];
    }

    public function removeSignatoryRow($index)
    {
        array_splice($this->docTypeSignatories, $index, 1);
    }

    public function saveSettings()
    {
        $this->validate([
            'pdf_header_image'          => 'nullable|image|max:2048',
            'docTypeSignatories'        => 'array',
            'docTypeSignatories.*.name' => 'required|string|max:255',
            'docTypeSignatories.*.title'=> 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () {
                $docType = RefDocumentType::withTrashed()->findOrFail($this->settingsDocTypeId);

                if ($this->pdf_header_image) {
                    if ($docType->pdf_header_image && Storage::disk('public')->exists($docType->pdf_header_image)) {
                        Storage::disk('public')->delete($docType->pdf_header_image);
                    }
                    $path = $this->pdf_header_image->storeAs(
                        'pdf-headers',
                        'doc_type_' . $this->settingsDocTypeId . '_header.' . $this->pdf_header_image->getClientOriginalExtension(),
                        'public'
                    );
                    $docType->update(['pdf_header_image' => $path]);
                    $this->existing_header_image = $path;
                }

                $docType->update(['signatory_font_size' => $this->signatory_font_size ?? 12]);

                RefDocumentTypeSignatory::where('ref_document_type_id', $this->settingsDocTypeId)->delete();

                foreach ($this->docTypeSignatories as $i => $row) {
                    if (empty(trim($row['name']))) continue;
                    RefDocumentTypeSignatory::create([
                        'ref_document_type_id' => $this->settingsDocTypeId,
                        'name'       => $row['name'],
                        'title'      => $row['title'],
                        'sort_order' => $i,
                    ]);
                }
            });

            $this->pdf_header_image = null;
            $this->dispatch('success', message: 'Settings saved successfully.');
            $this->dispatch('hide-doc-type-settings-modal');
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function removeHeaderImage()
    {
        try {
            $docType = RefDocumentType::withTrashed()->findOrFail($this->settingsDocTypeId);
            if ($docType->pdf_header_image && Storage::disk('public')->exists($docType->pdf_header_image)) {
                Storage::disk('public')->delete($docType->pdf_header_image);
            }
            $docType->update(['pdf_header_image' => null]);
            $this->existing_header_image = null;
            $this->dispatch('success', message: 'Header image removed.');
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function clearSettings()
    {
        $this->reset(['settingsDocTypeId', 'settingsDocTypeName', 'pdf_header_image', 'existing_header_image', 'docTypeSignatories', 'signatory_font_size']);
        $this->signatory_font_size = 12;
    }

    // ── Default PDF Template ─────────────────────────────────────────────────

    protected function defaultPdfTemplate(RefDocumentType $docType): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { size: A4 portrait; margin: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20mm; }
    .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
    .header h1 { font-size: 16px; margin: 0 0 4px; text-transform: uppercase; }
    .header p { margin: 0; font-size: 11px; color: #666; }
    .section { margin-bottom: 16px; }
    .label { font-weight: bold; font-size: 11px; color: #555; text-transform: uppercase; letter-spacing: 0.5px; }
    .value { margin-top: 2px; padding: 4px 0; border-bottom: 1px solid #eee; }
    .two-col { display: table; width: 100%; }
    .col { display: table-cell; width: 50%; padding-right: 10px; vertical-align: top; }
    .footer { margin-top: 40px; border-top: 1px solid #ccc; padding-top: 10px; font-size: 10px; color: #999; text-align: center; }
</style>
</head>
<body>
<div class="header">
    {{header_image}}
    <h1>{$docType->document_name}</h1>
    <p>Document Request</p>
</div>

<div class="section">
    <div class="two-col">
        <div class="col">
            <div class="label">Document No.</div>
            <div class="value">{{document_no}}</div>
        </div>
        <div class="col">
            <div class="label">Date Requested</div>
            <div class="value">{{date_requested}}</div>
        </div>
    </div>
</div>

<div class="section">
    <div class="label">Subject / Description</div>
    <div class="value">{{description}}</div>
</div>

<div class="section">
    <div class="two-col">
        <div class="col">
            <div class="label">Office / Organization</div>
            <div class="value">{{office_barangay_organization}}</div>
        </div>
        <div class="col">
            <div class="label">Location</div>
            <div class="value">{{location}}</div>
        </div>
    </div>
</div>

<div class="section">
    <div class="two-col">
        <div class="col">
            <div class="label">Contact Person</div>
            <div class="value">{{contact_person_name}}</div>
        </div>
        <div class="col">
            <div class="label">Memo No.</div>
            <div class="value">{{memo_no}}</div>
        </div>
    </div>
</div>

<div class="section">
    <div class="two-col">
        <div class="col">
            <div class="label">Contact Number</div>
            <div class="value">{{contact_person_number}}</div>
        </div>
        <div class="col">
            <div class="label">Email</div>
            <div class="value">{{contact_person_email}}</div>
        </div>
    </div>
</div>

{{signatories}}

<div class="footer">
    Generated on {{generated_at}} &mdash; {{document_type_name}}
</div>
</body>
</html>
HTML;
    }
}
