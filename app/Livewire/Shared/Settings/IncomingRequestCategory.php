<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefIncomingRequestCategory;
use App\Models\RefIncomingRequestCategorySignatory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Title('Incoming Request Category')]
class IncomingRequestCategory extends Component
{
    use WithPagination, WithFileUploads;

    public $editMode;
    public $search;
    public $incomingRequestCategoryId;
    public $incoming_request_category_name;
    public $office_id;

    // PDF editor state
    public $pdfEditorCategoryId;
    public $pdfEditorCategoryName;
    public $pdf_template;
    public $pdfEditorHeaderImageUrl;
    public $pdfEditorSignatories = [];

    // Settings modal state
    public $settingsCategoryId;
    public $settingsCategoryName;
    public $pdf_header_image;         // Livewire temp upload
    public $existing_header_image;    // current stored path
    public $categorySignatories = []; // [['name'=>'','title'=>'']]

    public function rules()
    {
        $rules = [
            'incoming_request_category_name' => 'required|unique:ref_incoming_request_categories,incoming_request_category_name,' . $this->incomingRequestCategoryId,
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
        return view(
            'livewire.shared.settings.incoming-request-category',
            [
                'incoming_request_categories' => $this->loadIncomingRequestCategories(),
                'offices' => $this->loadOffices() // Office Dropdown
            ]
        );
    }

    /**
     * Load Offices
     * Only shown when superadmin adds categories since we will be assigning categories to a specific office.
     */
    public function loadOffices()
    {
        return Role::all();
    }

    public function loadIncomingRequestCategories()
    {
        return RefIncomingRequestCategory::query()
            ->when($this->search, function ($query) {
                $query->where('incoming_request_category_name', 'like', '%' . $this->search . '%');
            })
            ->withTrashed()
            ->paginate(10);
    }

    public function saveIncomingRequestCategory()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $data = [
                    'incoming_request_category_name' => $this->incoming_request_category_name,
                ];

                if (!Auth::user()->hasRole('Super Admin')) {
                    $data['office_id'] = auth()->user()->roles()->first()->id;
                } else {
                    $data['office_id'] = $this->office_id;
                }

                RefIncomingRequestCategory::updateOrCreate(
                    ['id' => $this->incomingRequestCategoryId],
                    $data
                );

                $this->clear();
                $this->dispatch('hide-incoming-request-category-modal');
                $this->dispatch('success', message: 'Incoming Request Category saved successfully.');
            });
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editIncomingRequestCategory(RefIncomingRequestCategory $refIncomingRequestCategory)
    {
        try {
            $this->incoming_request_category_name = $refIncomingRequestCategory->incoming_request_category_name;
            $this->incomingRequestCategoryId = $refIncomingRequestCategory->id;
            if (Auth::user()->hasRole('Super Admin')) {
                $this->office_id = $refIncomingRequestCategory->office_id;
            }

            $this->editMode = true;
            $this->dispatch('show-incoming-request-category-modal');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function deleteIncomingRequestCategory(RefIncomingRequestCategory $refIncomingRequestCategory)
    {
        try {
            $refIncomingRequestCategory->delete();
            $this->dispatch('success', message: 'Incoming Request Category deleted successfully.');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function restoreIncomingRequestCategory($refIncomingRequestCategory)
    {
        try {
            $refIncomingRequestCategory = RefIncomingRequestCategory::withTrashed()->findOrFail($refIncomingRequestCategory);
            $refIncomingRequestCategory->restore();
            $this->dispatch('success', message: 'Incoming Request Category successfully restored.');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function openPdfEditor(RefIncomingRequestCategory $category)
    {
        $this->pdfEditorCategoryId      = $category->id;
        $this->pdfEditorCategoryName    = $category->incoming_request_category_name;
        $this->pdf_template             = $category->pdf_template ?? $this->defaultPdfTemplate($category);
        $this->pdfEditorHeaderImageUrl  = $category->pdf_header_image
            ? Storage::disk('public')->url($category->pdf_header_image)
            : null;
        $this->pdfEditorSignatories     = $category->signatories
            ->map(fn ($s) => ['name' => $s->name, 'title' => $s->title])
            ->toArray();
        $this->dispatch('show-pdf-editor-modal');
    }

    public function savePdfTemplate()
    {
        $this->validate(['pdf_template' => 'nullable|string']);

        try {
            RefIncomingRequestCategory::withTrashed()
                ->findOrFail($this->pdfEditorCategoryId)
                ->update(['pdf_template' => $this->pdf_template]);

            $this->dispatch('success', message: 'PDF template saved successfully.');
            $this->dispatch('hide-pdf-editor-modal');
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function clearPdfEditor()
    {
        $this->reset(['pdfEditorCategoryId', 'pdfEditorCategoryName', 'pdf_template', 'pdfEditorHeaderImageUrl', 'pdfEditorSignatories']);
    }

    // ── Settings (header image + signatories) ────────────────────────────────

    public function openSettings(RefIncomingRequestCategory $category)
    {
        $this->settingsCategoryId   = $category->id;
        $this->settingsCategoryName = $category->incoming_request_category_name;
        $this->existing_header_image = $category->pdf_header_image;
        $this->pdf_header_image     = null;

        $this->categorySignatories = $category->signatories
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name, 'title' => $s->title])
            ->toArray();

        if (empty($this->categorySignatories)) {
            $this->categorySignatories = [['id' => null, 'name' => '', 'title' => '']];
        }

        $this->dispatch('show-settings-modal');
    }

    public function addSignatoryRow()
    {
        $this->categorySignatories[] = ['id' => null, 'name' => '', 'title' => ''];
    }

    public function removeSignatoryRow($index)
    {
        array_splice($this->categorySignatories, $index, 1);
    }

    public function saveSettings()
    {
        $this->validate([
            'pdf_header_image'           => 'nullable|image|max:2048',
            'categorySignatories'        => 'array',
            'categorySignatories.*.name' => 'required|string|max:255',
            'categorySignatories.*.title'=> 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () {
                $category = RefIncomingRequestCategory::withTrashed()->findOrFail($this->settingsCategoryId);

                // Handle header image upload
                if ($this->pdf_header_image) {
                    // Delete old image if exists
                    if ($category->pdf_header_image && Storage::disk('public')->exists($category->pdf_header_image)) {
                        Storage::disk('public')->delete($category->pdf_header_image);
                    }
                    $path = $this->pdf_header_image->storeAs(
                        'pdf-headers',
                        'category_' . $this->settingsCategoryId . '_header.' . $this->pdf_header_image->getClientOriginalExtension(),
                        'public'
                    );
                    $category->update(['pdf_header_image' => $path]);
                    $this->existing_header_image = $path;
                }

                // Sync signatories: delete all then re-insert in order
                RefIncomingRequestCategorySignatory::where('ref_incoming_request_category_id', $this->settingsCategoryId)->delete();

                foreach ($this->categorySignatories as $i => $row) {
                    if (empty(trim($row['name']))) continue;
                    RefIncomingRequestCategorySignatory::create([
                        'ref_incoming_request_category_id' => $this->settingsCategoryId,
                        'name'       => $row['name'],
                        'title'      => $row['title'],
                        'sort_order' => $i,
                    ]);
                }
            });

            $this->pdf_header_image = null;
            $this->dispatch('success', message: 'Settings saved successfully.');
            $this->dispatch('hide-settings-modal');
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function removeHeaderImage()
    {
        try {
            $category = RefIncomingRequestCategory::withTrashed()->findOrFail($this->settingsCategoryId);
            if ($category->pdf_header_image && Storage::disk('public')->exists($category->pdf_header_image)) {
                Storage::disk('public')->delete($category->pdf_header_image);
            }
            $category->update(['pdf_header_image' => null]);
            $this->existing_header_image = null;
            $this->dispatch('success', message: 'Header image removed.');
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function clearSettings()
    {
        $this->reset(['settingsCategoryId', 'settingsCategoryName', 'pdf_header_image', 'existing_header_image', 'categorySignatories']);
    }

    // ── PDF Template ─────────────────────────────────────────────────────────

    protected function defaultPdfTemplate(RefIncomingRequestCategory $category): string
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
    <h1>{$category->incoming_request_category_name}</h1>
    <p>Incoming Request</p>
</div>

<div class="section">
    <div class="two-col">
        <div class="col">
            <div class="label">Control No.</div>
            <div class="value">{{category_no}}</div>
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
    Generated on {{generated_at}} &mdash; {{category_name}}
</div>
</body>
</html>
HTML;
    }
}
