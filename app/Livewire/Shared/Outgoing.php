<?php

namespace App\Livewire\Shared;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Outgoing')]
class Outgoing extends Component
{
    public function render()
    {
        return view('livewire.shared.outgoing');
    }
}
