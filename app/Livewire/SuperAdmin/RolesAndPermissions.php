<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Roles and Permissions')]
class RolesAndPermissions extends Component
{
    public function render()
    {
        return view('livewire.super-admin.roles-and-permissions');
    }
}
