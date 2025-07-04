<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefAccomplishmentCategory;
use App\Models\RefAccomplishmentSubcategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Title('Accomplishment Sub-category')]
class AccomplishmentSubcategory extends Component
{
    use WithPagination;

    public $search;
    public $editMode;
    public $accomplishmentSubcategoryId;

    # FORM
    public $ref_accomplishment_category_id,
        $accomplishment_sub_category_name,
        $order,
        $office_id;

    public $parent_sub_category_id = null; // NEW: Property for the parent sub-category
    public $speciesInputs = []; // Property for dynamic species inputs

    public function rules()
    {
        //* Determine the office_id to use since if the user is the Super Admin, we will choose the role the category is associated to. Otherwise, we will use the user's role when creating a new Accomplishment Category.
        $officeId = $this->office_id ?? auth()->user()->roles()->first()->id;

        $rules = [
            'ref_accomplishment_category_id' => 'required|exists:ref_accomplishment_categories,id',
            'accomplishment_sub_category_name' => [
                'required',
                'string',
                Rule::unique('ref_accomplishment_sub_categories')
                    ->where(function ($query) use ($officeId) {
                        $query->where('office_id', $officeId)
                            ->where('ref_accomplishment_category_id', $this->ref_accomplishment_category_id)
                            ->where('parent_id', $this->parent_sub_category_id); // <--- THIS IS THE KEY PART FOR HIERARCHICAL UNIQUENESS
                    })
                    ->ignore($this->accomplishmentSubcategoryId)
            ],
        ];

        if (auth()->user()->hasRole('Super Admin')) {
            $rules['office_id'] = 'required';
        }

        if (auth()->user()->hasRole('CITY VETERINARY OFFICE')) {
            $rules['order'] = 'required';
        }

        return $rules;
    }

    // Lifecycle hook: runs once when component is initialized
    public function mount()
    {
        // Initialize with one empty species input if adding a new subcategory
        // or load existing species if editing
        if (!$this->editMode) {
            // $this->addSpeciesInput(); // Start with one empty field for new entries
        }
    }

    // Method to add a new species input field
    public function addSpeciesInput()
    {
        $this->speciesInputs[] = ['id' => null, 'species_name' => '']; // Initialize with null ID for new entries
    }

    // Method to remove a species input field
    public function removeSpeciesInput($index)
    {
        // Optional: If you're deleting existing species, you'd handle that here (e.g., mark for deletion, or immediately delete from DB if appropriate)
        // For now, this just removes it from the form array.
        unset($this->speciesInputs[$index]);
        $this->speciesInputs = array_values($this->speciesInputs); // Re-index the array
    }

