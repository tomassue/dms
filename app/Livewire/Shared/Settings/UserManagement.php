<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefDivision;
use App\Models\RefPosition;
use App\Models\User;
use App\Models\UserMetadata;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Title('User Management')]
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
        $is_office_admin,
        $permissions = [];
    public $ref_division_id,
        $ref_position_id;

    public function rules()
    {
        $rules = [
            'name' => 'required|string',
            'username' => 'required|string|unique:users,username,' . $this->userId, // Exclude the current user's username
            'ref_division_id' => 'nullable|exists:ref_divisions,id'
        ];

        if ($this->editMode) {
            $rules['email'] = 'required|email|unique:users,email,' . $this->userId; // Exclude the current user's email
        }

        if (Auth::user()->hasRole('Super Admin')) {
            $rules['is_office_admin'] = 'required';
            $rules['role_id'] = 'required|exists:roles,id'; // Ensure the role exists,
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'role_id' => 'role',
            'ref_division_id' => 'division',
            'ref_position_id' => 'position'
        ];
    }

    public function updated($property)
    {

        if ($property === 'role_id') {
            $this->reset('ref_division_id');
        }
    }

    public function clear()
    {
        $this->reset();
        $this->resetValidation();
    }

    public function loadUsers()
    {
        $user = User::query()
            ->with(['roles', 'user_metadata'])
            ->withoutRole('Super Admin') // Exclude Super Admin role
            ->where('id', '!=', auth()->id()) // Always exclude current user first
            ->when(!Auth::user()->hasRole('Super Admin'), function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('role_id', Auth::user()->roles()->first()->id);
                });
            }, function ($query) {
                //return all users if Super Admin
                return $query;
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('username', 'like', '%' . $this->search . '%');
                });
            })
            ->withTrashed()
            ->paginate(10);

        return $user;
    }

    /**
     * Summary of loadRoles
     * a.k.a Offices ^_^
     */
    public function loadRoles()
    {
        return Role::whereNot('name', 'Super Admin')
            ->get();
    }

    public function loadDivisions()
    {
        //! when() does not work. it doesn't retrieve the selected options only the Ids 1 and 2. ???
        // return RefDivision::when($this->role_id, function ($query) {
        //     $query->where('office_id', $this->role_id); //* a.k.a role_id
        // })
        //     ->get()
        //     ->map(function ($query) {
        //         return [
        //             'id' => $query->id,
        //             'name' => $query->name
        //         ];
        //     });

        return RefDivision::all()
            ->map(function ($query) {
                return [
                    'id' => $query->id,
                    'name' => $query->name
                ];
            });
    }

    public function loadPositions()
    {
        return RefPosition::all();
    }

    public function render()
    {
        return view(
            'livewire.shared.settings.user-management',
            [
                'users' => $this->loadUsers(),
                'roles' => $this->loadRoles(), // Load roles for the dropdown
                'divisions' => $this->loadDivisions(), // Load divisions for the dropdown
                'positions' => $this->loadPositions(), // Load positions for the dropdown
            ]
        );
    }

    public function createUser()
    {
        $this->validate($this->rules(), [], $this->attributes());

        try {
            DB::transaction(function () {
                $user = new User();
                $user->name = $this->name;
                $user->username = $this->username;
                $user->email = $this->username . '@email.com';
                $user->password = Hash::make('password'); // Set a default password
                $user->save();

                // Create user metadata
                $user_metadata = new UserMetadata();
                $user_metadata->ref_division_id = $this->ref_division_id;
                $user_metadata->ref_position_id = $this->ref_position_id;
                $user_metadata->user_id = $user->id;

                if (Auth::user()->hasRole('Super Admin')) {
                    $user_metadata->is_office_admin = $this->is_office_admin;
                }

                $user_metadata->save();

                // Role assignment
                if (Auth::user()->hasRole('Super Admin')) {
                    $role = Role::findOrFail($this->role_id);
                } else {
                    $role = Role::findOrFail(Auth::user()->roles()->first()->id);
                }

                $user->syncRoles($role);
                $user->syncPermissions($this->permissions);

                $this->clear();
                $this->dispatch('hide-users-modal');
                $this->dispatch('success', message: 'User created successfully.');
            });
        } catch (\Throwable $th) {
            throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editUser($userId)
    {
        try {
            $user = User::findOrFail($userId);
            $this->name = $user->name;
            $this->username = $user->username;
            $this->email = $user->email;
            $this->role_id = $user->roles->first()->id; // Assuming the user has only one role
            $this->permissions = $user->getPermissionNames()->toArray(); // Get all permissions for the user

            if (Auth::user()->hasRole('Super Admin')) {
                $this->is_office_admin = $user->user_metadata->is_office_admin;
            }

            $this->ref_division_id = UserMetadata::where('user_id', $userId)->value('ref_division_id');
            $this->ref_position_id = UserMetadata::where('user_id', $userId)->value('ref_position_id');

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
        $this->validate($this->rules(), [], $this->attributes());

        try {
            DB::transaction(function () {
                $user = User::findOrFail($this->userId);
                $user->name = $this->name;
                $user->username = $this->username;
                $user->email = $this->email;
                $user->save();

                // Update user metadata
                $user_metadata = UserMetadata::findOrFail($this->userId);
                $user_metadata->ref_division_id = $this->ref_division_id;
                $user_metadata->ref_position_id = $this->ref_position_id;
                $user_metadata->user_id = $user->id;

                if (Auth::user()->hasRole('Super Admin')) {
                    $user_metadata->is_office_admin = $this->is_office_admin;
                }

                $user_metadata->save();

                // // Use updateOrCreate for metadata
                // $userMetadataData = [
                //     'ref_division_id' => $this->ref_division_id === '' ? null : $this->ref_division_id,
                //     'ref_position_id' => $this->ref_position_id === '' ? null : $this->ref_position_id,
                // ];

                // if (Auth::user()->hasRole('Super Admin')) {
                //     $userMetadataData['is_office_admin'] = $this->is_office_admin;
                // }

                // UserMetadata::updateOrCreate(
                //     ['user_id' => $this->userId],
                //     $userMetadataData
                // );

                // Sync roles and permissions
                $role = Role::findOrFail($this->role_id);
                $user->syncRoles($role);
                $user->syncPermissions($this->permissions);

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
            $user = User::withTrashed()->findOrFail($userId);
            $user->password = Hash::make('password');
            $user->save();

            $this->clear();
            $this->dispatch('success', message: 'Password reset successfully.');
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
