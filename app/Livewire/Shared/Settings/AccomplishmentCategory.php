<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefAccomplishmentCategory;
use Livewire\Component;
use Livewire\WithPagination;

class AccomplishmentCategory extends Component
{
    use WithPagination;

    public $search;
    public $editMode;
    public $accomplishmentCategoryId;
    # Properties Form
    public $name;

    public function clear()
    {
        $this->reset();
        $this->resetValidation();
    }

    public function render()
    {
        return view(
            'livewire.shared.settings.accomplishment-category',
            [
                'accomplishment_categories' => $this->loadAccomplishmentCategories()
            ]
        );
    }

    public function loadAccomplishmentCategories()
    {
        return RefAccomplishmentCategory::paginate(10);
    }
}
