<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefIncomingDocumentCategory;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class IncomingDocumentCategory extends Component
{
    use WithPagination;

    public $editMode;
    public $incomingDocumentCategoryId;
    public $search;
    public $name;

    public function rules()
    {
        return [
            'name' => 'required|unique:ref_incoming_documents_categories,name,' . $this->incomingDocumentCategoryId,
        ];
    }

    public function clear()
    {
        $this->reset();
        $this->resetValidation();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view(
            'livewire.shared.settings.incoming-document-category',
            [
                'incoming_document_categories' => $this->loadIncomingDocumentCategories()
            ]
        );
    }

    public function loadIncomingDocumentCategories()
    {
        return RefIncomingDocumentCategory::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->withTrashed()
            ->paginate(10);
    }

    public function saveIncomingDocumentCategory()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                RefIncomingDocumentCategory::updateOrCreate(
                    ['id' => $this->incomingDocumentCategoryId],
                    ['name' => $this->name]
                );
                $this->clear();
                $this->dispatch('hide-incoming-document-category-modal');
                $this->dispatch('success', message: 'Incoming Document Category saved successfully.');
            });
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editIncomingDocumentCategory($incomingDocumentCategoryId)
    {
        try {
            $incoming_document_category = RefIncomingDocumentCategory::findOrFail($incomingDocumentCategoryId);
            $this->name = $incoming_document_category->name;
            $this->incomingDocumentCategoryId = $incoming_document_category->id;
            $this->editMode = true;
            $this->dispatch('show-incoming-document-category-modal');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function deleteIncomingDocumentCategory($incomingDocumentCategoryId)
    {
        try {
            $incoming_document_category = RefIncomingDocumentCategory::findOrFail($incomingDocumentCategoryId);
            $incoming_document_category->delete();
            $this->clear();
            $this->dispatch('success', message: 'Incoming Document Category successfully deleted.');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function restoreIncomingDocumentCategory($incomingDocumentCategoryId)
    {
        try {
            $incoming_document_category = RefIncomingDocumentCategory::withTrashed()->findOrFail($incomingDocumentCategoryId);
            $incoming_document_category->restore();
            $this->clear();
            $this->dispatch('success', message: 'Incoming Document Category successfully restored.');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
