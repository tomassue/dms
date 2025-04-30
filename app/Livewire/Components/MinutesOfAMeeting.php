<?php

namespace App\Livewire\Components;

use App\Models\Apo\Meeting;
use App\Models\Apo\MinutesOfMeeting;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class MinutesOfAMeeting extends Component
{
    #[Reactive]
    public $show; //* Opposite from parent component
    public $editMode;
    public $apoMinutesOfMeetingId;

    #[Reactive]
    public $apoMeetingId;
    public $activity, $point_person, $expected_output, $agreements;

    public function clear()
    {
        $this->dispatch('clear');
    }

    public function render()
    {
        return view(
            'livewire.components.minutes-of-a-meeting',
            [
                'apo_meeting' => $this->loadApoMeeting(new Meeting()),
                'minutes_of_meeting' => $this->loadMinutesofAMeeting(new MinutesOfMeeting())
            ]
        );
    }

    public function loadApoMeeting(Meeting $apoMeetingId)
    {
        return $apoMeetingId->first();
    }

    public function loadMinutesofAMeeting(MinutesOfMeeting $minutes)
    {
        return $minutes->all();
    }
}
