<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefAccomplishmentCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class AccomplishmentCategory extends Component
{
    use WithPagination;

    public $search;
    public $editMode;
    public $accomplishmentCategoryId;
    # Properties Form
    public $name,
        $role_id;

    public function rules()
    {
        //* Determine the role_id to use since if the user is the Super Admin, we will choose the role the category is associated to. Otherwise, we will use the user's role when creating a new Accomplishment Category.
        $roleId = $this->role_id ?? auth()->user()->roles()->first()->id;

        $rules = [
            'name' => [
                'required',
                'string',
                Rule::unique('ref_accomplishment_categories')
                    ->where('role_id', $roleId)
                    ->ignore($this->accomplishmentCategoryId)
            ],
        ];

        if (auth()->user()->hasRole('Super Admin')) {
            $rules['role_id'] = 'required';
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
            'livewire.shared.settings.accomplishment-category',
            [
                'accomplishment_categories' => $this->loadAccomplishmentCategories(),
                'offices' => $this->loadOffice(), // Office dropdown
            ]
        );
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function loadAccomplishmentCategories()
    {
        return RefAccomplishmentCategory::query()
            ->withTrashed()
            ->when(auth()->user()->hasRole('Super Admin'), function ($query) {
                // Super Admin sees all
            }, function ($query) {
                $roleId = auth()->user()->roles()->first()->id; // Explicitly fails if no role
                $query->where('role_id', $roleId);
            })
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);
    }

    public function loadOffice()
    {
        //* Role
        return Role::query()
            ->whereNot('name', 'Super Admin')
            ->get();
    }

    public function createAccomplishmentCategory()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $accomplishment_category = new RefAccomplishmentCategory();
                if (auth()->user()->hasRole('Super Admin')) {
                    $accomplishment_category->role_id = $this->role_id;
                } else {
                    $accomplishment_category->role_id = auth()->user()->roles()->first()->id;
                }
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

            if (auth()->user()->hasRole('Super Admin')) {
                $this->role_id = $accomplishment_category->role_id;
            }

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
                if (auth()->user()->hasRole('Super Admin')) {
                    $accomplishment_category->role_id = $this->role_id;
                }
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
