<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefIncomingDocumentCategory;
use App\Models\RefIncomingRequestCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Title('Incoming Request Category')]
class IncomingRequestCategory extends Component
{
    use WithPagination;

    public $editMode;
    public $search;
    public $incomingRequestCategoryId;
    public $incoming_request_category_name;
    public $office_id;

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
}
