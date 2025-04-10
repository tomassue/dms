<?php

namespace App\Livewire\Shared;

use App\Models\Accomplishment;
use App\Models\Apo\Accomplishment as ApoAccomplishment;
use App\Models\RefAccomplishmentCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Accomplishments extends Component
{
    use WithPagination;

    public $editMode;
    public $search,
        $filter_start_date,
        $filter_end_date;
    public $accomplishmentId;
    public $ref_accomplishment_category_id,
        $date,
        $details;

    //* APO
    public $sub_category,
        $start_date,
        $end_date,
        $next_steps;

    public function rules()
    {
        $rules['details'] = 'required';

        if (auth()->user()->hasRole('APO')) {
            $rules['sub_category'] = 'required';
            $rules['start_date'] = 'required|date|before_or_equal:end_date';
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
            $rules['next_steps'] = 'required';
        } else {
            $rules = [
                'ref_accomplishment_category_id' => 'required|exists:ref_accomplishment_categories,id',
                'date' => 'required|date'
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

    #[On('clear-filter-data')]
    public function clear()
    {
        $this->reset();
        $this->resetValidation();
    }

    //* Listens for an event from MenuFilterComponent (child component)
    #[On('filter')]
    public function filter($start_date, $end_date)
    {
        $this->filter_start_date = $start_date;
        $this->filter_end_date = $end_date;
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
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('details', 'like', '%' . $this->search . '%')
                        ->orWhereHas('apo', function ($q) {
                            $q->where('sub_category', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->filter_start_date && $this->filter_end_date, function ($query) {
                $query->whereHas('apo', function ($q) {
                    $q->where(function ($innerQ) {
                        $innerQ->whereDate('start_date', '>=', $this->filter_start_date)
                            ->whereDate('start_date', '<=', $this->filter_end_date);
                    });
                });
            })
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
                    $apo_accomplishment = new ApoAccomplishment();
                    $apo_accomplishment->accomplishment_id = $accomplishment->id;
                    $apo_accomplishment->sub_category = $this->sub_category;
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

    public function editAccomplishment($accomplishmentId)
    {
        try {
            $accomplishment = Accomplishment::findOrFail($accomplishmentId);
            $this->ref_accomplishment_category_id = $accomplishment->ref_accomplishment_category_id;
            $this->date = Carbon::parse($accomplishment->date)->format('Y-m-d');
            $this->details = $accomplishment->details;

            //* APO
            if (auth()->user()->hasRole('APO')) {
                $apo_accomplishment = ApoAccomplishment::where('accomplishment_id', $accomplishmentId)->first();
                $this->sub_category = $apo_accomplishment->sub_category;
                $this->start_date = Carbon::parse($apo_accomplishment->start_date)->format('Y-m-d');
                $this->end_date = Carbon::parse($apo_accomplishment->end_date)->format('Y-m-d');
                $this->next_steps = $apo_accomplishment->next_steps;
            }

            $this->accomplishmentId = $accomplishmentId;
            $this->editMode = true;
            $this->dispatch('show-accomplishment-modal');
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function updateAccomplishment()
    {
        $this->validate($this->rules(), [], $this->attributes());

        try {
            DB::transaction(function () {
                // Update main accomplishment
                $accomplishment = Accomplishment::updateOrCreate(
                    ['id' => $this->accomplishmentId],
                    [
                        'ref_accomplishment_category_id' => $this->ref_accomplishment_category_id,
                        'date' => $this->date,
                        'details' => $this->details
                    ]
                );

                // Update APO accomplishment if needed
                if (auth()->user()->hasRole('APO')) {
                    ApoAccomplishment::updateOrCreate(
                        ['accomplishment_id' => $accomplishment->id],
                        [
                            'sub_category' => $this->sub_category,
                            'start_date' => $this->start_date,
                            'end_date' => $this->end_date,
                            'next_steps' => $this->next_steps
                        ]
                    );
                }

                $this->clear();
                $this->dispatch('hide-accomplishment-modal');
                $this->dispatch('success', message: 'Accomplishment successfully created.');
            });
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    //TODO: Try the OPTIMIZED CRUD function for multiple CRUD tables.
}
