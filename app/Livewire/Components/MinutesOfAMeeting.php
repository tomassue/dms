<?php

namespace App\Livewire\Components;

use App\Models\Apo\Meeting;
use App\Models\Apo\MinutesOfMeeting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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

    public function rules()
    {
        return [
            'activity' => [
                'required'
            ],
            'point_person' => [
                'sometimes'
            ],
            'expected_output' => [
                'sometimes'
            ],
            'agreements' => [
                'sometimes'
            ],
        ];
    }

    /**
     * goBack()
     * Emits and event to parent component
     * The purpose of this method is to emit an event to the parent component then there's a listener to it that performs an action
     * to clear the $apoMeetingId that will close the child component.
     */
    public function goBack()
    {
        $this->dispatch('clear');
    }

    public function cancel()
    {
        $this->resetExcept('apoMeetingId', 'show');
        $this->resetValidation();
    }

    public function render()
    {
        return view(
            'livewire.components.minutes-of-a-meeting',
            [
                'apo_meeting' => $this->loadApoMeeting($this->apoMeetingId),
                'minutes_of_meeting' => $this->loadMinutesofAMeeting(new MinutesOfMeeting()),
            ]
        );
    }

    public function loadApoMeeting($apoMeetingId)
    {
        return Meeting::find($apoMeetingId);
    }

    public function loadMinutesofAMeeting(MinutesOfMeeting $minutes)
    {
        return $minutes->where('apo_meeting_id', $this->apoMeetingId)->get();
    }

    public function saveMinutes()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                MinutesOfMeeting::updateOrCreate(
                    [
                        'id' => $this->apoMinutesOfMeetingId
                    ],
                    [
                        'apo_meeting_id' => $this->apoMeetingId,
                        'activity' => $this->activity,
                        'point_person' => $this->point_person,
                        'expected_output' => $this->expected_output,
                        'agreements' => $this->agreements
                    ]
                );

                $this->cancel();
                $this->dispatch('success', message: 'Minutes successfully saved.');
            });
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editMinute(MinutesOfMeeting $minutesOfMeeting)
    {
        try {
            $this->apoMinutesOfMeetingId = $minutesOfMeeting->id;

            $this->activity = $minutesOfMeeting->activity;
            $this->point_person = $minutesOfMeeting->point_person;
            $this->expected_output = $minutesOfMeeting->expected_output;
            $this->agreements = $minutesOfMeeting->agreements;

            $this->dispatch('scroll-to-top');
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function removeMinute(MinutesOfMeeting $minutesOfMeeting)
    {
        try {
            $minutesOfMeeting->delete();
            $this->dispatch('success', message: 'Minutes successfully removed.');
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
