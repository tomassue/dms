<?php

namespace App\Livewire\Shared\Incoming;

use App\Models\File;
use App\Models\Forwarded;
use App\Models\IncomingRequest;
use App\Models\RefDivision;
use App\Models\RefIncomingRequestCategory;
use App\Models\RefStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

#[Title('Incoming Requests')]
class Requests extends Component
{
    use WithPagination, WithFileUploads;

    public $page = 'incoming requests'; // For recent-forwards-directive
    public $editMode;
    public $search,
        $filter_start_date,
        $filter_end_date;
    public $incomingRequestId;
    public $selected_divisions = [], // for forwarded
        $forwarded_divisions = [];
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

    public function updatedSearch()
    {
        $this->resetPage();
    }

    #[On('filter')]
    public function filter($start_date, $end_date)
    {
        $this->filter_start_date = $start_date;
        $this->filter_end_date = $end_date;
    }

    #[On('clear-filter-data')]
    public function clear()
    {
        $this->reset();
        $this->resetValidation();
        $this->dispatch('reset-files');
        $this->dispatch('reset-division-select');
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
                'divisions' => $this->loadDivisions(), // Division dropdown
                'recent_forwards' => $this->loadRecentForwards(),
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
            ->latest()
            ->paginate(10);
    }

    /**
     * loadRecentForwards
     * * Returns the last 10 forwarded requests to our directive file.
     * path: livewire.directives.recent-forwards-directive
     */
    public function loadRecentForwards()
    {
        return Forwarded::query()
            ->Requests()
            ->latest()
            ->take(5)
            ->get();
    }

    public function loadIncomingRequestCategories()
    {
        return RefIncomingRequestCategory::all();
    }

    public function loadStatus()
    {
        return RefStatus::incoming()
            ->get();
    }

