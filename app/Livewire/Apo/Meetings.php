<?php

namespace App\Livewire\Apo;

use App\Models\Apo\Meeting;
use App\Models\RefApoMeetingsCategory;
use App\Models\RefSignatories;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Title('Meetings')]
class Meetings extends Component
{
    use WithPagination, WithFileUploads;

    public $show = true; //* An indicator to show/hide this component. Related to MinutesOfAMeeting child component.
    public $search, $filter_start_date, $filter_end_date;
    public $editMode;
    public $meetingId;
    /* ----------------------- begin:: meeting properties ----------------------- */
    public $date,
        $ref_apo_meetings_category_id,
        $description,
        $time_start,
        $time_end,
        $venue,
        $prepared_by, $prepared_by_position,
        $approved_by,
        $noted_by,
        $file_id = [];
    public $preview_file = [];
    /* ------------------------ end:: meeting properties ------------------------ */

    /**
     * dehydrate()
     * he dehydrate() method in Livewire is triggered after every component request cycle, including on the request that occurs during logout. 
     * At that moment, auth()->user() no longer exists because the session has been cleared or is in the process of being destroyed — which results in null, 
     * and then trying to access ->name causes the 404 (or sometimes a null property access error, depending on your config).
     * * Add a guard against this by checking if the user is still authenticated before accessing auth()->user()->name. 
     */
    // public function dehydrate()
    // {
    //     if (auth()->check()) {
    //         $this->prepared_by = Auth::user()->name;
    //     }
    // }

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
            'ref_apo_meetings_category_id' => 'required',
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


    #[On('filter')]
    public function filter($start_date, $end_date)
    {
        $this->filter_start_date = $start_date;
        $this->filter_end_date = $end_date;
    }

    #[On(['clear', 'clear-filter-data'])]
    public function clear()
    {
        $this->resetExcept($this->show);
        $this->resetPage();

        $this->dispatch('reset-files');
    }

    public function render()
    {
        return view(
            'livewire.apo.meetings',
            [
                'meetings' => $this->loadMeetings(),
                'signatories' => $this->loadSignatories(), // signatories dropdown
                'categories' => $this->getApoMeetingsCategories() // categories dropdown
            ]
        );
    }

    public function loadMeetings()
    {
        return Meeting::query()
            ->dateRange($this->filter_start_date, $this->filter_end_date)
            ->search($this->search)
            ->orderBy('date', 'desc')
            ->paginate(10);
    }

    public function loadSignatories()
    {
        // return RefSignatories::all();
        return RefSignatories::withinOffice()
            // ->where('ref_division_id', Auth::user()->user_metadata->ref_division_id) //! removed
            ->get();
    }

    public function getApoMeetingsCategories()
    {
        return RefApoMeetingsCategory::all();
    }

    public function saveMeeting()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $data = [
                    'date' => $this->date,
                    'ref_apo_meetings_category_id' => $this->ref_apo_meetings_category_id,
                    'description' => $this->description,
                    'time_start' => $this->time_start,
                    'time_end' => $this->time_end,
                    'venue' => $this->venue,
                    'prepared_by' => $this->prepared_by ?? null,
                    'prepared_by_position' => $this->prepared_by_position ?? null,
                    'approved_by' => $this->approved_by ?? null,
                    'noted_by' => $this->noted_by ?? null,
                    'office_id' => Auth::user()->roles()->first()->id,
                    'ref_division_id' => Auth::user()?->user_metadata?->ref_division_id ?? null
                ];

                $meeting = Meeting::updateOrCreate(
                    ['id' => $this->meetingId],
                    $data
                );

                $this->saveFiles($meeting);

                $this->clear();
                $this->dispatch('hide-meeting-modal');
                $this->dispatch('success', message: 'Meeting saved successfully.');
            });
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    protected function saveFiles($model)
    {
        if (empty($this->file_id)) return null;

        $uploadedFiles = [];

        foreach ((array)$this->file_id as $file) {
            if ($file->isValid()) {
                $uploadedFiles[] = $model->files()->create([
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                    'file' => file_get_contents($file->getRealPath()),
                ]);
            }
        }

        return $uploadedFiles;
    }


    public function editMeeting(Meeting $meeting)
    {
        try {
            $this->editMode = true;
            $this->meetingId = $meeting->id;

            $this->date = $meeting->date;
            $this->ref_apo_meetings_category_id = $meeting->ref_apo_meetings_category_id;
            $this->description = $meeting->description;
            $this->time_start = $meeting->time_start;
            $this->time_end = $meeting->time_end;
            $this->venue = $meeting->venue;
            $this->prepared_by = $meeting->prepared_by ?? '';
            $this->prepared_by_position = $meeting->prepared_by_position ?? '';
            $this->approved_by = $meeting->approved_by ?? '';
            $this->noted_by = $meeting->noted_by ?? '';
            $this->preview_file = $meeting->files->where('type', '!=', 'application/pdf'); // We will be only showing images and not the PDF file.

            $this->dispatch('show-meeting-modal');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
