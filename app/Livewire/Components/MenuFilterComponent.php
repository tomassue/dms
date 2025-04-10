<?php

namespace App\Livewire\Components;

use Livewire\Component;

class MenuFilterComponent extends Component
{
    //* APO
    public $start_date,
        $end_date;

    public function filter()
    {
        $this->dispatch(
            'filter',
            start_date: $this->start_date,
            end_date: $this->end_date
        );
    }

    public function clear()
    {
        $this->reset();
        $this->dispatch('clear-filter-date'); // date range picker

        $this->dispatch('clear-filter-data');
    }

    public function render()
    {
        return view('livewire.components.menu-filter-component');
    }
}
