<?php

namespace App\Livewire\Shared;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AccountSettings extends Component
{
    public $editProfile;
    public $user;
    public $name,
        $username,
        $email,
        $office,
        $division,
        $position;

    public function mount()
    {
        $this->user = auth()->user();
        $this->loadPersonalDetails();
    }

    public function render()
    {
        return view('livewire.shared.account-settings');
    }

    public function loadPersonalDetails()
    {
        $this->name = $this->user->name;
        $this->username = $this->user->username;
        $this->email = $this->user->email;
        $this->office = Auth::user()->getRoleNames()->first();
        $this->division = $this->user->user_metadata->division->name ?? null;
        $this->position = $this->user->user_metadata->position->name ?? null;
    }

    public function editPersonalDetails()
    {
        try {
            $this->editProfile = true;
        } catch (\Throwable $th) {
            throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
