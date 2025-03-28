<?php

namespace App\Livewire\SuperAdmin;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

class PermissionsComponent extends Component
{
    use WithPagination;

    public $permissionId;
    public $editMode;
    # Properties for the form
    public $name;

    public function clear()
    {
        $this->reset();
        $this->resetValidation();
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
        return Permission::paginate('1');
    }

    public function createPermission()
    {
        try {
            DB::transaction(function () {
                $permission = new Permission();
                $permission->name = $this->name;
                $permission->guard_name = 'web';
                $permission->save();

                $this->clear();
                $this->dispatch('hide-permissions-modal');
                $this->dispatch('success', message: 'Permission created successfully.');
            });
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
