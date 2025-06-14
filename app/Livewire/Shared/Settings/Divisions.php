<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefDivision;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Title('Divisions')]
class Divisions extends Component
{
    use WithPagination;

    public $search;
    public $editMode;
    public $divisionId;
    public $name,
        $office_id;

    public function rules()
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ref_divisions')->where(function ($query) {
                    return $query->where('office_id', $this->office_id);
                })->ignore($this->divisionId),
            ],
        ];

        if (auth()->user()->hasRole('Super Admin')) {
            $rules['office_id'] = 'required';
        }

        return $rules;
    }

    public function clear()
    {
        $this->reset();
        $this->resetValidation();
    }

    public function render()
    {
        return view(
            'livewire.shared.settings.divisions',
            [
                'divisions' => $this->loadDivisions(),
                'roles' => $this->loadRoles(), # Load roles for dropdown
            ]
        );
    }

    public function loadDivisions()
    {
        return RefDivision::query()
            ->with('roles')
            ->withTrashed()
            ->when($this->search, function ($query) {
                return $query->where('name', 'LIKE', '%' . $this->search . '%');
            })
            ->paginate(10);
    }

    public function loadRoles()
    {
        return Role::query()
            ->whereNot('name', 'Super Admin')
            ->get();
    }

    public function createDivision()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $division = new RefDivision();
                if (Auth::user()->hasRole('Super Admin')) {
                    $division->office_id = $this->office_id;
                } else {
                    $division->office_id = auth()->user()->roles()->first()->id;
                }
                $division->name = $this->name;
                $division->save();

                $this->clear();
                $this->dispatch('hide-division-modal');
                $this->dispatch('success', message: 'Division created successfully.');
            });
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editDivision($divisionId)
    {
        try {
            $this->editMode = true;
            $this->divisionId = $divisionId;

            $division = RefDivision::find($divisionId);
            $this->name = $division->name;
            $this->office_id = $division->office_id;

            $this->dispatch('show-division-modal');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function updateDivision()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $division = RefDivision::find($this->divisionId);
                if (Auth::user()->hasRole('Super Admin')) {
                    $division->office_id = $this->office_id;
                } else {
                    $division->office_id = auth()->user()->roles()->first()->id;
                }
                $division->name = $this->name;
                $division->save();

                $this->clear();
                $this->dispatch('hide-division-modal');
                $this->dispatch('success', message: 'Division updated successfully.');
            });
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: ' Something went wrong.');
        }
    }

    public function deleteDivision($divisionId)
    {
        try {
            DB::transaction(function () use ($divisionId) {
                $division = RefDivision::find($divisionId);
                $division->delete();

                $this->dispatch('success', message: 'Division deleted successfully.');
            });
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function restoreDivision($divisionId)
    {
        try {
            DB::transaction(function () use ($divisionId) {
                $division = RefDivision::withTrashed()->find($divisionId);
                $division->restore();

                $this->dispatch('success', message: 'Division restored successfully.');
            });
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
