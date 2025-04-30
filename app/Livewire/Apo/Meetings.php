<?php

namespace App\Livewire\Apo;

use App\Models\Apo\Meeting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Meetings extends Component
{
    use WithPagination;

    public $show = true; //* An indicator to show/hide this component. Related to MinutesOfAMeeting child component.
    public $editMode;
    public $meetingId;
    /* ----------------------- begin:: meeting properties ----------------------- */
    public $date, $description, $time_start, $time_end, $venue, $prepared_by, $approved_by, $noted_by;
    /* ------------------------ end:: meeting properties ------------------------ */

    public function mount()
    {
        $this->prepared_by = Auth::user()->name;
    }

    public function rules()
    {
        $rules = [
            'date' => [
                'required',
                'date',
                Rule::unique('apo_meetings')->where(function ($query) {
                    return $query->where('description', request('description'));
                })->ignore($this->meetingId),
            ],
            'description' => 'required',
            'time_start' => 'required',
            'venue' => 'required'
        ];

        return $rules;
    }

    /**
     * readMinutesOfMeeting()
     * Sets the meetingId property.
     * * The meetingId is then passed to the child component. The apoMeetingId property in the child component is REACTIVE.
     */
    public function readMinutesOfMeeting($meetingId)
    {
        $this->meetingId = $meetingId;
        $this->show = false;
    }

    #[On('clear')]
    public function clear()
    {
        $this->resetExcept($this->show);
        $this->resetPage();
    }

    public function render()
    {
        return view(
            'livewire.apo.meetings',
            [
                'meetings' => $this->loadMeetings()
            ]
        );
    }

    public function loadMeetings()
    {
        return Meeting::paginate(10);
    }

    public function saveMeeting()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                Meeting::updateOrCreate(
                    ['id' => $this->meetingId],
                    [
                        'date' => $this->date,
                        'description' => $this->description,
                        'time_start' => $this->time_start,
                        'time_end' => $this->time_end,
                        'venue' => $this->venue,
                        'prepared_by' => Auth::user()->id,
                    ]
                );

                $this->clear();
                $this->dispatch('hide-meeting-modal');
                $this->dispatch('success', message: 'Meeting saved successfully.');
            });
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function editMeeting(Meeting $meeting)
    {
        try {
            $this->editMode = true;
            $this->meetingId = $meeting->id;

            $this->date = $meeting->date;
            $this->description = $meeting->description;
            $this->time_start = $meeting->time_start;
            $this->time_end = $meeting->time_end;
            $this->venue = $meeting->venue;
            $this->prepared_by = $meeting->preparedby->name;
            $this->dispatch('show-meeting-modal');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