    // Gets the latest order and assign it to the property
    public function getLatestOrder()
    {
        $latestOrder = RefAccomplishmentSubcategory::max('order');
        $this->order = $latestOrder + 1;
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

    public function saveAccomplishmentSubcategory()
    {
        $this->validate();

        try {
            $subCategoryData = [
                'ref_accomplishment_category_id' => $this->ref_accomplishment_category_id,
                'accomplishment_sub_category_name' => $this->accomplishment_sub_category_name,
                'parent_id' => $this->parent_sub_category_id, // <--- THIS SAVES THE PARENT RELATIONSHIP
            ];

            if (auth()->user()->hasRole('Super Admin')) {
                $subCategoryData['office_id'] = $this->office_id;
            } else {
                $subCategoryData['office_id'] = auth()->user()->roles()->first()->id;
            }

            if (auth()->user()->hasRole('CITY VETERINARY OFFICE')) {
                $subCategoryData['order'] = $this->order;
            }

            DB::transaction(function () use ($subCategoryData) {
                $subCategory = RefAccomplishmentSubcategory::updateOrCreate(
                    ['id' => $this->accomplishmentSubcategoryId],
                    $subCategoryData
                );

                if (auth()->user()->hasRole('CITY VETERINARY OFFICE')) {
                    $this->accomplishmentSubcategoryId = $subCategory->id;

                    $currentSpeciesIdsInForm = [];

                    foreach ($this->speciesInputs as $speciesData) {
                        $species = $subCategory->species()->updateOrCreate(
                            ['id' => $speciesData['id']],
                            [
                                'species_name' => $speciesData['species_name'],
                                'office_id' => auth()->user()->hasRole('Super Admin') ? $this->office_id : auth()->user()->roles()->first()->id
                            ]
                        );

                        $currentSpeciesIdsInForm[] = $species->id;
                    }

                    $subCategory->species()->whereNotIn('id', $currentSpeciesIdsInForm)->forceDelete();
                }
            });

            $this->clear();
            $this->dispatch('hide-accomplishment-subcategory-modal');
            $this->dispatch('success', message: 'Accomplishment Sub-category saved successfully.');
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editAccomplishmentSubcategory(RefAccomplishmentSubcategory $accomplishment_subcategory)
    {
        $this->editMode = true;
        $this->accomplishmentSubcategoryId = $accomplishment_subcategory->id;

        $this->ref_accomplishment_category_id = $accomplishment_subcategory->ref_accomplishment_category_id;
        $this->parent_sub_category_id = $accomplishment_subcategory->parent_id; // <--- THIS LOADS THE PARENT RELATIONSHIP FOR EDITING
        $this->accomplishment_sub_category_name = $accomplishment_subcategory->accomplishment_sub_category_name;

        if (auth()->user()->hasRole('Super Admin')) {
            $this->office_id = $accomplishment_subcategory->office_id;
        }

        if (auth()->user()->hasRole('CITY VETERINARY OFFICE')) {
            $this->order = $accomplishment_subcategory->order;

            $this->speciesInputs = $accomplishment_subcategory->species->map(function ($species) {
                return ['id' => $species->id, 'species_name' => $species->species_name];
            })->toArray();

            // if (empty($this->speciesInputs)) {
            //     $this->addSpeciesInput();
            // }
        }

        $this->dispatch('show-accomplishment-subcategory-modal');
    }

    // NEW: Load subcategories that can be selected as parents
    public function loadParentSubcategories()
    {
        if (!$this->ref_accomplishment_category_id) {
            return collect(); //
        }

        $query = RefAccomplishmentSubcategory::where('ref_accomplishment_category_id', $this->ref_accomplishment_category_id)
            ->where('parent_id', null);

        // Prevent a sub-category from being its own parent or a descendant of itself
        if ($this->editMode && $this->accomplishmentSubcategoryId) {
            $query->where('id', '!=', $this->accomplishmentSubcategoryId);
            $descendants = $this->getDescendants($this->accomplishmentSubcategoryId);
            $query->whereNotIn('id', $descendants);
        }

        if (!auth()->user()->hasRole('Super Admin')) {
            $query->where('office_id', auth()->user()->roles()->first()->id);
        }

        return $query->get();
    }

    // Helper method to recursively get all descendants of a category to prevent circular relationships
    private function getDescendants($categoryId)
    {
        $descendants = collect();
        $children = RefAccomplishmentSubcategory::where('parent_id', $categoryId)->pluck('id');

        foreach ($children as $childId) {
            $descendants->push($childId);
            $descendants = $descendants->merge($this->getDescendants($childId));
        }

        return $descendants->unique()->toArray();
    }

    public function loadAccomplishmentSubcategories()
    {
        $accomplishment_subcategories = RefAccomplishmentSubcategory::query()
            ->search($this->search)
            ->withTrashed()
            ->paginate(10);

        return $accomplishment_subcategories;
    }

    public function loadAccomplishmentCategories()
    {
        $accomplishment_categories = RefAccomplishmentCategory::all();

        return $accomplishment_categories;
    }

    public function loadOffices()
    {
        $role = Role::query()
            ->whereNot('name', 'Super Admin')
            ->get();

        return $role;
    }

    public function render()
    {
        return view(
            'livewire.shared.settings.accomplishment-subcategory',
            [
                'accomplishment_subcategories' => $this->loadAccomplishmentSubcategories(),
                'parent_sub_categories' => $this->loadParentSubcategories(), // NEW: Pass to view
                'accomplishment_categories' => $this->loadAccomplishmentCategories(), // Accomplishment Category dropdown
                'offices' => $this->loadOffices(), // Office dropdown
            ]
        );
    }
}
