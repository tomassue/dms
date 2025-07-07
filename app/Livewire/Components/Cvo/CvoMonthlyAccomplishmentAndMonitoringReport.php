<?php

namespace App\Livewire\Components\Cvo;

use App\Models\RefAccomplishmentCategory;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class CvoMonthlyAccomplishmentAndMonitoringReport extends Component
{
    #[Reactive]
    public $accomplishmentId;

    public function mount($accomplishmentId)
    {
        $this->accomplishmentId = $accomplishmentId;
    }

    public function getCategorySelect()
    {
        $categories = RefAccomplishmentCategory::all();

        return $categories;
    }

    public function getSubCategorySelect()
    {
        // if (!$this->ref_accomplishment_category_id) {
        //     return collect(); //
        // }

        // $sub_categories = RefAccomplishmentSubcategory::where('ref_accomplishment_category_id', $this->ref_accomplishment_category_id)
        //     ->where('parent_id', null);

        // return $sub_categories;
    }

    public function render()
    {
        return view(
            'livewire.components.cvo.cvo-monthly-accomplishment-and-monitoring-report',
            [
                'category_select' => $this->getCategorySelect(),
                'sub_category_select' => $this->getSubCategorySelect(),
            ]
        );
    }

    public function hideMonthlyAccomplishmentAndMonitoringReport()
    {
        $this->dispatch('hideMonthlyAccomplishmentAndMonitoringReport');
    }
}
