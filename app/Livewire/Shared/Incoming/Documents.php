<?php

namespace App\Livewire\Shared\Incoming;

use App\Models\Apo\IncomingDocument as ApoIncomingDocument;
use App\Models\File;
use App\Models\IncomingDocument;
use App\Models\RefIncomingDocumentCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Documents extends Component
{
    use WithPagination, WithFileUploads;

    public $editMode;
    public $search;
    public $incomingDocumentId;
    public $preview_file = [];

    /* ---------------------------- begin::Properties --------------------------- */
    public $ref_incoming_document_category_id,
        $document_info,
        $date,
        $remarks,
        $file_id = []; // for file upload - MorphMany
    //* APO
    public $source;
    /* ----------------------------- end::Properties ---------------------------- */

    public function rules()
    {
        $rules = [
            'ref_incoming_document_category_id' => 'required|exists:ref_incoming_documents_categories,id',
            'document_info' => 'required',
            'date' => 'required|date',
            'remarks' => 'required',
            'file_id' => 'required',
        ];

        if (auth()->user()->hasRole('APO')) {
            $rules += [
                'source' => 'required',
            ];
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'ref_incoming_document_category_id' => 'document category',
            'file_id' => 'file',
        ];
    }

    public function clear()
    {
        $this->reset();
        $this->resetValidation();

        $this->dispatch('reset-files');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function loadIncomingDocuments()
    {
        return IncomingDocument::query()
            ->with('apoDocument')
            ->paginate(10);
    }

    public function loadRefIncomingDocumentCategory()
    {
        return RefIncomingDocumentCategory::all();
    }

    public function render()
    {
        return view(
            'livewire.shared.incoming.documents',
            [
                'incoming_documents' => $this->loadIncomingDocuments(),
                'incoming_documents_categories' => $this->loadRefIncomingDocumentCategory(), // Incoming Document Category dropdown
            ]
        );
    }

    public function editIncomingDocument(IncomingDocument $incomingDocument)
    {
        try {
            $this->ref_incoming_document_category_id = $incomingDocument->ref_incoming_document_category_id;
            $this->document_info = $incomingDocument->document_info;
            $this->date = $incomingDocument->date;
            $this->remarks = $incomingDocument->remarks;
            $this->preview_file = $incomingDocument->files;

            if (auth()->user()->hasRole('APO')) {
                $this->source = $incomingDocument->apoDocument->source ?? '';
            }

            $this->incomingDocumentId = $incomingDocument->id;
            $this->editMode = true;
            $this->dispatch('show-incoming-document-modal');
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function saveIncomingDocument()
    {
        $this->validate($this->rules(), [], $this->attributes());

        try {
            DB::transaction(function () {
                // Save main document (without file_id in initial create)
                $incomingDocument = $this->saveMainIncomingDocument();

                // Handle file uploads polymorphically
                $this->saveFiles($incomingDocument);

                // Save APO data if applicable
                $this->saveApoIncomingDocument($incomingDocument);

                $this->clear();
                $this->dispatch('hide-incoming-document-modal');
                $this->dispatch('success', message: 'Incoming Document successfully saved with files.');
            });
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    protected function saveMainIncomingDocument()
    {
        $data = [
            'ref_incoming_document_category_id' => $this->ref_incoming_document_category_id,
            'document_info' => $this->document_info,
            'date' => $this->date,
            'remarks' => $this->remarks
        ];

        return IncomingDocument::updateOrCreate(
            ['id' => $this->incomingDocumentId ?? null],
            $data
        );
    }


    protected function saveApoIncomingDocument($incomingDocument)
    {
        if (!auth()->user()->hasRole('APO')) return; // Return if not APO

        return ApoIncomingDocument::updateOrCreate(
            ['incoming_document_id' => $incomingDocument->id], // Update if exists. Otherwise, create
            [
                'source' => $this->source,
            ]
        );
    }

    protected function saveFiles($model)
    {
        if (empty($this->file_id)) return null;

        $uploadedFiles = [];

        foreach ((array)$this->file_id as $file) {
            $uploadedFiles[] = $model->files()->create([
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'type' => $file->getMimeType(),
                'file' => file_get_contents($file->getRealPath()),
                // fileable_id and fileable_type are auto-set by morphMany
            ]);
        }

        return $uploadedFiles;
    }

    public function viewFile($id)
    {
        $signedURL = URL::temporarySignedRoute(
            'file.view',
            now()->addMinutes(10),
            ['id' => $id]
        );

        // Dispatch an event to the browser to open the URL in a new tab
        $this->dispatch('open-file', url: $signedURL);
    }
}