    public function loadDivisions()
    {
        return RefDivision::where('office_id', auth()->user()->roles()->first()->id)
            ->get()
            ->map(function ($division) {
                return [
                    'value' => $division->id,
                    'label' => $division->name
                ];
            });
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
                        'ref_status_id' => $this->ref_status_id ?? '1', //! Default value set in the database is not working. - Set to pending.
                        'remarks' => $this->remarks,
                        'office_id' => auth()->user()->roles()->first()->id
                    ]
                );

                // save files
                $this->saveFiles($incomingRequest);

                $this->clear();
                $this->dispatch('hide-incoming-request-modal');
                $this->dispatch('success', message: 'Incoming Request successfully saved.');
            });
        } catch (\Throwable $th) {
            // throw $th;
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

    public function editIncomingRequest(IncomingRequest $incomingRequest)
    {
        try {
            // Mark all forwarded requests to this division as opened
            $incomingRequest->forwards()
                ->where('ref_division_id', auth()->user()->user_metadata->ref_division_id)
                ->update([
                    'is_opened' => true
                ]);

            // Check if all divisions have opened their copies
            $this->checkAllDivisionsOpened($incomingRequest);

            $this->editMode = true;
            $this->incomingRequestId = $incomingRequest->id;

            $this->no = $incomingRequest->no;
            $this->office_barangay_organization = $incomingRequest->office_barangay_organization;
            $this->date_requested = $incomingRequest->date_requested;
            $this->ref_incoming_request_category_id = $incomingRequest->ref_incoming_request_category_id;
            $this->date_time = $incomingRequest->date_time;
            $this->contact_person_name = $incomingRequest->contact_person_name;
            $this->contact_person_number = $incomingRequest->contact_person_number;
            $this->description = $incomingRequest->description;
            $this->ref_status_id = $incomingRequest->ref_status_id;

            //* Hide it so that other divisions won't see it. Remarks inputted can only be seen inside activity log modal.
            //// $this->remarks = $incomingRequest->remarks; 

            $this->preview_file = $incomingRequest->files;

            $this->dispatch('show-incoming-request-modal');
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    protected function checkAllDivisionsOpened(IncomingRequest $incomingRequest)
    {
        /**
         * if (auth()->user()->user_metadata->ref_division_id != null)
         * Users assigned as the office admin are not assigned with ref_division_id and ref_position_id.
         * Because it doesn't make sense to have an assigned division if the user is an office admin.
         * * In this dynamic DMS, we have division admin that can manipulate forwarded requests, documents, etc.
         * Since the system is always checking for opened forwarded requests, documents, etc., we constantly update its status if all divisions that forwarded the request, documents, etc. are opened.
         * * We skip the automatic status update for office admins.
         */
        if (auth()->user()->user_metadata->ref_division_id != null) {
            // Get all forwarded requests for current division
            // $divisionForwards = $incomingRequest->forwards()
            //     ->where('ref_division_id', auth()->user()->user_metadata->ref_division_id)
            //     ->get();

            // Check if any forwarded document is already opened by this division
            // if ($divisionForwards->where('is_opened', true)->isNotEmpty()) {
            //     $this->dispatch('error', message: 'This request is already being processed by your division.');
            //     return;
            // }

            /**
             * if ($incomingRequest->ref_status_id == RefStatus::where('name', 'forwarded')->first()->id)
             * * We update the status to "received" if all divisions have opened their forwarded documents.
             * Only update status when the status is "forwarded".
             */
            if ($incomingRequest->ref_status_id == RefStatus::where('name', 'forwarded')->first()->id) {
                $unopenedForwards = $incomingRequest->forwards()
                    ->where('is_opened', false)
                    ->exists();

                if (!$unopenedForwards) {
                    $incomingRequest->update([
                        'ref_status_id' => RefStatus::where('name', 'received')->first()->id
                    ]);

                    // $this->dispatch('error', message: 'All divisions have opened this request.');
                }
            }
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
            // Step 1: Get all file IDs related to this IncomingRequest
            $fileIds = File::where('fileable_type', IncomingRequest::class)
                ->where('fileable_id', $id)
                ->pluck('id');

            // Step 2: Fetch IncomingRequest activity
            $incomingRequestLogs = Activity::where('subject_type', IncomingRequest::class)
                ->where('log_name', 'incoming_request')
                ->whereNot('event', 'created')
                ->where('subject_id', $id)
                ->with(['causer.user_metadata.division'])
                ->get();

            // Step 3: Fetch File activity logs
            $fileLogs = Activity::where('subject_type', File::class)
                ->whereIn('subject_id', $fileIds)
                ->with(['causer.user_metadata.division'])
                ->get();

            // Step 4: Combine and sort by created_at DESC
            $this->activity_log = $incomingRequestLogs->merge($fileLogs)
                ->sortByDesc('created_at')
                ->values()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'file_log_description' => $activity->description, // File activity log
                        'causer' => $activity->causer?->name ?? 'System',
                        'division' => $activity->causer?->user_metadata?->division?->name
                            ? '[' . $activity->causer->user_metadata->division->name . ']'
                            : '',
                        'created_at' => Carbon::parse($activity->created_at)->format('M d, Y h:i A'),
                        'changes' => collect($activity->properties['attributes'] ?? [])
                            ->except(['id', 'created_at', 'updated_at', 'deleted_at'])
                            ->map(function ($newValue, $key) use ($activity) {
                                $oldValue = $activity->properties['old'][$key] ?? 'N/A';

                                $fieldName = match ($key) {
                                    'file_id' => 'Files',
                                    'ref_status_id' => 'Status',
                                    'ref_incoming_request_category_id' => 'Category',
                                    'office_barangay_organization' => 'Office/Brgy/Org',
                                    'ref_division_id' => 'Division',
                                    'is_opened' => 'Opened',
                                    default => ucfirst(str_replace('_', ' ', $key))
                                };

                                if (in_array($key, ['date_requested', 'deleted_at'])) {
                                    $oldValue = $oldValue !== 'N/A' ? Carbon::parse($oldValue)->format('M d, Y') : 'N/A';
                                    $newValue = $newValue !== 'N/A' ? Carbon::parse($newValue)->format('M d, Y') : 'N/A';
                                }

                                if ($key === 'date_time') {
                                    $oldValue = $oldValue !== 'N/A' ? Carbon::parse($oldValue)->format('M d, Y h:i A') : 'N/A';
                                    $newValue = $newValue !== 'N/A' ? Carbon::parse($newValue)->format('M d, Y h:i A') : 'N/A';
                                }

                                if ($key === 'ref_incoming_request_category_id') {
                                    $oldValue = $oldValue !== 'N/A' ? RefIncomingRequestCategory::find($oldValue)?->incoming_request_category_name : 'N/A';
                                    $newValue = $newValue !== 'N/A' ? RefIncomingRequestCategory::find($newValue)?->incoming_request_category_name : 'N/A';
                                }

                                if ($key === "ref_status_id") {
                                    $oldValue = $oldValue !== 'N/A' ? RefStatus::find($oldValue)?->name : 'N/A';
                                    $newValue = $newValue !== 'N/A' ? RefStatus::find($newValue)?->name : 'N/A';
                                }

                                if ($key === "ref_division_id") {
                                    $oldValue = $oldValue !== 'N/A' ? RefDivision::find($oldValue)?->name : 'N/A';
                                    $newValue = $newValue !== 'N/A' ? RefDivision::find($newValue)?->name : 'N/A';
                                }

                                if ($key === "is_opened") {
                                    $oldValue = $oldValue !== 'N/A' ? ($oldValue ? 'Yes' : 'No') : 'N/A';
                                    $newValue = $newValue !== 'N/A' ? ($newValue ? 'Yes' : 'No') : 'N/A';
                                }

                                if ($key === 'file_id') {
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
                                    'field' => $fieldName,
                                    'old' => $oldValue,
                                    'new' => $newValue,
                                ];
                            })
                            ->values()
                            ->toArray()
                    ];
                });

            // Forwarded division logs (no change)
            $this->forwarded_divisions = Forwarded::where('forwardable_type', IncomingRequest::class)
                ->where('forwardable_id', $id)
                ->with(['division'])
                ->latest()
                ->get()
                ->map(fn($forward) => [
                    'division_name' => $forward->division?->name ?? 'N/A',
                ]);

            $this->dispatch('show-activity-log-modal');
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function forward()
    {
        $this->validate([
            'selected_divisions' => 'required|min:1',
            'selected_divisions.*' => 'exists:ref_divisions,id',
        ], [], [
            'selected_divisions' => 'division'
        ]);

        try {
            $incomingRequest = IncomingRequest::find($this->incomingRequestId);

            foreach ($this->selected_divisions as $division) {
                $incomingRequest->forwards()->create([
                    'ref_division_id' => $division,
                ]);
            }

            $incomingRequest->update([
                'ref_status_id' => RefStatus::where('name', 'forwarded')->first()->id,
            ]);

            $this->clear();
            $this->dispatch('hide-forward-modal');
            $this->dispatch('success', message: 'Request forwarded successfully.');
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
