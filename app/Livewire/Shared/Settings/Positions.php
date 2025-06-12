<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefPosition;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Title('Positions')]
class Positions extends Component
{
    public $editMode;
    public $positionId;
    public $position_name, $office_id;

    public function rules()
    {
        $rules = [
            'position_name' => 'required|unique:ref_positions,position_name,' . $this->positionId,
        ];

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
            'livewire.shared.settings.positions',
            [
                'positions' => $this->getPositions(),
                'roles' => $this->getRoles(), // Dropdown
            ]
        );
    }

    public function getPositions()
    {
        $positions = RefPosition::withTrashed();

        if (Auth::user()->hasRole('Super Admin')) {
            $positions = $positions->office(); // apply the scope or relationship
        }

        $positions = $positions->paginate(10);

        return $positions;
    }

    /**
     * a.k.a Offices
     * @return \Illuminate\Database\Eloquent\Collection<int, RefPosition>
     */
    public function getRoles()
    {
        $roles = Role::whereNot('name', 'Super Admin')
            ->get();

        return $roles;
    }

    public function savePosition()
    {
        $this->validate();

        try {
            $data = [
                'position_name' => $this->position_name
            ];

            if (Auth::user()->hasRole('Super Admin')) {
                $data['office_id'] = $this->office_id;
            } else {
                $data['office_id'] = Auth::user()->roles->first()->id;
            }

            RefPosition::updateOrCreate(
                ['id' => $this->positionId],
                $data
            );

            $this->clear();
            $this->dispatch('hide-position-modal');
            $this->dispatch('success', message: 'Position saved successfully.');
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editPosition(RefPosition $position)
    {
        try {
            $this->editMode = true;
            $this->positionId = $position->id;
            $this->position_name = $position->position_name;
            $this->office_id = $position->office_id;

            $this->dispatch('show-position-modal');
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function deletePosition(RefPosition $position)
    {
        try {
            $position->delete();

            $this->dispatch('success', message: 'Position deleted successfully.');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function restorePosition($id)
    {
        try {
            $position = RefPosition::withTrashed()->find($id);
            $position->restore();

            $this->dispatch('success', message: 'Position restored successfully.');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
