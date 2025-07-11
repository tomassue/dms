<?php

namespace App\Livewire\Components\Cvo;

use App\Models\CvoAccomplishment;
use App\Models\RefAccomplishmentCategory;
use App\Models\RefAccomplishmentSubcategory;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class CvoMonthlyAccomplishmentAndMonitoringReport extends Component
{
    #[Reactive]
    public $accomplishmentId;
    public $accomplishment;
    public $ref_accomplishment_category_id;
    public $ref_accomplishment_subcategory_id;

    //! NOTHING
    public $speciesMonthlyInputs = [];
    public $calculatedAccomplishmentToDate = [];
    public $calculatedPercentages = [];
    public $selectedAccomplishmentMonth;
    public $speciesTargets = [];
    //! NOTHING

    public $entityTargetsInput = [];
    public $entityMonthlyInput = [];
    public $entityRemarksInput = [];
    public $periodTargets = [];

    public function rules()
    {
        $rules = [
            'entityTargetsInput.*.*' => 'nullable|numeric|min:0',
            'entityMonthlyInput.*.*' => 'nullable|numeric|min:0',
            'entityRemarksInput.*.*' => 'nullable|string',
        ];

        return $rules;
    }

    public function mount($accomplishmentId)
    {
        $this->accomplishmentId = $accomplishmentId;
        $this->accomplishment = CvoAccomplishment::with(['monthlySpeciesAccomplishments', 'speciesTargets'])->find($accomplishmentId);
    }

    public function updatedEntityTargetsInput($value, $key)
    {
        // $value will be the new value that was set
        // $key will be the full path of the property that changed, e.g., "category.1" or "species.5"
        $this->validateOnly("entityTargetsInput.{$key}");
        $this->savePeriodTargetsLogic($key);
        $this->dispatch('success', message: 'Target successfully saved.');
    }

    private function savePeriodTargetsLogic($changedKey = null)
    {
        try {
            $targets_to_save = $this->entityTargetsInput;
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    public function updatedEntityMonthlyInput($value, $key)
    {
        $this->validateOnly("entityMonthlyInput.{$key}");
        $this->savePeriodMonthlyInputsLogic($key);
        $this->dispatch('success', message: 'Monthly accomplishment successfully saved.');
    }

    public function savePeriodMonthlyInputsLogic($changedKey = null)
    {
        try {
            $monthly_accomplishment_to_save = $this->entityMonthlyInput;
            dd($monthly_accomplishment_to_save);
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    public function getCategorySelect()
    {
        $categories = RefAccomplishmentCategory::all();

        return $categories;
    }

    public function getSubCategorySelect()
    {
        if (!$this->ref_accomplishment_category_id) {
            return collect(); //
        }

        $sub_categories = RefAccomplishmentSubcategory::when($this->ref_accomplishment_category_id, function ($query) {
            $query->where('ref_accomplishment_category_id', $this->ref_accomplishment_category_id);
        })
            ->where('parent_id', null)
            ->get();

        return $sub_categories;
    }

    public function getCategories()
    {
        // Eager load only direct sub-categories (parent_id is null) for each category.
        // Ensure that these direct sub-categories also eager load their own species and their children (which also eager load species).
        $categories = RefAccomplishmentCategory::with(['sub_category' => function ($query) {
            // Filter to only include top-level subcategories directly under the category
            $query->whereNull('parent_id')
                ->with(['children.species', 'species']); // Eager load children (which in turn eager load their species) AND direct species
        }])
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'order' => $category->order,
                    'accomplishment_category_name' => $category->accomplishment_category_name,
                    'is_inputtable' => $category->is_inputtable,
                    'sub_categories' => $category->sub_category->map(function ($subCategory) {
                        return [
                            'id' => $subCategory->id,
                            'accomplishment_sub_category_name' => $subCategory->accomplishment_sub_category_name,
                            'is_inputtable' => $subCategory->is_inputtable,
                            'parent_id' => $subCategory->parent_id, // This should now always be null for these top-level sub_categories
                            'species' => $subCategory->species->map(function ($species) {
                                return [
                                    'id' => $species->id,
                                    'species_name' => $species->species_name,
                                ];
                            })->toArray(),
                            'children' => $subCategory->children->map(function ($childSubCategory) {
                                // IMPORTANT: Ensure child subcategories also eager load their species
                                return [
                                    'id' => $childSubCategory->id,
                                    'accomplishment_sub_category_name' => $childSubCategory->accomplishment_sub_category_name,
                                    'is_inputtable' => $childSubCategory->is_inputtable, // Pass this along if relevant for children
                                    'parent_id' => $childSubCategory->parent_id,
                                    'species' => $childSubCategory->species->map(function ($species) {
                                        return [
                                            'id' => $species->id,
                                            'species_name' => $species->species_name,
                                        ];
                                    })->toArray(),
                                ];
                            })->toArray(),
                        ];
                    })->toArray(),
                ];
            })
            ->toArray();

        return $categories;
    }

    public function render()
    {
        return view(
            'livewire.components.cvo.cvo-monthly-accomplishment-and-monitoring-report',
            [
                'category_select' => $this->getCategorySelect(),
                'sub_category_select' => $this->getSubCategorySelect(),
                'categories' => $this->getCategories(),
            ]
        );
    }

    public function hideMonthlyAccomplishmentAndMonitoringReport()
    {
        $this->dispatch('hideMonthlyAccomplishmentAndMonitoringReport');
    }
}
