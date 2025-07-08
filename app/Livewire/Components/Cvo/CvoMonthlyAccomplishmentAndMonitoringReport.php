<?php

namespace App\Livewire\Components\Cvo;

use App\Models\CvoAccomplishment;
use App\Models\RefAccomplishmentCategory;
use App\Models\RefAccomplishmentSubcategory;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class CvoMonthlyAccomplishmentAndMonitoringReport extends Component
{
    #[Reactive]
    public $accomplishmentId;
    public $accomplishment;
    public $ref_accomplishment_category_id;
    public $ref_accomplishment_subcategory_id;

    public function mount($accomplishmentId)
    {
        $this->accomplishmentId = $accomplishmentId;
        $this->accomplishment = CvoAccomplishment::find($accomplishmentId);
    }

    public function getCategorySelect()
    {
        $categories = RefAccomplishmentCategory::all();

        return $categories;
    }

    public function getSubCategorySelect()
    {
        if (!$this->ref_accomplishment_category_id) {
            return collect(); //
        }

        $sub_categories = RefAccomplishmentSubcategory::when($this->ref_accomplishment_category_id, function ($query) {
            $query->where('ref_accomplishment_category_id', $this->ref_accomplishment_category_id);
        })
            ->where('parent_id', null)
            ->get();

        return $sub_categories;
    }

    public function getCategories()
    {
        $categories = RefAccomplishmentCategory::with(['sub_category' => function ($query) {
            $query->with(['children', 'species']); // Eager load children and species for direct subcategories
        }])
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'order' => $category->order,
                    'accomplishment_category_name' => $category->accomplishment_category_name,
                    'sub_categories' => $category->sub_category->map(function ($subCategory) {
                        return [
                            'id' => $subCategory->id,
                            'accomplishment_sub_category_name' => $subCategory->accomplishment_sub_category_name, // Assuming you have this field
                            'parent_id' => $subCategory->parent_id,
                            'species' => $subCategory->species->map(function ($species) {
                                return [
                                    'id' => $species->id,
                                    'species_name' => $species->species_name, // Assuming you have this field
                                    // Add any other species attributes you need
                                ];
                            })->toArray(),
                            'children' => $subCategory->children->map(function ($childSubCategory) {
                                return [
                                    'id' => $childSubCategory->id,
                                    'accomplishment_sub_category_name' => $childSubCategory->accomplishment_sub_category_name, // Assuming you have this field
                                    'parent_id' => $childSubCategory->parent_id,
                                    'species' => $childSubCategory->species->map(function ($species) {
                                        return [
                                            'id' => $species->id,
                                            'species_name' => $species->species_name,
                                        ];
                                    })->toArray(),
                                    // You can nest further if you have more levels of children
                                ];
                            })->toArray(),
                        ];
                    })->toArray(),
                ];
            })
            ->toArray();

        return $categories;
    }

    public function render()
    {
        return view(
            'livewire.components.cvo.cvo-monthly-accomplishment-and-monitoring-report',
            [
                'category_select' => $this->getCategorySelect(),
                'sub_category_select' => $this->getSubCategorySelect(),
                'categories' => $this->getCategories(),
            ]
        );
    }

    public function hideMonthlyAccomplishmentAndMonitoringReport()
    {
        $this->dispatch('hideMonthlyAccomplishmentAndMonitoringReport');
    }
}
