<?php

namespace App\Livewire\Cvo;

use App\Models\CvoAccomplishment;
use App\Models\RefAccomplishmentCategory;
use App\Models\RefAccomplishmentSubcategory;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class CvoAccomplishments extends Component
{
    public $showMonthlyAccomplishmentAndMonitoringReport = false;
    public $editMode;
    public $search;
    public $accomplishmentId;
    public $target, $office_id, $ref_division_id;
    public $activity_log = [];

    public function rules()
    {
        $rules = [
            'target' => 'required|unique:cvo_accomplishments,target,' . $this->accomplishmentId,
        ];

        return $rules;
    }

    public function saveAccomplishment()
    {
        $this->validate();

        try {
            // Assuming you get the value from a request or Livewire property
            $selectedHalfYear = $this->target; // Or $this->selected_half_year;

            $data = [
                'target' => $selectedHalfYear,
                'office_id' => auth()->user()->roles()->first()->id,
                'ref_division_id' => auth()->user()->user_metadata->ref_division_id
            ];

            CvoAccomplishment::updateOrCreate(
                ['id' => $this->accomplishmentId],
                $data
            );

            $this->clear();
            $this->dispatch('hide-accomplishment-modal');
            $this->dispatch('success', message: 'Accomplishment successfully saved.');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editAccomplishment(CvoAccomplishment $accomplishment)
    {
        try {
            $this->editMode = true;
            $this->accomplishmentId = $accomplishment->id;

            $this->target = $accomplishment->target;

            $this->dispatch('show-accomplishment-modal');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function activityLog($id)
    {
        try {
            $this->activity_log = Activity::where(function ($query) use ($id) {
                $query->where('subject_type', CvoAccomplishment::class)
                    ->where('subject_id', $id);
            })
                ->with(['causer.user_metadata.division'])
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'causer' => $activity->causer?->name ?? 'System',
                        'division' => $activity->causer?->user_metadata?->division?->name ? '[' . $activity->causer?->user_metadata?->division?->name . ']' : '',
                        'created_at' => Carbon::parse($activity->created_at)->format('M d, Y h:i A'),
                        'changes' => collect($activity->properties['attributes'] ?? [])
                            ->except(['id', 'office_id', 'ref_division_id', 'created_at', 'updated_at', 'deleted_at'])
                            ->map(function ($newValue, $key) use ($activity) {
                                $oldValue = $activity->properties['old'][$key] ?? 'N/A';

                                $fieldName = match ($key) {
                                    'target' => 'Target',
                                    default => ucfirst(str_replace('_', ' ', $key))
                                };

                                if ($key === 'target') {
                                    $oldValue = $activity->properties['old']['target'] ?? 'N/A';
                                    $newValue = $activity->properties['attributes']['target'] ?? 'N/A';
                                }

                                return [
                                    'field' => $fieldName,
                                    'old' => $oldValue,
                                    'new' => $newValue,
                                ];
                            })
                            ->values()
                            ->toArray()
                    ];
                });

            $this->dispatch('show-activity-log-modal');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function clear()
    {
        $this->reset();
        $this->resetValidation();
    }

    public function getAccomplishments()
    {
        $accomplishments = CvoAccomplishment::all();

        return $accomplishments;
    }

    public function render()
    {
        return view(
            'livewire.cvo.cvo-accomplishments',
            [
                'accomplishments' => $this->getAccomplishments()
            ]
        );
    }

    public function viewMonthlyAccomplishmentAndMonitoringReport($id)
    {
        try {
            $this->showMonthlyAccomplishmentAndMonitoringReport = true;
            $this->accomplishmentId = $id;
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    #[On('hideMonthlyAccomplishmentAndMonitoringReport')]
    public function hideMonthlyAccomplishmentAndMonitoringReport()
    {
        $this->showMonthlyAccomplishmentAndMonitoringReport = false;
    }
}
