<?php

namespace App\Livewire\Shared\Settings;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserManagement extends Component
{
    use WithPagination;

    public $userId;
    public $editMode;
    public $search;

    # Properties
    public $name,
        $username,
        $email,
        $role_id,
        $permissions = [];

    public function rules()
    {
        return [
            'name' => 'required|string',
            'username' => 'required|string|unique:users,username,' . $this->userId, // Exclude the current user's username
            'email' => 'required|email|unique:users,email,' . $this->userId, // Exclude the current user's email
            'role_id' => 'required|exists:roles,id' // Ensure the role exists
        ];
    }

    public function clear()
    {
        $this->reset();
        $this->resetValidation();
    }

    public function loadUsers()
    {
        return User::query()
            ->with('roles')
            ->where('id', '!=', auth()->id()) // Always exclude current user first
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('username', 'like', '%' . $this->search . '%');
                });
            })
            ->withTrashed()
            ->paginate(10);
    }

    public function loadRoles()
    {
        return Role::whereNot('name', 'Super Admin')
            ->get();
    }

    public function render()
    {
        return view(
            'livewire.shared.settings.user-management',
            [
                'users' => $this->loadUsers(),
                'roles' => $this->loadRoles(), // Load roles for the dropdown
            ]
        );
    }

    public function createUser()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $user = new User();
                $user->name = $this->name;
                $user->username = $this->username;
                $user->email = $this->email;
                $user->password = Hash::make('password'); // Set a default password
                $user->save();

                $role = Role::findOrFail($this->role_id);
                $user->syncRoles($role);

                $user->syncPermissions($this->permissions); // Sync permissions if needed

                $this->clear();
                $this->dispatch('hide-users-modal');
                $this->dispatch('success', message: 'User created successfully.');
            });
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editUser($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $this->userId = $user->id;
            $this->name = $user->name;
            $this->username = $user->username;
            $this->email = $user->email;
            $this->role_id = $user->roles->first()->id ?? ''; // Assuming the user has only one role
            // $user->roles->pluck('id'); // Returns collection of IDs

            $this->permissions = $user->getPermissionNames()->toArray(); // Get all permissions for the user

            $this->editMode = true;

            $this->userId = $userId;

            $this->dispatch('show-users-modal');
        } catch (\Exception $e) {
            // throw $e;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function updateUser()
    {
        // dd($this->permissions);
        $this->validate();

        try {
            DB::transaction(function () {
                $user = User::find($this->userId);
                $user->name = $this->name;
                $user->username = $this->username;
                $user->email = $this->email;
                $user->save();

                $role = Role::findOrFail($this->role_id);
                $user->syncRoles($role);

                $user->syncPermissions($this->permissions); // Sync permissions if needed

                $this->clear();
                $this->dispatch('hide-users-modal');
                $this->dispatch('success', message: 'User updated successfully.');
            });
        } catch (\Exception $e) {
            // throw $e;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function deleteUser($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $user->delete();

            $this->clear();
            $this->dispatch('success', message: 'User deleted successfully.');
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function restoreUser($userId)
    {
        try {
            $user = User::withTrashed()->findOrFail($userId);
            $user->restore();

            $this->clear();
            $this->dispatch('success', message: 'User restored successfully.');
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function resetPasswordUser($userId)
    {
        try {
            $user = new User();
            $user->password = Hash::make('password');
            $user->save();

            $this->clear();
            $this->dispatch('success', message: 'Password reset successfully.');
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
