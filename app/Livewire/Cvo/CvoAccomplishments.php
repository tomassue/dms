<?php

namespace App\Livewire\Cvo;

use App\Models\RefAccomplishmentCategory;
use Livewire\Component;

class CvoAccomplishments extends Component
{
    public $editMode;

    public function render()
    {
        return view(
            'livewire.cvo.cvo-accomplishments',
            [
                'category_select' => $this->getCategorySelect(),
            ]
        );
    }

    public function getCategorySelect()
    {
        $categories = RefAccomplishmentCategory::all();

        return $categories;
    }
}
