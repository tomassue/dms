<?php

namespace App\Livewire\SuperAdmin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class RolesComponent extends Component
{
    use WithPagination;

    public $roleId,
        $role;
    public $editMode;

    # Properties
    public $name;

    public function rules()
    {
        return [
            'name' => 'required|string|unique:roles,name,' . $this->roleId,
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
                'roles' => $this->loadRoles()
            ]
        );
    }

    public function loadRoles()
    {
        return Role::whereNot('name', 'Super Admin')
            ->paginate(10);
    }

    public function createRole()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $role = new Role();
                $role->name = $this->name;
                $role->save();

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn(new Role())
                    ->useLog('role')
                    ->event('created')
                    ->withProperties([
                        'name' => $this->name
                    ])
                    ->tap(function (Activity $activity) {
                        $activity->log_name = 'role';
                        $activity->subject_id = Role::latest()->first()->id;
                    })
                    ->log('Role has been created by ' . Auth::user()->name);

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
        $this->validate();

        try {
            DB::transaction(function () {
                $role = Role::find($this->roleId);
                $role->name = $this->name;
                $role->save();

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn(new Role())
                    ->useLog('role')
                    ->event('updated')
                    ->withProperties([
                        'name' => $this->name
                    ])
                    ->tap(function (Activity $activity) {
                        $activity->log_name = 'role';
                        $activity->subject_id = Role::latest()->first()->id;
                    })
                    ->log('Role has been updated by ' . Auth::user()->name);

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
