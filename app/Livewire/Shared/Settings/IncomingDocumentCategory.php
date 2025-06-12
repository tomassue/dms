<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefIncomingDocumentCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Title('Incoming Document Category')]
class IncomingDocumentCategory extends Component
{
    use WithPagination;

    public $editMode;
    public $incomingDocumentCategoryId;
    public $search;
    public $incoming_document_category_name, $office_id;

    public function rules()
    {
        $rules = [
            'incoming_document_category_name' => 'required|unique:ref_incoming_documents_categories,incoming_document_category_name,' . $this->incomingDocumentCategoryId,
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

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view(
            'livewire.shared.settings.incoming-document-category',
            [
                'incoming_document_categories' => $this->loadIncomingDocumentCategories(),
                'offices' => $this->loadOffices(), // Office Dropdown
            ]
        );
    }

    public function loadIncomingDocumentCategories()
    {
        return RefIncomingDocumentCategory::query()
            ->when($this->search, function ($query) {
                $query->where('incoming_document_category_name', 'like', '%' . $this->search . '%');
            })
            ->withTrashed()
            ->paginate(10);
    }

    /**
     * Load Offices
     * Only shown when superadmin adds categories since we will be assigning categories to a specific office.
     */
    public function loadOffices()
    {
        return Role::all();
    }

    public function saveIncomingDocumentCategory()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $data = [
                    'incoming_document_category_name' => $this->incoming_document_category_name,
                ];

                if (!Auth::user()->hasRole('Super Admin')) {
                    $data['office_id'] = auth()->user()->roles()->first()->id;
                } else {
                    $data['office_id'] = $this->office_id;
                }

                RefIncomingDocumentCategory::updateOrCreate(
                    ['id' => $this->incomingDocumentCategoryId],
                    $data
                );

                $this->clear();
                $this->dispatch('hide-incoming-document-category-modal');
                $this->dispatch('success', message: 'Incoming Document Category saved successfully.');
            });
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editIncomingDocumentCategory($incomingDocumentCategoryId)
    {
        try {
            $incoming_document_category = RefIncomingDocumentCategory::findOrFail($incomingDocumentCategoryId);
            $this->incoming_document_category_name = $incoming_document_category->incoming_document_category_name;
            if (Auth::user()->hasRole('Super Admin')) {
                $this->office_id = $incoming_document_category->office_id;
            }

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
