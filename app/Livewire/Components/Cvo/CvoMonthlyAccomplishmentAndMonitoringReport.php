<?php

namespace App\Livewire\Components\Cvo;

use App\Models\CvoAccomplishment;
use App\Models\CvoMonthlyAccomplishment;
use App\Models\CvoPeriodTarget;
use App\Models\RefAccomplishmentCategory;
use App\Models\RefAccomplishmentSubcategory;
use App\Models\RefSpecies;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use Livewire\Livewire;

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

    public $totalMonthlyPeriodAccomplishments = [];
    public $entityTargetsInput = [];
    public $selectedAccomplishmentMonth;
    public $entityMonthlyInputs = [];
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
            'entityMonthlyInputs.*.*.*.remarks_value' => 'nullable|string|max:500', // For monthly remarks: entityMonthlyInputs.type.id.month.remark. Added max:500 from previous suggestions
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
            'entityMonthlyInputs.*.*.*.remarks_value.string' => 'The remarks must be text.',
            'entityMonthlyInputs.*.*.*.remarks_value.max' => 'The remarks cannot be more than 500 characters.',
        ];
    }

    public function mount($accomplishmentId)
    {
        $this->accomplishmentId = $accomplishmentId;
        $this->accomplishment = CvoAccomplishment::find($accomplishmentId);
        $this->loadAccomplishmentData();
    }

    #TODO: acomplismentToDateTotals refreshes as entityMonthlyInputs refreshes
    public function getAccomplishmentToDateTotalsProperty()
    {
        $totals = [];

        // Loop through entityMonthlyInputs which already contains data
        foreach ($this->entityMonthlyInputs as $type => $entities) {
            foreach ($entities as $entityId => $monthlyData) {
                $sum = 0;

                foreach ($monthlyData as $month => $values) {
                    if (
                        ($this->accomplishment->target === '2025-H1' && in_array($month, ['1', '2', '3', '4', '5', '6'])) ||
                        ($this->accomplishment->target === '2025-H2' && in_array($month, ['7', '8', '9', '10', '11', '12']))
                    ) {
                        $sum += (int) ($values['accomplished_value'] ?? 0);
                    }
                }

                $totals[$type][$entityId] = $sum;
            }
        }

        return $totals;
    }

    public function loadAccomplishmentData()
    {
        $targets = CvoPeriodTarget::where('cvo_accomplishment_id', $this->accomplishmentId)->get();
        $this->entityTargetsInput = []; // Reset to ensure fresh load
        foreach ($targets as $target) {
            $type = $this->getTypeFromModelClass($target->targetable_type);
            $this->entityTargetsInput[$type] ??= []; // Ensure the type array exists
            $this->entityTargetsInput[$type][$target->targetable_id] = [
                'target_value' => $target->target_value
            ];
        }

        $accomplishments = CvoMonthlyAccomplishment::where('cvo_accomplishment_id', $this->accomplishmentId)->get();
        $this->entityMonthlyInputs = []; // Reset to ensure fresh load
        foreach ($accomplishments as $accomplishment) {
            $type = $this->getTypeFromModelClass($accomplishment->accomplishable_type);
            $this->entityMonthlyInputs[$type] ??= []; // Ensure the type array exists
            $this->entityMonthlyInputs[$type][$accomplishment->accomplishable_id][$accomplishment->month] = [
                'accomplished_value' => $accomplishment->accomplished_value,
                'remarks_value' => $accomplishment->remarks,
            ];
            $this->selectedAccomplishmentMonth = $accomplishment->month;
        }
    }

    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'entityTargetsInput')) {
            $this->validateOnly($propertyName);
            $key = str_replace('entityTargetsInput.', '', $propertyName);
            $this->dispatch('save-target', ['key' => $key]); // Delay saving until after DOM update
        }

        if (str_starts_with($propertyName, 'entityMonthlyInputs')) {
            $this->validateOnly('selectedAccomplishmentMonth');
            $this->validateOnly($propertyName);
            $key = str_replace('entityMonthlyInputs.', '', $propertyName);
            $this->dispatch('save-monthly-accomplishment', ['key' => $key]); // Delay saving until after DOM update;
        }
    }

    public function updatedSelectedAccomplishmentMonth()
    {
        $this->entityMonthlyInputs = [];
        $this->loadMonthlyAccomplishmentsForSelectedMonth();
    }


    #[On('triggerSaveTarget')]
    public function savePeriodTargetsLogic($changedKey = null)
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
                    $this->dispatch('error', message: "Invalid entity type: $entityType");
                    continue;
                }

                foreach ($targetsDataForType as $entityId => $nestedData) {
                    $targetValue = $nestedData['target_value'] ?? null;

                    // Check if a record already exists
                    $hasExistingRecord = CvoPeriodTarget::where('cvo_accomplishment_id', $this->accomplishmentId)
                        ->where('targetable_type', $modelClass)
                        ->where('targetable_id', $entityId)
                        ->exists();

                    // Avoid saving empty values unless there’s something to delete
                    if (!is_numeric($targetValue) && !$hasExistingRecord) {
                        // \Log::debug("⏭️ Skipping: $entityType $entityId is empty and no existing record.");
                        continue;
                    }

                    if (is_numeric($targetValue)) {
                        // \Log::debug("✅ Saving: $entityType $entityId = $targetValue");

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
                        // \Log::debug("🗑️ Deleting: $entityType $entityId (input cleared)");

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

    #[On('triggerSaveMonthlyAccomplishment')]
    public function savePeriodMonthlyInputsLogic($changedKey = null)
    {
        // changedKey is a string like: entityMonthlyInputs.monthly.123.7.accomplished_value

        if (!$this->accomplishmentId) {
            throw new \Exception('No accomplishment period selected to save monthly inputs.');
        }

        DB::transaction(function () use ($changedKey) {
            $inputsToSave = [];

            if ($changedKey && str_contains($changedKey, '.')) {
                // E.g., entityMonthlyInputs.category.123.7.accomplished_value
                $parts = explode('.', $changedKey);
                $entityType = $parts[1]; // monthly
                $entityId = $parts[2];
                $month = $parts[3];
                $fieldName = $parts[4];

                $value = $this->entityMonthlyInputs[$entityType][$entityId][$month][$fieldName] ?? null;

                $inputsToSave[$entityType][$entityId][$month][$fieldName] = $value;
            } else {
                $inputsToSave = $this->entityMonthlyInputs;
            }

            $selectedMonth = $this->selectedAccomplishmentMonth;

            foreach ($inputsToSave as $entityType => $entities) {
                $modelClass = $this->getModelClassFromType($entityType);

                if (!$modelClass) {
                    $this->dispatch('error', message: "Invalid entity type: $entityType");
                    continue;
                }

                foreach ($entities as $entityId => $months) {
                    if (!isset($months[$selectedMonth])) continue;

                    $values = $months[$selectedMonth];

                    $accomplishedValue = $values['accomplished_value'] ?? null;
                    $remarksValue = $values['remarks_value'] ?? null;

                    $queryConditions = [
                        'cvo_accomplishment_id' => $this->accomplishmentId,
                        'accomplishable_type' => $modelClass,
                        'accomplishable_id' => $entityId,
                        'month' => $selectedMonth,
                    ];

                    $accomplishedValue = trim($accomplishedValue ?? '');
                    $remarksValue = trim($remarksValue ?? '');

                    if ($accomplishedValue === '' && $remarksValue === '') {
                        CvoMonthlyAccomplishment::where($queryConditions)->delete();
                    } else {
                        CvoMonthlyAccomplishment::updateOrCreate(
                            $queryConditions,
                            [
                                'accomplished_value' => is_numeric($accomplishedValue) ? (float) $accomplishedValue : null,
                                'remarks' => $remarksValue !== '' ? $remarksValue : null,
                            ]
                        );
                    }
                }
            }

            $this->dispatch('success', message: 'Monthly accomplishments saved successfully.');
        });
    }

    private function getModelClassFromType(string $type): ?string
    {
        return match ($type) {
            'category' => RefAccomplishmentCategory::class,
            'subCategory' => RefAccomplishmentSubcategory::class,
            'species' => RefSpecies::class,
            default => null,
        };
    }

    private function getTypeFromModelClass($type)
    {
        return match ($type) {
            RefAccomplishmentCategory::class => 'category',
            RefAccomplishmentSubcategory::class => 'subCategory',
            RefSpecies::class => 'species',
            default => null,
        };
    }

    public function loadMonthlyAccomplishmentsForSelectedMonth()
    {
        if (!$this->selectedAccomplishmentMonth) {
            return;
        }

        $month = $this->selectedAccomplishmentMonth;
        $this->entityMonthlyInputs = [];

        $records = CvoMonthlyAccomplishment::where('cvo_accomplishment_id', $this->accomplishmentId)
            ->where('month', $month)
            ->get();

        foreach ($records as $accomplishment) {
            $type = $this->getTypeFromModelClass($accomplishment->accomplishable_type);
            $this->entityMonthlyInputs[$type][$accomplishment->accomplishable_id][$month] = [
                'accomplished_value' => $accomplishment->accomplished_value,
                'remarks_value' => $accomplishment->remarks,
            ];
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
