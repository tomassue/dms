<?php

namespace App\Livewire\Components;

use App\Models\RefStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MenuFilterComponent extends Component
{
    public $page;
    //* APO
    public $start_date,
        $end_date,
        $status;

    public function mount($page)
    {
        $this->page = $page;
    }

    public function filter()
    {
        $this->dispatch(
            'filter',
            start_date: $this->start_date,
            end_date: $this->end_date,
            status: $this->status
        );
    }

    public function clear()
    {
        $this->reset();

        $this->dispatch('clear-filter-date'); // date range picker

        $this->dispatch('clear-filter-data'); // Clear ALL filter data for parent components
    }

    public function render()
    {
        return view('livewire.components.menu-filter-component', [
            'status_dropdown' => $this->loadStatus(), // Status dropdown
        ]);
    }

    public function loadStatus()
    {
        switch ($this->page) {
            case 'outgoing':
                $status = RefStatus::outgoing()->get();
                break;
            case 'incoming':
                $status = RefStatus::incoming()->get();
                break;
            default:
                $status = RefStatus::all();
                break;
        }

        return $status;
    }
}
