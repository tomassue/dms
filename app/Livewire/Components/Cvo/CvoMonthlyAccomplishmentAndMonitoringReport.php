<?php

namespace App\Livewire\Components\Cvo;

use App\Models\CvoAccomplishment;
use App\Models\CvoMonthlyAccomplishment;
use App\Models\CvoPeriodTarget;
use App\Models\RefAccomplishmentCategory;
use App\Models\RefAccomplishmentSubcategory;
use App\Models\RefSpecies;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;

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
    public $entityRemarksInputs = [];
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

    public function getAccomplishmentToDateTotalsProperty()
    {
        $totals = [];

        $target = $this->accomplishment->target; // Example: '2025-H1'
        [$year, $half] = explode('-', $target);
        $months = match ($half) {
            'H1' => range(1, 6),
            'H2' => range(7, 12),
            default => [],
        };

        // Fetch all monthly accomplishments within relevant months
        $accomplishments = CvoMonthlyAccomplishment::where('cvo_accomplishment_id', $this->accomplishmentId)
            ->whereIn('month', $months)
            ->get();

        foreach ($accomplishments as $accomplishment) {
            $type = $this->getTypeFromModelClass($accomplishment->accomplishable_type);
            $totals[$type][$accomplishment->accomplishable_id] ??= 0;
            $totals[$type][$accomplishment->accomplishable_id] += (int) $accomplishment->accomplished_value;
        }

        return $totals;
    }

    public function getAccomplishmentToDatePercentagesProperty()
    {
        $percentages = [];

        // Get all targets for the same accomplishment and half
        $targets = CvoPeriodTarget::where('cvo_accomplishment_id', $this->accomplishmentId)
            ->get();

        // Get totals
        $totals = $this->accomplishmentToDateTotals;

        foreach ($targets as $targetRecord) {
            $type = $this->getTypeFromModelClass($targetRecord->targetable_type);
            $id = $targetRecord->targetable_id;

            $totalAccomplished = $totals[$type][$id] ?? 0;
            $targetValue = (int) $targetRecord->target_value;

            // Avoid division by zero
            $percent = $targetValue > 0 ? ($totalAccomplished / $targetValue) * 100 : 0;

            $percentages[$type][$id] = round($percent, 2); // Optional: round to 2 decimal places
        }

        return $percentages;
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

        $accomplishments = CvoMonthlyAccomplishment::where('cvo_accomplishment_id', $this->accomplishmentId)
            ->where('user_id', auth()->user()->id) // ✅ Filter by user
            ->orderBy('month', 'asc')
            ->get();
        $this->entityMonthlyInputs = []; // Reset to ensure fresh load
        foreach ($accomplishments as $accomplishment) {
            $type = $this->getTypeFromModelClass($accomplishment->accomplishable_type);
            $this->entityMonthlyInputs[$type] ??= []; // Ensure the type array exists
            $this->entityMonthlyInputs[$type][$accomplishment->accomplishable_id][$accomplishment->month] = [
                'accomplished_value' => $accomplishment->accomplished_value,
                // 'remarks_value' => $accomplishment->remarks,
            ];
            $this->entityRemarksInputs[$type][$accomplishment->accomplishable_id][$accomplishment->month] = [
                'remarks_value' => $accomplishment->remarks
            ];
            $this->selectedAccomplishmentMonth = $accomplishment->month;
        }
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
            ->where('user_id', auth()->user()->id) // ✅ Filter by user
            ->get();

        foreach ($records as $accomplishment) {
            $type = $this->getTypeFromModelClass($accomplishment->accomplishable_type);
            $this->entityMonthlyInputs[$type][$accomplishment->accomplishable_id][$month] = [
                'accomplished_value' => $accomplishment->accomplished_value,
                // 'remarks_value' => $accomplishment->remarks,
            ];
            $this->entityRemarksInputs[$type][$accomplishment->accomplishable_id][$month] = [
                'remarks_value' => $accomplishment->remarks
            ];
        }
    }

    // 📝 This is for users like the admin, where they can't update monthly accomplishment values and remarks but only view all user inputs like the technicians.
    //* Not used
    // public function getMonthlyAccomplishmentList($entityType, $entityId, $month)
    // {
    //     $modelClass = $this->getModelClassFromType($entityType);

    //     if (!$modelClass) {
    //         return collect();
    //     }

    //     return CvoMonthlyAccomplishment::where('cvo_accomplishment_id', $this->accomplishmentId)
    //         ->where('accomplishable_type', $modelClass)
    //         ->where('accomplishable_id', $entityId)
    //         ->where('month', $month)
    //         ->with('user') // assuming you have a relation in the model
    //         ->get()
    //         ->map(function ($record) {
    //             return [
    //                 'user' => $record->user->name ?? 'Unknown',
    //                 'accomplished_value' => $record->accomplished_value,
    //                 'remarks' => $record->remarks_value,
    //             ];
    //         });
    // }

    // 📝 This is for users like the admin, where they can't update monthly accomplishment values and remarks but only view all user inputs like the technicians.
    public function getTotalMonthlyAccomplishmentValues($entityType, $entityId, $month)
    {
        $modelClass = $this->getModelClassFromType($entityType);

        if (!$modelClass) {
            return collect();
        }

        return CvoMonthlyAccomplishment::where('cvo_accomplishment_id', $this->accomplishmentId)
            ->where('accomplishable_type', $modelClass)
            ->where('accomplishable_id', $entityId)
            ->where('month', $month)
            ->sum('accomplished_value');
    }

    // 📝 This is for users like the admin, where they can't update monthly accomplishment values and remarks but only view all user inputs like the technicians.
    public function getMonthlyAccomplishmentRemarksList($entityType, $entityId, $month)
    {
        $modelClass = $this->getModelClassFromType($entityType);

        if (!$modelClass) {
            return collect();
        }

        return CvoMonthlyAccomplishment::where('cvo_accomplishment_id', $this->accomplishmentId)
            ->where('accomplishable_type', $modelClass)
            ->where('accomplishable_id', $entityId)
            ->where('month', $month)
            ->with('user') // assuming you have a relation in the model
            ->get()
            ->map(function ($record) {
                return [
                    // 'user' => $record->user->name ?? 'Unknown',
                    'remarks' => $record->remarks,
                ];
            });
    }

    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'entityTargetsInput')) {
            if (auth()->user()->can('monthly-reporting.input-target-period')) {
                $this->validateOnly($propertyName);
                $key = str_replace('entityTargetsInput.', '', $propertyName);
                $this->dispatch('save-target', ['key' => $key]); // Delay saving until after DOM update
            } else {
                $this->loadAccomplishmentData();
                $this->dispatch('error', message: 'You do not have permission to save targets.');
            }
        }

        if (str_starts_with($propertyName, 'entityMonthlyInputs')) {
            if (auth()->user()->can('monthly-reporting.input-accomplishment-by-month')) {
                $this->validateOnly('selectedAccomplishmentMonth');
                $this->validateOnly($propertyName);
                $key = str_replace('entityMonthlyInputs.', '', $propertyName);
                $this->dispatch('save-monthly-accomplishment', ['key' => $key]); // Delay saving until after DOM update;
            } else {
                $this->loadAccomplishmentData();
                $this->dispatch('error', message: 'You do not have permission to save accomplishments.');
            }
        }

        if (str_starts_with($propertyName, 'entityRemarksInputs')) {
            if (auth()->user()->can('monthly-reporting.input-accomplishment-by-month')) {
                $this->validateOnly('selectedAccomplishmentMonth');
                $this->validateOnly($propertyName);
                $key = str_replace('entityRemarksInputs.', '', $propertyName);
                $this->dispatch('save-remarks', ['key' => $key]); // Delay saving until after DOM update;
            } else {
                $this->loadAccomplishmentData();
                $this->dispatch('error', message: 'You do not have permission to save accomplishments.');
            }
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
                // Single field changed
                $parts = explode('.', $changedKey, 3);
                $entityType = $parts[0];
                $entityId = $parts[1];
                $fieldName = $parts[2] ?? null;

                $targetValue = $this->entityTargetsInput[$entityType][$entityId][$fieldName] ?? null;
                $targetsToSave[$entityType][$entityId] = ['target_value' => $targetValue];
            } else {
                // Mass save → include only changed or new records
                foreach ($this->entityTargetsInput as $entityType => $targetsDataForType) {
                    foreach ($targetsDataForType as $entityId => $nestedData) {
                        $existingRecord = CvoPeriodTarget::where('cvo_accomplishment_id', $this->accomplishmentId)
                            ->where('targetable_type', $this->getModelClassFromType($entityType))
                            ->where('targetable_id', $entityId)
                            ->first();

                        $newValue = $nestedData['target_value'] ?? null;

                        if (
                            (is_numeric($newValue) && !$existingRecord) ||
                            ($existingRecord && $existingRecord->target_value != $newValue)
                        ) {
                            $targetsToSave[$entityType][$entityId] = ['target_value' => $newValue];
                        }
                    }
                }
            }

            foreach ($targetsToSave as $entityType => $targetsDataForType) {
                $modelClass = $this->getModelClassFromType($entityType);

                if (!$modelClass) {
                    $this->dispatch('error', message: "Invalid entity type: $entityType");
                    continue;
                }

                foreach ($targetsDataForType as $entityId => $nestedData) {
                    $targetValue = $nestedData['target_value'] ?? null;

                    $hasExistingRecord = CvoPeriodTarget::where('cvo_accomplishment_id', $this->accomplishmentId)
                        ->where('targetable_type', $modelClass)
                        ->where('targetable_id', $entityId)
                        ->exists();

                    if (!is_numeric($targetValue) && !$hasExistingRecord) {
                        continue;
                    }

                    $details = $this->getEntityDetails($entityType, $entityId);
                    $formattedType = $details['formattedType'];
                    $entityName = $details['entityName'];

                    if (is_numeric($targetValue)) {
                        CvoPeriodTarget::updateOrCreate(
                            [
                                'cvo_accomplishment_id' => $this->accomplishmentId,
                                'targetable_type' => $modelClass,
                                'targetable_id' => $entityId,
                            ],
                            [
                                'target_value' => (float) $targetValue,
                                'office_id' => auth()->user()->roles()->first()->id,
                                'ref_division_id' => auth()->user()->user_metadata?->ref_division_id ?? 0,
                                'user_id' => auth()->user()->id
                            ]
                        );

                        activity()
                            ->causedBy(auth()->user())
                            ->performedOn($this->accomplishment)
                            ->useLog('cvo_period_target')
                            ->event('saved')
                            ->tap(fn($activity) => $activity->log_name = 'cvo_period_target')
                            ->log(
                                auth()->user()->name . ' saved a target for a ' . $formattedType .
                                    ' of ' . $entityName . ' with a value of ' . $targetValue
                            );
                    } else {
                        CvoPeriodTarget::where('cvo_accomplishment_id', $this->accomplishmentId)
                            ->where('targetable_type', $modelClass)
                            ->where('targetable_id', $entityId)
                            ->delete();

                        activity()
                            ->causedBy(auth()->user())
                            ->performedOn($this->accomplishment)
                            ->useLog('cvo_period_target')
                            ->event('delete')
                            ->tap(fn($activity) => $activity->log_name = 'cvo_period_target')
                            ->log(
                                auth()->user()->name . ' removed a target value for a ' .
                                    $formattedType . ' of ' . $entityName
                            );
                    }
                }
            }

            $this->dispatch('success', message: 'Targets successfully saved.');
        });
    }

    #[On('triggerSaveMonthlyAccomplishment')]
    public function savePeriodMonthlyInputsLogic($changedKey = null)
    {
        if (!$this->accomplishmentId) {
            throw new \Exception('No accomplishment period selected to save monthly inputs.');
        }

        DB::transaction(function () use ($changedKey) {
            $inputsToSave = [];

            // 🔑 CASE 1: Single changed field (autosave)
            if ($changedKey && str_contains($changedKey, '.')) {
                $parts = explode('.', $changedKey);
                $entityType = $parts[1];
                $entityId = $parts[2];
                $month = $parts[3];
                $fieldName = $parts[4];

                $value = $this->entityMonthlyInputs[$entityType][$entityId][$month][$fieldName] ?? null;
                $inputsToSave[$entityType][$entityId][$month][$fieldName] = $value;
            } else {
                // 🔑 CASE 2: Mass save → only collect changed/new rows
                foreach ($this->entityMonthlyInputs as $entityType => $entities) {
                    foreach ($entities as $entityId => $months) {
                        foreach ($months as $month => $fields) {
                            $existingRecord = CvoMonthlyAccomplishment::where([
                                'cvo_accomplishment_id' => $this->accomplishmentId,
                                'accomplishable_type' => $this->getModelClassFromType($entityType),
                                'accomplishable_id' => $entityId,
                                'month' => $month,
                                'user_id' => auth()->id(), // per user
                            ])->first();

                            $newAccomplished = trim($fields['accomplished_value'] ?? '');
                            $newRemarks = trim($fields['remarks_value'] ?? '');

                            if (
                                (!$existingRecord && ($newAccomplished !== '' || $newRemarks !== '')) ||
                                ($existingRecord && (
                                    $existingRecord->accomplished_value != $newAccomplished ||
                                    $existingRecord->remarks_value != $newRemarks
                                ))
                            ) {
                                $inputsToSave[$entityType][$entityId][$month] = $fields;
                            }
                        }
                    }
                }
            }

            $selectedMonth = $this->selectedAccomplishmentMonth;
            $user = auth()->user();
            $officeId = $user->roles()->first()->id;
            $refDivisionId = $user->user_metadata?->ref_division_id ?? 0;
            $userId = $user->id;

            foreach ($inputsToSave as $entityType => $entities) {
                $modelClass = $this->getModelClassFromType($entityType);

                if (!$modelClass) {
                    $this->dispatch('error', message: "Invalid entity type: $entityType");
                    continue;
                }

                foreach ($entities as $entityId => $months) {
                    if (!isset($months[$selectedMonth])) continue;

                    $values = $months[$selectedMonth];
                    $accomplishedValue = trim($values['accomplished_value'] ?? '');
                    $remarksValue = trim($values['remarks_value'] ?? '');

                    $queryConditions = [
                        'cvo_accomplishment_id' => $this->accomplishmentId,
                        'accomplishable_type' => $modelClass,
                        'accomplishable_id' => $entityId,
                        'month' => $selectedMonth,
                        'user_id' => $userId,
                    ];

                    $existingRecord = CvoMonthlyAccomplishment::where($queryConditions)->first();

                    // Get entity details for activity logs
                    $details = $this->getEntityDetails($entityType, $entityId);
                    $formattedType = $details['formattedType'];
                    $entityName = $details['entityName'];

                    if ($accomplishedValue === '' && $remarksValue === '') {
                        // ✅ Delete record (only if it exists)
                        if ($existingRecord) {
                            $existingRecord->delete();

                            $monthName = Carbon::create()->month($selectedMonth)->format('M');

                            activity()
                                ->causedBy($user)
                                ->performedOn($this->accomplishment)
                                ->useLog('cvo_monthly_accomplishment')
                                ->event('delete')
                                ->tap(fn(Activity $activity) => $activity->log_name = 'cvo_monthly_accomplishment')
                                ->log("{$user->name} removed monthly accomplishment for {$formattedType} '{$entityName}' in month {$monthName}");
                        }
                    } else {
                        $updateData = [
                            'accomplished_value' => is_numeric($accomplishedValue) ? (float) $accomplishedValue : null,
                            'remarks_value' => $remarksValue !== '' ? $remarksValue : null,
                        ];

                        if (
                            !$existingRecord ||
                            $existingRecord->office_id != $officeId ||
                            $existingRecord->ref_division_id != $refDivisionId
                        ) {
                            $updateData['office_id'] = $officeId;
                            $updateData['ref_division_id'] = $refDivisionId;
                        }

                        // ✅ Create/update only when changed or new
                        CvoMonthlyAccomplishment::updateOrCreate($queryConditions, $updateData);

                        $monthName = Carbon::create()->month((int) $selectedMonth)->format('M');

                        activity()
                            ->causedBy($user)
                            ->performedOn($this->accomplishment)
                            ->useLog('cvo_monthly_accomplishment')
                            ->event('saved')
                            ->tap(fn(Activity $activity) => $activity->log_name = 'cvo_monthly_accomplishment')
                            ->log("{$user->name} saved monthly accomplishment for {$formattedType} '{$entityName}' with value {$accomplishedValue} and remarks '{$remarksValue}' for month {$monthName}");
                    }
                }
            }

            $this->dispatch('success', message: 'Monthly accomplishments saved successfully.');
        });
    }

    #[On('triggerSaveRemarks')]
    public function saveRemarksLogic($changedKey = null)
    {
        if (!$this->accomplishmentId) {
            throw new \Exception('No accomplishment period selected to save remarks.');
        }

        DB::transaction(function () use ($changedKey) {
            $inputsToSave = [];

            // 🔑 CASE 1: Single field autosave
            if ($changedKey && str_contains($changedKey, '.')) {
                $parts = explode('.', $changedKey);
                $entityType = $parts[1];
                $entityId = $parts[2];
                $month = $parts[3];
                $fieldName = $parts[4];

                $value = $this->entityRemarksInputs[$entityType][$entityId][$month][$fieldName] ?? null;
                $inputsToSave[$entityType][$entityId][$month][$fieldName] = $value;
            } else {
                // 🔑 CASE 2: Mass save → only include changed/new remarks
                foreach ($this->entityRemarksInputs as $entityType => $entities) {
                    foreach ($entities as $entityId => $months) {
                        foreach ($months as $month => $fields) {
                            $existingRecord = CvoMonthlyAccomplishment::where([
                                'cvo_accomplishment_id' => $this->accomplishmentId,
                                'accomplishable_type' => $this->getModelClassFromType($entityType),
                                'accomplishable_id' => $entityId,
                                'month' => $month,
                                'user_id' => auth()->id(),
                            ])->first();

                            $newRemarks = trim($fields['remarks_value'] ?? '');

                            if (
                                (!$existingRecord && $newRemarks !== '') ||
                                ($existingRecord && $existingRecord->remarks_value != $newRemarks)
                            ) {
                                $inputsToSave[$entityType][$entityId][$month]['remarks_value'] = $newRemarks;
                            }
                        }
                    }
                }
            }

            $selectedMonth = $this->selectedAccomplishmentMonth;
            $user = auth()->user();
            $officeId = $user->roles()->first()->id;
            $refDivisionId = $user->user_metadata?->ref_division_id ?? 0;
            $userId = $user->id;

            foreach ($inputsToSave as $entityType => $entities) {
                $modelClass = $this->getModelClassFromType($entityType);

                if (!$modelClass) {
                    $this->dispatch('error', message: "Invalid entity type: $entityType");
                    continue;
                }

                foreach ($entities as $entityId => $months) {
                    if (!isset($months[$selectedMonth])) continue;

                    $remarksValue = trim($months[$selectedMonth]['remarks_value'] ?? '');

                    $queryConditions = [
                        'cvo_accomplishment_id' => $this->accomplishmentId,
                        'accomplishable_type' => $modelClass,
                        'accomplishable_id' => $entityId,
                        'month' => $selectedMonth,
                        'user_id' => $userId,
                    ];

                    $existingRecord = CvoMonthlyAccomplishment::where($queryConditions)->first();

                    // Entity details for logs
                    $details = $this->getEntityDetails($entityType, $entityId);
                    $formattedType = $details['formattedType'];
                    $entityName = $details['entityName'];

                    if ($remarksValue === '') {
                        // ✅ Delete only if record exists
                        if ($existingRecord) {
                            $existingRecord->delete();

                            $monthName = Carbon::create()->month($selectedMonth)->format('M');

                            activity()
                                ->causedBy($user)
                                ->performedOn($this->accomplishment)
                                ->useLog('cvo_monthly_accomplishment')
                                ->event('delete')
                                ->tap(fn(Activity $activity) => $activity->log_name = 'cvo_monthly_accomplishment')
                                ->log("{$user->name} removed remarks for {$formattedType} '{$entityName}' in month {$monthName}");
                        }
                    } else {
                        $updateData = [
                            'remarks' => $remarksValue,
                        ];

                        if (
                            !$existingRecord ||
                            $existingRecord->office_id != $officeId ||
                            $existingRecord->ref_division_id != $refDivisionId
                        ) {
                            $updateData['office_id'] = $officeId;
                            $updateData['ref_division_id'] = $refDivisionId;
                            $updateData['user_id'] = $userId;
                        }

                        CvoMonthlyAccomplishment::updateOrCreate($queryConditions, $updateData);

                        $monthName = Carbon::create()->month((int) $selectedMonth)->format('M');

                        activity()
                            ->causedBy($user)
                            ->performedOn($this->accomplishment)
                            ->useLog('cvo_monthly_accomplishment')
                            ->event('saved')
                            ->tap(fn(Activity $activity) => $activity->log_name = 'cvo_monthly_accomplishment')
                            ->log("{$user->name} saved remarks for {$formattedType} '{$entityName}' with '{$remarksValue}' in month {$monthName}");
                    }
                }
            }

            $this->dispatch('success', message: 'Remarks saved successfully.');
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

    private function getEntityDetails(string $entityType, int $entityId): array
    {
        return match ($entityType) {
            'category' => [
                'formattedType' => 'category',
                'entityName' => RefAccomplishmentCategory::find($entityId)?->accomplishment_category_name ?? ''
            ],
            'subCategory' => [
                'formattedType' => 'sub category',
                'entityName' => RefAccomplishmentSubcategory::find($entityId)?->accomplishment_sub_category_name ?? ''
            ],
            'species' => [
                'formattedType' => 'species',
                'entityName' => RefSpecies::find($entityId)?->species_name ?? ''
            ],
            default => [
                'formattedType' => $entityType,
                'entityName' => ''
            ]
        };
    }

    public function generateMonthlyAccomplishmentAndMonitoringReportPdf()
    {
        try {
            //...
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
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
