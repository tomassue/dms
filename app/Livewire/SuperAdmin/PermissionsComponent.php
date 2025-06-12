<?php

namespace App\Livewire\SuperAdmin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;

class PermissionsComponent extends Component
{
    use WithPagination;

    public $permissionId;
    public $editMode;
    public $search;
    # Properties for the form
    public $name;

    public function rules()
    {
        return [
            'name' => 'required|string|unique:permissions,name,' . $this->permissionId, // Exclude the current permission's name
        ];
    }

    public function clear()
    {
        $this->reset();
        $this->resetValidation();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view(
            'livewire.super-admin.permissions-component',
            [
                'permissions' => $this->loadPermissions(),
            ]
        );
    }

    public function loadPermissions()
    {
        return Permission::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name', 'asc')
            ->paginate('5');
    }

    public function createPermission()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $permission = new Permission();
                $permission->name = $this->name;
                $permission->guard_name = 'web';
                $permission->save();

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn(new Permission())
                    ->useLog('permission')
                    ->event('created')
                    ->withProperties([
                        'name' => $this->name,
                        'guard_name' => 'web'
                    ])
                    ->tap(function (Activity $activity) {
                        $activity->log_name = 'role';
                        $activity->subject_id = Permission::latest()->first()->id;
                    })
                    ->log('Permission has been created by ' . Auth::user()->name);

                $this->clear();
                $this->dispatch('hide-permissions-modal');
                $this->dispatch('success', message: 'Permission created successfully.');
            });
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
