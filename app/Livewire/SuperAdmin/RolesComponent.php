<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class RolesComponent extends Component
{
    use WithPagination;

    public $roleId,
        $role;
    public $editMode;
    # Properties
    public $name,
        $team_id,
        $permissions = [];

    public function rules()
    {
        return [
            'name' => 'required|string',
            'team_id' => 'required',
            'permissions' => 'required|array',
        ];
    }

    public function attributes()
    {
        return [
            'team_id' => 'team',
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
            'livewire.super-admin.roles-component',
            [
                'roles' => $this->loadRoles(),
                'teams' => $this->loadTeams(), // Load teams for the dropdown
            ]
        );
    }

    public function loadRoles()
    {
        return Role::whereNot('name', 'Super Admin')
            ->paginate(10);
    }

    public function loadTeams()
    {
        return Team::all();
    }

    public function createRole()
    {
        $this->validate($this->rules(), [], $this->attributes());

        try {
            DB::transaction(function () {
                $role = new Role();
                $role->name = $this->name;
                $role->team_id = $this->team_id;
                $role->save();

                $this->clear();
                $this->dispatch('hide-roles-modal');
                $this->dispatch('success', message: 'Role created successfully.');
            });
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editRole($roleId)
    {
        try {
            $role = Role::findOrFail($roleId);
            $this->name = $role->name;
            $this->team_id = $role->team_id;
            $this->permissions = $role->getPermissionNames()->toArray();

            $this->roleId = $roleId;

            $this->editMode = true;

            $this->dispatch('show-roles-modal');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function updateRole()
    {
        $this->validate($this->rules(), [], $this->attributes());

        try {
            DB::transaction(function () {
                $role = Role::find($this->roleId);
                $role->name = $this->name;
                $role->team_id = $this->team_id;
                $role->syncPermissions($this->permissions);
                $role->save();

                $this->clear();
                $this->dispatch('hide-roles-modal');
                $this->dispatch('success', message: 'Role updated successfully.');
            });
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
