<?php

namespace App\Livewire\Shared\Incoming;

use App\Models\File;
use App\Models\IncomingRequest;
use App\Models\RefIncomingRequestCategory;
use App\Models\RefStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class Requests extends Component
{
    use WithPagination, WithFileUploads;

    public $editMode;
    public $search,
        $filter_start_date,
        $filter_end_date;
    public $incomingRequestId;
    public $preview_file = [];
    public $activity_log = [];

    /* ------------------------------ begin::fields ----------------------------- */

    public $no,
        $office_barangay_organization,
        $date_requested,
        $ref_incoming_request_category_id,
        $date_time,
        $contact_person_name,
        $contact_person_number,
        $description,
        $ref_status_id,
        $remarks,
        $file_id = []; // for file upload - MorphMany

    /* ------------------------------- end::fields ------------------------------ */

    public function rules()
    {
        return [
            'no' => 'required|unique:incoming_requests,no,' . $this->incomingRequestId,
            'office_barangay_organization' => 'required',
            'date_requested' => 'required',
            'ref_incoming_request_category_id' => 'required|exists:ref_incoming_request_categories,id',
            'date_time' => 'required',
            'contact_person_name' => 'required',
            'contact_person_number' => 'required',
            'description' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'ref_incoming_request_category_id' => 'category',
        ];
    }

    #[On('filter')]
    public function filter($start_date, $end_date)
    {
        $this->filter_start_date = $start_date;
        $this->filter_end_date = $end_date;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    #[On('clear-filter-data')]
    public function clear()
    {
        $this->reset();
        $this->resetValidation();
        $this->dispatch('reset-files');
    }

    public function generateReferenceNo()
    {
        $this->no = IncomingRequest::generateUniqueReference('REF-', 8); // Pre-generate reference number to show in the input field (disabled).
    }

    public function render()
    {
        return view(
            'livewire.shared.incoming.requests',
            [
                'incoming_requests' => $this->loadIncomingRequests(),
                'incoming_request_categories' => $this->loadIncomingRequestCategories(), // Incoming Request Category dropdown
                'status' => $this->loadStatus(), // Status dropdown
            ]
        );
    }

    public function loadIncomingRequests()
    {
        return IncomingRequest::query()
            ->when($this->search, function ($query) {
                $query->where('no', 'like', '%' . $this->search . '%')
                    ->orWhere('office_barangay_organization', 'like', '%' . $this->search . '%');
            })
            ->when($this->filter_start_date && $this->filter_end_date, function ($query) {
                $query->whereBetween('date_time', [
                    Carbon::parse($this->filter_start_date)->startOfDay(),
                    Carbon::parse($this->filter_end_date)->endOfDay()
                ]);
            })
            ->paginate(10);
    }

    public function loadIncomingRequestCategories()
    {
        return RefIncomingRequestCategory::all();
    }

    public function loadStatus()
    {
        return RefStatus::all();
    }

    public function saveIncomingRequest()
    {
        $this->validate($this->rules(), [], $this->attributes());

        try {
            DB::transaction(function () {
                $incomingRequest = IncomingRequest::updateOrCreate(
                    ['id' => $this->incomingRequestId ?? null],
                    [
                        'no' => $this->no,
                        'office_barangay_organization' => $this->office_barangay_organization,
                        'date_requested' => $this->date_requested,
                        'ref_incoming_request_category_id' => $this->ref_incoming_request_category_id,
                        'date_time' => $this->date_time,
                        'contact_person_name' => $this->contact_person_name,
                        'contact_person_number' => $this->contact_person_number,
                        'description' => $this->description,
                        'ref_status_id' => 1,
                        'remarks' => $this->remarks
                    ]
                );

                // save files
                $this->saveFiles($incomingRequest);

                $this->clear();
                $this->dispatch('hide-incoming-request-modal');
                $this->dispatch('success', message: 'Incoming Request successfully saved.');
            });
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    protected function saveFiles($model)
    {
        if (empty($this->file_id)) return null;

        $uploadedFiles = [];

        foreach ((array)$this->file_id as $file) {
            $uploadedFiles[] = $model->files()->create([
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'type' => $file->getMimeType(),
                'file' => file_get_contents($file->getRealPath()),
                // fileable_id and fileable_type are auto-set by morphMany
            ]);
        }

        return $uploadedFiles;
    }

    public function editIncomingRequest(IncomingRequest $incomingRequestId)
    {
        try {
            $this->editMode = true;
            $this->incomingRequestId = $incomingRequestId->id;
            $this->no = $incomingRequestId->no;
            $this->office_barangay_organization = $incomingRequestId->office_barangay_organization;
            $this->date_requested = $incomingRequestId->date_requested;
            $this->ref_incoming_request_category_id = $incomingRequestId->ref_incoming_request_category_id;
            $this->date_time = $incomingRequestId->date_time;
            $this->contact_person_name = $incomingRequestId->contact_person_name;
            $this->contact_person_number = $incomingRequestId->contact_person_number;
            $this->description = $incomingRequestId->description;
            $this->ref_status_id = $incomingRequestId->ref_status_id;
            $this->remarks = $incomingRequestId->remarks;
            $this->preview_file = $incomingRequestId->files;

            $this->dispatch('show-incoming-request-modal');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function viewFile($fileId)
    {
        $signedURL = URL::temporarySignedRoute(
            'file.view',
            now()->addMinutes(10),
            ['id' => $fileId]
        );

        $this->dispatch('open-file', url: $signedURL);
    }

    public function activityLog($id)
    {
        try {
            $this->activity_log = Activity::where('subject_type', IncomingRequest::class)
                ->where('subject_id', $id)
                ->where('log_name', 'incoming_request')
                ->whereNot('event', 'created')
                ->latest()
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'description' => $activity->description,
                        'causer' => $activity->causer?->name ?? 'System',
                        'created_at' => Carbon::parse($activity->created_at)->format('M d, Y h:i A'),
                        'changes' => collect($activity->properties['attributes'] ?? [])
                            ->except(['id', 'created_at', 'updated_at', 'deleted_at']) // Exclude timestamps
                            ->map(function ($newValue, $key) use ($activity) {
                                $oldValue = $activity->properties['old'][$key] ?? 'N/A';

                                // Format date fields
                                if (in_array($key, ['date_requested', 'deleted_at'])) {
                                    $oldValue = $oldValue !== 'N/A' ? Carbon::parse($oldValue)->format('M d, Y') : 'N/A';
                                    $newValue = $newValue !== 'N/A' ? Carbon::parse($newValue)->format('M d, Y') : 'N/A';
                                }

                                // Replace foreign keys with related names
                                if ($key === 'ref_incoming_request_category_id') {
                                    $oldValue = $oldValue !== 'N/A' ? RefIncomingRequestCategory::find($oldValue)?->name : 'N/A';
                                    $newValue = $newValue !== 'N/A' ? RefIncomingRequestCategory::find($newValue)?->name : 'N/A';
                                }

                                // Convert array values to a string (e.g., file IDs to filenames)
                                if ($key === 'file_id') {
                                    // Ensure values are decoded from JSON if stored as a string
                                    $oldValue = is_string($oldValue) ? json_decode($oldValue, true) : $oldValue;
                                    $newValue = is_string($newValue) ? json_decode($newValue, true) : $newValue;

                                    if (is_array($oldValue)) {
                                        $oldValue = File::whereIn('id', $oldValue)->pluck('name')->toArray();
                                        $oldValue = !empty($oldValue) ? implode(', ', $oldValue) : 'N/A';
                                    }

                                    if (is_array($newValue)) {
                                        $newValue = File::whereIn('id', $newValue)->pluck('name')->toArray();
                                        $newValue = !empty($newValue) ? implode(', ', $newValue) : 'N/A';
                                    }
                                }

                                return [
                                    'field' => ucfirst(str_replace('_', ' ', $key)), // Format key
                                    'old' => $oldValue,
                                    'new' => $newValue,
                                ];
                            })
                            ->values()
                            ->toArray()
                    ];
                });

            $this->dispatch('show-activity-log-modal');
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    /**
     * TODO
     * Create a morphed table for forwarded documents.
     * Forwarded documents should have a read indicator that the user in which the document is forwarded.
     */
}
