<?php

namespace App\Livewire\Shared;

use App\Models\Accomplishment;
use App\Models\RefAccomplishmentCategory;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Accomplishments extends Component
{
    use WithPagination;

    public $editMode;
    public $search;
    public $accomplishmentId;
    public $ref_accomplishment_category_id,
        $date,
        $details;

    //* APO
    public $start_date,
        $end_date,
        $next_steps;

    public function rules()
    {
        if (auth()->user()->hasRole('APO')) {
            $rules['start_date'] = 'required|date|before_or_equal:end_date';
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
            $rules['next_steps'] = 'required';
        } else {
            $rules = [
                'ref_accomplishment_category_id' => 'required|exists:ref_accomplishment_categories,id',
                'date' => 'required|date',
                'details' => 'required',
            ];
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'ref_accomplishment_category_id' => 'accomplishment category',
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
            'livewire.shared.accomplishments',
            [
                'accomplishments' => $this->loadAccomplishments(),
                'accomplishment_categories' => $this->loadAccomplishmentCategories() // Accomplishment Category dropdown
            ]
        );
    }

    public function loadAccomplishments()
    {
        return Accomplishment::query()
            ->with(['accomplishment_category', 'apo'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function loadAccomplishmentCategories()
    {
        return RefAccomplishmentCategory::query()
            ->when(auth()->user()->hasRole('Super Admin'), function ($query) {
                // Super Admin sees all
            }, function ($query) {
                $roleId = auth()->user()->roles()->first()->id; // Explicitly fails if no role
                $query->where('role_id', $roleId);
            })
            ->get();
    }

    public function createAccomplishment()
    {
        $this->validate($this->rules(), [], $this->attributes());

        try {
            DB::transaction(function () {
                $accomplishment = new Accomplishment();
                $accomplishment->ref_accomplishment_category_id = $this->ref_accomplishment_category_id;
                $accomplishment->date = $this->date;
                $accomplishment->details = $this->details;
                $accomplishment->save();

                //* APO
                if (auth()->user()->hasRole('APO')) {
                    $apo_accomplishment = new \App\Models\Apo\Accomplishment();
                    $apo_accomplishment->accomplishment_id = $accomplishment->id;
                    $apo_accomplishment->start_date = $this->start_date;
                    $apo_accomplishment->end_date = $this->end_date;
                    $apo_accomplishment->next_steps = $this->next_steps;
                    $apo_accomplishment->save();
                }

                $this->clear();
                $this->dispatch('hide-accomplishment-modal');
                $this->dispatch('success', message: 'Accomplishment successfully created.');
            });
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
