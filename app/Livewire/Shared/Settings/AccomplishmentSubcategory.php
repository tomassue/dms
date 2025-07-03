<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefAccomplishmentCategory;
use App\Models\RefAccomplishmentSubcategory;
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
                    ->where('office_id', $officeId)
                    ->ignore($this->accomplishmentSubcategoryId)
            ],
        ];

        if (auth()->user()->hasRole('Super Admin')) {
            $rules['office_id'] = 'required';
        }

        if (auth()->user()->hasRole('CITY VETERINARY OFFICE')) {
            $rules['order'] = 'required';
            $rules['speciesInputs.*.species_name'] = 'required';
        }

        return $rules;
    }

    // Lifecycle hook: runs once when component is initialized
    public function mount()
    {
        // Initialize with one empty species input if adding a new subcategory
        // or load existing species if editing
        if (!$this->editMode) {
            $this->addSpeciesInput(); // Start with one empty field for new entries
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
            $data = [
                'ref_accomplishment_category_id' => $this->ref_accomplishment_category_id,
                'accomplishment_sub_category_name' => $this->accomplishment_sub_category_name,
            ];

            if (auth()->user()->hasRole('Super Admin')) {
                $data['office_id'] = $this->office_id;
            } else {
                $data['office_id'] = auth()->user()->roles()->first()->id;
            }

            if (auth()->user()->hasRole('CITY VETERINARY OFFICE')) {
                $data['order'] = $this->order;
            }

            RefAccomplishmentSubcategory::updateOrCreate(
                ['id' => $this->accomplishmentSubcategoryId],
                $data
            );

            $this->clear();
            $this->dispatch('hide-accomplishment-subcategory-modal');
            $this->dispatch('success', message: 'Accomplishment Sub-category saved successfully.');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editAccomplishmentSubcategory(RefAccomplishmentSubcategory $accomplishment_subcategory)
    {
        $this->editMode = true;
        $this->accomplishmentSubcategoryId = $accomplishment_subcategory->id;

        $this->ref_accomplishment_category_id = $accomplishment_subcategory->ref_accomplishment_category_id;
        $this->accomplishment_sub_category_name = $accomplishment_subcategory->accomplishment_sub_category_name;

        if (auth()->user()->hasRole('Super Admin')) {
            $this->office_id = $accomplishment_subcategory->office_id;
        }

        if (auth()->user()->hasRole('CITY VETERINARY OFFICE')) {
            $this->order = $accomplishment_subcategory->order;
        }

        $this->dispatch('show-accomplishment-subcategory-modal');
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
        $accomplishment_categories = RefAccomplishmentCategory::orderBy('accomplishment_category_name', 'asc')
            ->get();

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
                'accomplishment_categories' => $this->loadAccomplishmentCategories(), // Accomplishment Category dropdown
                'offices' => $this->loadOffices(), // Office dropdown
            ]
        );
    }
}
