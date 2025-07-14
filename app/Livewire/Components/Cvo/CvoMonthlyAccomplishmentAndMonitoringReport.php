<?php

namespace App\Livewire\Components\Cvo;

use App\Models\CvoAccomplishment;
use App\Models\CvoPeriodTarget;
use App\Models\RefAccomplishmentCategory;
use App\Models\RefAccomplishmentSubcategory;
use App\Models\RefSpecies;
use Illuminate\Support\Facades\DB;
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

    public $speciesTargets = [];
    //! NOTHING

    public $entityTargetsInput = [];
    public $selectedAccomplishmentMonth;
    public $entityMonthlyInput = [];
    public $entityRemarksInput = [];
    public $periodTargets = [];
    // Property to control autosave status messages
    public $autosaveMessage = '';
    public $autosaveError = '';

    public function rules()
    {
        return [
            // Targets
            'entityTargetsInput.*.*.target_value' => 'nullable|numeric|min:0', // For targets: entityTargetsInput.type.id.target_value

            // Monthly Accomplishments
            'selectedAccomplishmentMonth' => 'required', // ADDED: Rule for selected month
            'entityMonthlyInputs.*.*.*.accomplished_value' => 'nullable|numeric|min:0', // For monthly accomplishments: entityMonthlyInputs.type.id.month.accomplished_value
            'entityMonthlyInputs.*.*.*.remarks' => 'nullable|string|max:500', // For monthly remarks: entityMonthlyInputs.type.id.month.remark. Added max:500 from previous suggestions
        ];
    }

    public function messages()
    {
        return [
            // Targets
            'entityTargetsInput.*.*.target_value.numeric' => 'The target value must be a number.',
            'entityTargetsInput.*.*.target_value.min' => 'The target value must be at least 0.',

            // Monthly Accomplishments
            'selectedAccomplishmentMonth.required' => 'Please select a month before entering data.', // ADDED: Custom message
            'entityMonthlyInputs.*.*.*.accomplished_value.numeric' => 'The accomplishment value must be a number.',
            'entityMonthlyInputs.*.*.*.accomplished_value.min' => 'The accomplishment value must be at least 0.',
            'entityMonthlyInputs.*.*.*.remarks.string' => 'The remarks must be text.',
            'entityMonthlyInputs.*.*.*.remarks.max' => 'The remarks cannot be more than 500 characters.',
        ];
    }

    public function mount($accomplishmentId)
    {
        $this->accomplishmentId = $accomplishmentId;
        $this->accomplishment = CvoAccomplishment::with(['monthlySpeciesAccomplishments', 'speciesTargets'])->find($accomplishmentId);
        $this->loadAccomplishmentData();
    }

    public function loadAccomplishmentData()
    {
        // ... (previous code)

        // Load Period Targets - IMPORTANT: Load into the correct nested structure
        $targets = CvoPeriodTarget::where('cvo_accomplishment_id', $this->accomplishmentId)->get();
        $this->entityTargetsInput = []; // Reset to ensure fresh load
        foreach ($targets as $target) {
            $type = $this->getTypeFromModelClass($target->targetable_type);
            $this->entityTargetsInput[$type] ??= []; // Ensure the type array exists
            $this->entityTargetsInput[$type][$target->targetable_id] = [
                'target_value' => $target->target_value
            ];
        }

        // ... (rest of the code)
    }

    public function updatedEntityTargetsInput($value, $key)
    {
        // $value will be the new value that was set
        // Same logic as above. The $key already holds the path after 'entityTargetsInput.'
        $this->validateOnly('entityTargetsInput.*.*.target_value'); // Use the wildcard rule path
        $this->savePeriodTargetsLogic($key);
    }

    //TODO
    //! ERROR
    public function updatedEntityMonthlyInput($value, $key)
    {
        // $value will be the new value that was set
        // Same logic as above. The $key already holds the path after 'entityMonthlyInput.'

        // First, validate that a month is selected
        $this->validateOnly('selectedAccomplishmentMonth');

        // Then, validate the specific monthly input that changed
        // $key for monthly inputs is like "category.1.7.accomplished_value" or "category.1.7.remarks"
        $this->validateOnly('entityMonthlyInputs.' . $key);

        // If validation passes, proceed with saving
        $this->savePeriodMonthlyInputsLogic($key);
    }

    private function savePeriodTargetsLogic($changedKey = null)
    {
        if (!$this->accomplishmentId) {
            throw new \Exception('No accomplishment period selected to save targets.');
        }

        DB::transaction(function () use ($changedKey) {
            $targetsToSave = [];

            if ($changedKey && str_contains($changedKey, '.')) {
                // Parse the key: e.g., "category.1.target_value"
                $parts = explode('.', $changedKey, 3); // Max 3 parts: type, id, field_name
                $entityType = $parts[0];
                $entityId = $parts[1];
                $fieldName = $parts[2] ?? null; // Should be 'target_value'

                // Access the value correctly
                $targetValue = $this->entityTargetsInput[$entityType][$entityId][$fieldName] ?? null;

                // Mark for processing. We're only processing the one changed item.
                $targetsToSave[$entityType][$entityId] = ['target_value' => $targetValue];
            } else {
                // This branch would only be hit if savePeriodTargetsLogic was called without a $changedKey,
                // implying a mass save, which isn't the autosave use case.
                // We will assume that if changedKey is null, we iterate the whole array (e.g. initial load)
                $targetsToSave = $this->entityTargetsInput;
            }

            foreach ($targetsToSave as $entityType => $targetsDataForType) {
                $modelClass = $this->getModelClassFromType($entityType);
                if (!$modelClass) {
                    continue;
                }

                foreach ($targetsDataForType as $entityId => $nestedData) {
                    // Extract the actual numeric value from the nested array
                    $targetValue = $nestedData['target_value'] ?? null;

                    if (is_numeric($targetValue)) {
                        CvoPeriodTarget::updateOrCreate(
                            [
                                'cvo_accomplishment_id' => $this->accomplishmentId,
                                'targetable_type' => $modelClass,
                                'targetable_id' => $entityId,
                            ],
                            [
                                'target_value' => (float) $targetValue,
                            ]
                        );
                    } else {
                        // If the input is empty or non-numeric, delete the existing record.
                        CvoPeriodTarget::where('cvo_accomplishment_id', $this->accomplishmentId)
                            ->where('targetable_type', $modelClass)
                            ->where('targetable_id', $entityId)
                            ->delete();
                    }
                }
            }

            $this->dispatch('success', message: 'Targets successfully saved.');
        });
    }

    public function savePeriodMonthlyInputsLogic($changedKey = null)
    {
        try {
            if (!$this->accomplishmentId) {
                throw new \Exception('No accomplishment period selected to save targets.');
            }

            $monthly_accomplishment_to_save = $this->entityMonthlyInput;
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    private function getModelClassFromType(string $type): ?string
    {
        return match ($type) {
            'category' => RefAccomplishmentCategory::class,
            'sub_category' => RefAccomplishmentSubcategory::class,
            'species' => RefSpecies::class,
            default => null,
        };
    }

    private function getTypeFromModelClass($type)
    {
        return match ($type) {
            RefAccomplishmentCategory::class => 'category',
            RefAccomplishmentSubcategory::class => 'sub_category',
            RefSpecies::class => 'species',
            default => null,
        };
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
