<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefOutgoingCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Title('Outgoing Category')]
class OutgoingCategory extends Component
{
    use WithPagination;

    public $editMode;
    public $outgoingCategoryId;
    public $outgoing_category_name, $office_id;

    public function clear()
    {
        $this->reset();
        $this->resetValidation();
    }

    public function loadOffices()
    {
        return Role::all();
    }

    public function loadOutgoingCategories()
    {
        return RefOutgoingCategory::withTrashed()
            ->paginate(10);
    }

    public function countTotalOutgoingCategories()
    {
        return RefOutgoingCategory::count();
    }

    public function render()
    {
        $data = [
            'total_outgoing_categories' => $this->countTotalOutgoingCategories(),
            'outgoing_categories' => $this->loadOutgoingCategories(),
            'offices'             => $this->loadOffices(),
        ];

        return view('livewire.shared.settings.outgoing-category', $data);
    }

    public function rules()
    {
        $rules = [
            'outgoing_category_name' => [
                'required',
                Rule::unique('ref_outgoing_categories', 'outgoing_category_name')
                    ->where(fn($query) => $query->where('office_id', $this->office_id))
                    ->ignore($this->outgoingCategoryId)
            ],
        ];

        if (Auth::user()->hasRole('Super Admin')) {
            $rules['office_id'] = 'required';
        } else {
            $rules['office_id'] = 'nullable';
        }

        return $rules;
    }

    public function saveOutgoingCategory()
    {
        $this->validate();

        try {
            $data = [
                'outgoing_category_name' => $this->outgoing_category_name,
            ];

            if (!Auth::user()->hasRole('Super Admin')) {
                $data['office_id'] = auth()->user()->roles()->first()->id;
            } else {
                $data['office_id'] = $this->office_id;
            }

            RefOutgoingCategory::updateOrCreate(
                ['id' => $this->outgoingCategoryId],
                $data
            );

            $this->clear();
            $this->dispatch('hide-outgoing-category-modal');
            $this->dispatch('success', message: 'Outgoing Category saved successfully.');
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function getOutgoingCategory(RefOutgoingCategory $refOutgoingCategory)
    {
        try {
            $this->editMode = true;
            $this->outgoingCategoryId = $refOutgoingCategory->id;
            $this->outgoing_category_name = $refOutgoingCategory->outgoing_category_name;
            $this->office_id = $refOutgoingCategory->office_id;

            $this->dispatch('show-outgoing-category-modal');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function deleteOutgoingCategory(RefOutgoingCategory $refOutgoingCategory)
    {
        try {
            $refOutgoingCategory->delete();
            $this->dispatch('success', message: 'Outgoing Category deleted successfully.');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function restoreOutgoingCategory($refOutgoingCategory)
    {
        try {
            $refOutgoingCategory = RefOutgoingCategory::withTrashed()->findOrFail($refOutgoingCategory);
            $refOutgoingCategory->restore();
            $this->dispatch('success', message: 'Outgoing Category successfully restored.');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
