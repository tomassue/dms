<?php

namespace App\Livewire\Shared\Settings;

use App\Models\RefApoMeetingsCategory;
use Livewire\Component;
use Livewire\WithPagination;

class MeetingsCategory extends Component
{
    use WithPagination;

    public $editMode;
    public $meeting_category_id;
    public $search;
    public $name;

    public function clear()
    {
        $this->reset();
        $this->resetValidation();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.shared.settings.meetings-category', [
            'meeting_categories' => $this->getMeetingCategories(),
        ]);
    }

    public function getMeetingCategories()
    {
        $meeting_categories =  RefApoMeetingsCategory::withTrashed()
            ->latest()
            ->paginate(10);

        return $meeting_categories;
    }

    public function saveMeetingCategory()
    {
        try {
            $this->validate([
                'name' => 'required|unique:ref_apo_meetings_categories,name,' . $this->meeting_category_id,
            ]);

            RefApoMeetingsCategory::updateOrCreate(
                ['id' => $this->meeting_category_id],
                [
                    'name' => $this->name
                ]
            );

            $this->dispatch('hide-meeting-category-modal');
            $this->clear();
            $this->dispatch('success', message: 'Meeting Category saved successfully.');
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editMeetingCategory(RefApoMeetingsCategory $meeting_category)
    {
        try {
            $this->editMode = true;
            $this->meeting_category_id = $meeting_category->id;
            $this->name = $meeting_category->name;
            $this->dispatch('show-meeting-category-modal');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function deleteMeetingCategory(RefApoMeetingsCategory $meeting_category)
    {
        try {
            $meeting_category->delete();
            $this->dispatch('success', message: 'Meeting Category deleted successfully.');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function restoreMeetingCategory(RefApoMeetingsCategory $meeting_category)
    {
        try {
            $meeting_category->withTrashed()->restore();
            $this->dispatch('success', message: 'Meeting Category restored successfully.');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
