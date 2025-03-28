<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TeamsComponent extends Component
{
    public $teamId;
    public $editMode;
    # Properties for the form
    public $name;

    public function rules()
    {
        return [
            'name' => 'required|string',
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
            'livewire.super-admin.teams-component',
            [
                'teams' => $this->loadTeams(),
            ]
        );
    }

    public function loadTeams()
    {
        return Team::all();
    }

    public function createTeam()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $team = new Team();
                $team->name = $this->name;
                $team->save();

                $this->clear();
                $this->dispatch('hide-teams-modal');
                $this->dispatch('success', message: 'Team created successfully.');
            });
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editTeam($teamId)
    {
        try {
            $team = Team::find($teamId);
            $this->teamId = $team->id;
            $this->name = $team->name;
            $this->editMode = true;

            $this->dispatch('show-teams-modal');
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function updateTeam()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $team = Team::find($this->teamId);
                $team->name = $this->name;
                $team->save();

                $this->clear();
                $this->dispatch('hide-teams-modal');
                $this->dispatch('success', message: 'Team updated successfully.');
            });
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
