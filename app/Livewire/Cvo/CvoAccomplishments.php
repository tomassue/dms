<?php

namespace App\Livewire\Cvo;

use App\Models\CvoAccomplishment;
use App\Models\RefAccomplishmentCategory;
use App\Models\RefAccomplishmentSubcategory;
use Livewire\Attributes\On;
use Livewire\Component;

class CvoAccomplishments extends Component
{
    public $showMonthlyAccomplishmentAndMonitoringReport = false;
    public $editMode;
    public $search;
    public $accomplishmentId;
    public $target, $office_id, $ref_division_id;

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
    //TODO: Continue
    public function editAccomplishment(CvoAccomplishment $accomplishment)
    {
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
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
