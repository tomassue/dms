<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefAccomplishmentCategory;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class AccomplishmentCategory extends Component
{
    use WithPagination;

    public $search;
    public $editMode;
    public $accomplishmentCategoryId;
    # Properties Form
    public $name;

    public function rules()
    {
        return [
            'name' => 'required|string|unique:ref_accomplishment_categories,name,' . $this->accomplishmentCategoryId,
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
            'livewire.shared.settings.accomplishment-category',
            [
                'accomplishment_categories' => $this->loadAccomplishmentCategories()
            ]
        );
    }

    public function loadAccomplishmentCategories()
    {
        return RefAccomplishmentCategory::query()
            ->withTrashed()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);
    }

    public function createAccomplishmentCategory()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $accomplishment_category = new RefAccomplishmentCategory();
                $accomplishment_category->name = $this->name;
                $accomplishment_category->save();
            });

            $this->clear();
            $this->dispatch('hide-accomplishment-category-modal');
            $this->dispatch('success', message: 'Accomplishment Category successfully created.');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editAccomplishmentCategory($accomplishmentCategoryId)
    {
        try {
            $accomplishment_category = RefAccomplishmentCategory::findOrFail($accomplishmentCategoryId);
            $this->name = $accomplishment_category->name;
            $this->accomplishmentCategoryId = $accomplishment_category->id;

            $this->editMode = true;
            $this->dispatch('show-accomplishment-category-modal');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function updateAccomplishmentCategory()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $accomplishment_category = RefAccomplishmentCategory::findOrFail($this->accomplishmentCategoryId);
                $accomplishment_category->name = $this->name;
                $accomplishment_category->save();

                $this->clear();
                $this->dispatch('hide-accomplishment-category-modal');
                $this->dispatch('success', message: 'Accomplishment Category successfully updated.');
            });
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function deleteAccomplishmentCategory($accomplishmentCategoryId)
    {
        try {
            $accomplishment_category = RefAccomplishmentCategory::findOrFail($accomplishmentCategoryId);
            $accomplishment_category->delete();
            $this->dispatch('success', message: 'Accomplishment Category successfully deleted.');
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function restoreAccomplishmentCategory($accomplishmentCategoryId)
    {
        try {
            $accomplishment_category = RefAccomplishmentCategory::withTrashed()->findOrFail($accomplishmentCategoryId);
            $accomplishment_category->restore();
            $this->dispatch('success', message: 'Accomplishment Category successfully restored.');
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
