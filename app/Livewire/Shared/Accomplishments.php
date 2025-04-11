<?php

namespace App\Livewire\Shared;

use App\Livewire\Components\GeneratePdfComponent;
use App\Models\Accomplishment;
use App\Models\Apo\Accomplishment as ApoAccomplishment;
use App\Models\RefAccomplishmentCategory;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
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

    //* begin::APO
    public $sub_category,
        $start_date,
        $end_date,
        $next_steps;
    public $report = "accomplishments";
    //* end:: APO

    public function rules()
    {
        $rules = [
            'ref_accomplishment_category_id' => 'required|exists:ref_accomplishment_categories,id',
            'details' => 'required',
            'date' => 'required|date'
        ];

        //* += Merged Rules Properly: Used += to merge the APO rules with the base rules instead of overwriting them.
        if (auth()->user()->hasRole('APO')) {
            $rules += [
                'sub_category' => 'required',
                'start_date' => 'required|date|before_or_equal:end_date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'next_steps' => 'required'
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

        /**
         * getCollection(): Returns a collection of the query results
         * The loadAccomplishments() method returns a collection of Accomplishment objects, which is a collection of Accomplishment models.
         * The getCollection() method returns a collection of the query results, which is a collection of Accomplishment objects.
         * * The getCollection() removes pagination to use foreach loop.
         * 
         * Then we dispatch an event to the GeneratePdfComponent class with the accomplishments collection.
         */
        $accomplishments = $this->loadAccomplishments()->getCollection();
        $this->dispatch('filtered-accomplishments', accomplishments: $accomplishments)->to(GeneratePdfComponent::class);
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

    public function saveAccomplishment()
    {
        $this->validate($this->rules(), [], $this->attributes());

        try {
            DB::transaction(function () {
                $accomplishment = $this->saveMainAccomplishment();
                $this->saveApoAccomplishment($accomplishment);

                $this->clear();
                $this->dispatch('hide-accomplishment-modal');
                $this->dispatch('success', message: 'Accomplishment successfully saved.');
            });
        } catch (\Throwable $th) {
            report($th); // Log the error
            $this->dispatch('error', message: 'Operation failed. Please try again.');
        }
    }

    protected function saveMainAccomplishment()
    {
        $data = [
            'ref_accomplishment_category_id' => $this->ref_accomplishment_category_id,
            'date' => $this->date,
            'details' => $this->details
        ];

        return $this->accomplishmentId
            ? Accomplishment::updateOrCreate(['id' => $this->accomplishmentId], $data)
            : Accomplishment::create($data);
    }

    protected function saveApoAccomplishment($accomplishment)
    {
        if (!auth()->user()->hasRole('APO')) return;

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

    public function editAccomplishment($accomplishmentId)
    {
        try {
            $accomplishment = Accomplishment::with('apo')->findOrFail($accomplishmentId);

            $this->accomplishmentId = $accomplishmentId;
            $this->ref_accomplishment_category_id = $accomplishment->ref_accomplishment_category_id;
            $this->date = optional($accomplishment->date)->format('Y-m-d');
            $this->details = $accomplishment->details;

            if (auth()->user()->hasRole('APO') && $accomplishment->apo) {
                $this->sub_category = $accomplishment->apo->sub_category;
                $this->start_date = optional($accomplishment->apo->start_date)->format('Y-m-d');
                $this->end_date = optional($accomplishment->apo->end_date)->format('Y-m-d');
                $this->next_steps = $accomplishment->apo->next_steps;
            }

            $this->editMode = true;
            $this->dispatch('show-accomplishment-modal');
        } catch (\Throwable $th) {
            report($th);
            $this->dispatch('error', message: 'Failed to load accomplishment data.');
        }
    }
}
