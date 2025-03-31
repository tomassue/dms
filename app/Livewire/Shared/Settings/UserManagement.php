<?php

namespace App\Livewire\Shared\Settings;

use App\Models\Team;
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

    # Properties
    public $name,
        $team_id,
        $username,
        $email,
        $role_id;

    public function rules()
    {
        return [
            'name' => 'required|string',
            'username' => 'required|string|unique:users,username,' . $this->userId, // Exclude the current user's username
            'team_id' => 'required|exists:teams,id',
            'email' => 'required|email|unique:users,email,' . $this->userId // Exclude the current user's email
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

    public function loadUsers()
    {
        return User::with('team')
            ->paginate(10);
    }

    public function loadTeams()
    {
        return Team::all();
    }

    public function loadRoles()
    {
        $user_team = auth()->user()->team_id;

        return Role::whereNot('name', 'Super Admin')
            // ->where('team_id', $user_team)
            ->get();
    }

    public function render()
    {
        return view(
            'livewire.shared.settings.user-management',
            [
                'users' => $this->loadUsers(),
                'teams' => $this->loadTeams(), // Load teams for the dropdown
                'roles' => $this->loadRoles(), // Load roles for the dropdown
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
                $user->team_id = $this->team_id;
                $user->username = $this->username;
                $user->email = $this->email;
                $user->password = Hash::make('password'); // Set a default password
                // $user->assignRole();
                $user->save();

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
            $this->team_id = $user->team_id;
            $this->username = $user->username;
            $this->email = $user->email;

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
                $user = User::find($this->userId);
                $user->name = $this->name;
                $user->team_id = $this->team_id;
                $user->username = $this->username;
                $user->email = $this->email;
                $user->save();

                // Get the role by ID and verify it exists for this team
                $role = Role::where('id', $this->role_id)
                    ->where('team_id', $this->team_id)
                    ->firstOrFail();

                // Clear existing roles for this team
                $user->roles()->wherePivot('team_id', $this->team_id)->detach();

                // Assign the new role with team context
                $user->assignRole([$role->name => $this->team_id]);

                $this->clear();
                $this->dispatch('hide-users-modal');
                $this->dispatch('success', message: 'User updated successfully.');
            });
        } catch (\Exception $e) {
            throw $e;
            $this->dispatch('error', message: 'Something went wrong: ' . $e->getMessage());
            // Remove the throw if you want to handle it gracefully
        }
    }
}
