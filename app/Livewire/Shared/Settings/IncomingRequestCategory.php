<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefIncomingDocumentCategory;
use App\Models\RefIncomingRequestCategory;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class IncomingRequestCategory extends Component
{
    use WithPagination;

    public $editMode;
    public $search;
    public $incomingRequestCategoryId;
    public $name;

    public function rules()
    {
        return [
            'name' => 'required|unique:ref_incoming_request_categories,name,' . $this->incomingRequestCategoryId,
        ];
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
                'incoming_request_categories' => $this->loadIncomingRequestCategories()
            ]
        );
    }

    public function loadIncomingRequestCategories()
    {
        return RefIncomingRequestCategory::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->withTrashed()
            ->paginate(10);
    }

    public function saveIncomingRequestCategory()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                RefIncomingRequestCategory::updateOrCreate(
                    ['id' => $this->incomingRequestCategoryId],
                    [
                        'name' => $this->name
                    ]
                );

                $this->clear();
                $this->dispatch('hide-incoming-request-category-modal');
                $this->dispatch('success', message: 'Incoming Request Category saved successfully.');
            });
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editIncomingRequestCategory(RefIncomingRequestCategory $refIncomingRequestCategory)
    {
        try {
            $this->name = $refIncomingRequestCategory->name;
            $this->incomingRequestCategoryId = $refIncomingRequestCategory->id;
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
