<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefDocumentType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Title('Document Type')]
class DocumentType extends Component
{
    
    public $editMode;
    public $search;
    public $document_id;
    public $document_name;
    public $office_id;

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
        return view('livewire.shared.settings.document-type',
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
                $data = [
                    'document_name' => $this->document_name,
                ];

                if (!Auth::user()->hasRole('Super Admin')) {
                    $data['office_id'] = auth()->user()->roles()->first()->id;
                } else {
                    $data['office_id'] = $this->office_id;
                }

                // dd($data);
                RefDocumentType::updateOrCreate(
                    ['id' => $this->document_id],
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

    public function editIncomingRequestCategory(RefDocumentType $RefDocumentType)
    {
        try {
            $this->document_name = $RefDocumentType->document_name;
            $this->document_id = $RefDocumentType->id;
            if (Auth::user()->hasRole('Super Admin')) {
                $this->office_id = $RefDocumentType->office_id;
            }

            $this->editMode = true;
            $this->dispatch('show-incoming-request-category-modal');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function deleteIncomingRequestCategory(RefDocumentType $RefDocumentType)
    {
        try {
            $RefDocumentType->delete();
            $this->dispatch('success', message: 'Incoming Request Category deleted successfully.');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function restoreIncomingRequestCategory($RefDocumentType)
    {
        try {
            $RefDocumentType = RefDocumentType::withTrashed()->findOrFail($RefDocumentType);
            $RefDocumentType->restore();
            $this->dispatch('success', message: 'Incoming Request Category successfully restored.');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
