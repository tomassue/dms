<?php

namespace App\Livewire\Shared\Incoming;

use App\Models\File;
use App\Models\Forwarded;
use App\Models\IncomingRequest;
use App\Models\NumberMessage;
use App\Models\RefDivision;
use App\Models\RefIncomingRequestCategory;
use App\Models\RefStatus;
use App\Models\SmsSender;
use App\Models\UserMetadata;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;
// Teodz
use App\Models\FilesDirectory;
use App\Models\RefDocumentType;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Fpdi\PdfParser\StreamReader;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File as FileFacade;
use setasign\Fpdi\PdfParser\PdfParserException; // Added for specific error handling


#[Title('Incoming Requests')]
class Requests extends Component
{
    use WithPagination, WithFileUploads;

    public $page = 'incoming requests'; // For recent-forwards-directive
    public $editMode;
    public $search,
        $filter_start_date,
        $filter_end_date,
        $filter_category,
        $filter_document_type,
        $filter_status;
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
        $comment,
        $category_no,
        $contact_person_email,
        $location,
        $memo_no,
        $user_id,
        $ref_document_type_id,
        $files = []; // for file upload - MorphMany

    /* ------------------------------- end::fields ------------------------------ */
    //--TEODZ
        public $is_custom,
            $assignThis,
            $tempID;
            
        public $sortField = 'date_requested'; // Default sort field
        public $sortDirection = 'desc';

    public function rules()
    {
        return [
            'no' => 'required|unique:incoming_requests,no,' . $this->incomingRequestId,
            'office_barangay_organization' => 'required',
            'date_requested' => 'nullable',
            'ref_incoming_request_category_id' => 'required|exists:ref_incoming_request_categories,id',
            'date_time' => 'nullable',
            'contact_person_name' => 'nullable',
            'contact_person_number' => 'nullable',
            'description' => 'nullable',
            'category_no' => 'string|nullable', // Additional Teodz
            'contact_person_email' => 'string|nullable',
            'location' => 'string|nullable',
            'memo_no' => 'string|nullable',
            //'files.*' => 'nullable|mimes:pdf,jpg,jpeg,png|max:10240', 
            'files.*' => 'nullable|mimes:pdf|max:81920', 
            'ref_document_type_id' => 'nullable|exists:ref_document_type,id',
            'comment' => 'nullable',
        ];
    }

    public function mount()
    {
        $now = Carbon::now();
        $this->date_requested = $now->format('Y-m-d'); 
        $this->date_time = $now->format('Y-m-d\TH:i');
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
    public function filter($start_date, $end_date, $request_category, $doctype, $status)
    {
        $this->filter_start_date = $start_date;
        $this->filter_end_date = $end_date;
        $this->filter_category = $request_category;
        $this->filter_document_type = $doctype;
        $this->filter_status = $status;
    }

    #[On('clear-filter-data')]
    public function clear()
    {
        $this->reset();
        $this->reset('files');
        $this->resetValidation();
        $this->dispatch('reset-files');
        $this->dispatch('reset-division-select');
    }

    public function generateReferenceNo()
    {
        $this->no = IncomingRequest::generateUniqueReference('INCR-', 8); // Pre-generate reference number to show in the input field (disabled).
    }
    public function categoryNo()
    {
        $this->category_no = IncomingRequest::latest('category_no');
    }

    public function render()
    {
        return view(
            'livewire.shared.incoming.requests',
            [
                'incoming_requests' => $this->loadIncomingRequests(),
                // 'sss' => $this->loadRoleCustom(),
                'incoming_request_categories' => $this->loadIncomingRequestCategories(), // Incoming Request Category dropdown
                'status' => $this->loadStatus(), // Status dropdown
                'divisions' => $this->loadDivisions(), // Division dropdown
                'recent_forwards' => $this->loadRecentForwards(),
                'document_type' => $this->loadDocumentType(), // Incoming Request Category dropdown
            ]
        );
        
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function loadIncomingRequests()
{   
    return IncomingRequest::query()
        // Added eager loading for forwards and divisions to support your Blade column update
        ->with(['status', 'category', 'username', 'forwards.division']) 
        ->when($this->search, function ($query) {
            $query->where(function ($q){
            $q->where('no', 'like', '%' . $this->search . '%')
                ->orWhere('office_barangay_organization', 'like', '%' . $this->search . '%')
                ->orWhere('category_no', 'like', '%' . $this->search . '%')
                ->orWhere('memo_no', 'like', '%' . $this->search . '%')
                ->orWhere('description', 'like', '%' . $this->search . '%')
                ->orWhere('contact_person_name', 'like', '%' . $this->search . '%');
                // ->orWhereHas('category', function ($q) {
                //     $q->where('incoming_request_category_name', 'like', '%' . $this->search . '%');
                // });
            });
        })
        ->when($this->filter_start_date && $this->filter_end_date, function ($query) {
            $query->whereBetween('date_time', [
                Carbon::parse($this->filter_start_date)->startOfDay(),
                Carbon::parse($this->filter_end_date)->endOfDay()
            ]);
        })
        ->when($this->filter_category, function ($query) {
            $query->where('ref_incoming_request_category_id', $this->filter_category);
        })
        ->when($this->filter_document_type, function ($query) {
            $query->where('ref_document_type_id', $this->filter_document_type);
        })
        ->when($this->filter_status, function ($query) {
            $query->where('ref_status_id', $this->filter_status);
        })
        ->orderBy($this->sortField, $this->sortDirection)
        ->orderBy('id', 'desc')
        // ->latest()
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

    public function loadDocumentType()
    {
        return RefDocumentType::orderBy('document_name', 'asc')->get();
    }

    public function loadStatus()
    {
        // return RefStatus::incoming()
        //     ->get();
            
        return RefStatus::get();
    }

    public function loadDivisions()
    {
       // dd(auth()->user()->roles()->first()->id);
        return RefDivision::where('office_id', auth()->user()->roles()->first()->id)
            ->get()
            ->map(function ($division) {
                return [
                    'value' => $division->id,
                    'label' => $division->name
                ];
            });
    }

    // Assuming this code is within a Livewire component or a class with $this-> files (array of uploaded files)
    // and an IncomingRequest model relationship is NOT being used for files.
    public function saveIncomingRequest()
    {
        $this->validate($this->rules(), [], $this->attributes());
        
        $padded_category_no = str_pad($this->category_no, 3, '0', STR_PAD_LEFT);
        // Parse the date to extract Month and Year
        $requestedDate = \Carbon\Carbon::parse($this->date_requested);

         // 2. Check if this specific combination already exists
        $query = IncomingRequest::where('ref_incoming_request_category_id', $this->ref_incoming_request_category_id)
                                ->where('category_no', $padded_category_no) // Changed from ref_document_type_id to category_no
                                ->whereMonth('date_requested', $requestedDate->month)
                                ->whereYear('date_requested', $requestedDate->year);
                                
        // 3. If editing, don't count the current record as a duplicate
        if ($this->incomingRequestId) {
            $query->where('id', '!=', $this->incomingRequestId);
        }

        $exists = $query->exists();

        if ($exists) {
            // Fetch the category name for a clearer error message
            $category = RefIncomingRequestCategory::find($this->ref_incoming_request_category_id);
            
            // Dispatch the "pop-up" error message
            $this->dispatch('error', message: "{$category->incoming_request_category_name}-{$padded_category_no} already exists ");
            return; // Stop the execution here so it doesn't save
        }

        try {
            DB::transaction(function () use ($padded_category_no) {
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
                        'office_id' => auth()->user()->roles()->first()->id,
                        'category_no' => $padded_category_no,
                        'contact_person_email' => $this->contact_person_email,
                        'location' => $this->location,
                        'memo_no' => $this->memo_no,
                        'ref_document_type_id' => $this->ref_document_type_id,
                        'comment' => $this->comment,
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
        if (empty($this->files)) return null;

        $uploadedFiles = [];
        $storageDisk = 'public'; 
        $storagePath = 'incoming_requests_files'; 

        foreach ((array)$this->files as $file) {
            // 💡 CRITICAL FIX: Ensure $file is a valid Livewire TemporaryUploadedFile object.
            // If the upload failed or the array contains stale/invalid data, this check prevents the error.
            if (empty($file) || !($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)) {
                continue; // Skip this invalid item and move to the next.
            }

            // 1. Get the original filename and extension separately
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $timestamp = now()->format('YmdHis');

            // 2. Combine them: Name + Underscore + Timestamp + Dot + Extension
            $newFileName = $originalName . '_' . $timestamp . '.' . $extension;

            // 3. Store the file with the new name
            $filePath = $file->storeAs($storagePath, $newFileName, $storageDisk);
            
            // 4. Create the File model record with the path
            $uploadedFiles[] = $model->files()->create([
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'type' => $file->getMimeType(),
                'file_path' => $filePath, 
                'disk' => $storageDisk,    
            ]);
        }

        // 3. IMPORTANT: Clear the uploaded files from the Livewire property after saving
        $this->files = []; 

        return $uploadedFiles;
    }
    
    public function editIncomingRequest(IncomingRequest $incomingRequest)
    {
        //dd($incomingRequest);
        try {
            if (!Auth::user()->hasRole('Super Admin')) {
                // Mark all forwarded requests to this division as opened
                $incomingRequest->forwards()
                    ->where('ref_division_id', auth()->user()->user_metadata->ref_division_id)
                    ->update([
                        'is_opened' => true
                    ]);

                // Log the activity of opening the request
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($incomingRequest) // Equivalent to setting subject_type & subject_id manually
                    ->useLog('incoming_request')
                    ->event('updated')
                    ->withProperties(['is_opened' => true])
                    ->log('Opened incoming request ' . ($incomingRequest->no ?? '') . ': ' . (auth()->user()?->user_metadata?->division?->name ?? 'System'));

                // Check if all divisions have opened their copies
                $this->checkAllDivisionsOpened($incomingRequest);
            }
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
            $this->category_no = $incomingRequest->category_no; //Teodz
            $this->contact_person_email = $incomingRequest->contact_person_email;
            $this->location = $incomingRequest->location;
            $this->memo_no = $incomingRequest->memo_no;
            $this->ref_document_type_id = $incomingRequest->ref_document_type_id;
            $this->remarks = $incomingRequest->remarks; 
            $this->comment = $incomingRequest->comment; 

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
        // 1. Get the file record (assuming your File model now has a 'file_path' and 'disk' column)
        $file = File::findOrFail($fileId);
        
        // 2. Check if the file exists in storage
        if (!Storage::disk($file->disk)->exists($file->file_path)) {
            $this->dispatch('error', message: 'File not found in storage.');
            return;
        }

        // 3. Generate a temporary signed URL for the file path
        $signedURL = URL::temporarySignedRoute(
            'file.view.disk', // Note: This route name must be a custom one you define in web.php
            now()->addMinutes(10),
            ['path' => $file->file_path, 'disk' => $file->disk] 
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
                ->whereIn('log_name', ['incoming_request', 'forwarded'])
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
            // $this->forwarded_divisions = Forwarded::where('forwardable_type', IncomingRequest::class)
            //     ->where('forwardable_id', $id)
            //     ->with(['division'])
            //     ->latest()
            //     ->get()
            //     ->map(fn($forward) => [
            //         'division_name' => $forward->division?->name ?? 'N/A',
            //     ]);

            $this->dispatch('show-activity-log-modal');
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    /**
     * getForwardedDivisions
     * * This function is used to get the forwarded divisions of the incoming document.
     * * It will return the forwarded divisions of the incoming document.
     */
    public function getForwardedDivisions(IncomingRequest $incomingRequest)
    {
        try {
            $forwarded_divisions = $incomingRequest->forwards()
                ->with(['division'])
                ->get()
                ->map(function ($forward) {
                    return $forward->ref_division_id;
                })
                ->toArray();

            if ($forwarded_divisions) {
                $this->dispatch('set-division-select', $forwarded_divisions);
            }
        } catch (\Throwable $th) {
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

            /* ------------------------- CITY VETERINARY OFFICE ------------------------- */
            if (Auth::user()->hasRole('CITY VETERINARY OFFICE')) {
                /**
                 * In CVO, we have a customed function to send an SMS to the selected divisions.
                 * We updated user_metadata and added phone_number column.
                 * Now we can use the phone_number column to send an SMS to the selected divisions.
                 */
                $phoneNumbers = UserMetadata::whereIn('ref_division_id', (array) $this->selected_divisions)
                    ->pluck('phone_number')
                    ->filter() // optional: remove null values
                    ->unique() // optional: remove duplicates
                    ->values(); // reindex if needed;

                foreach ($phoneNumbers as $phoneNumber) {
                    /**
                     * We enclosed the SMS sending code in a try-catch block to handle any exceptions that might occur during the SMS sending process.
                     * If an exception occurs, we will log the error message and continue to the next iteration of the loop.
                     * This allows us to send the SMS to the next phone number without stopping the entire process.
                     */
                    try {
                        $message = "APO-DMS NOTIFICATION\n\n" .
                            "An incoming request with a reference no. of " . $incomingRequest->no . " and a description of " . $incomingRequest->description . ", " .
                            " has been forwarded.\n\n" .
                            "This is a system-generated message. DO NOT REPLY.";

                        SmsSender::create([
                            'trans_id' => time() . '-' . mt_rand(),
                            'received_id' => 'CVO-DMS-NOTIFICATION',
                            'recipient' => $phoneNumber,
                            'reciepient_name' => 'CVO',
                            'recipient_message' => $message
                        ]);

                        $userIds = UserMetadata::where('phone_number', $phoneNumber)->pluck('user_id');

                        NumberMessage::create([
                            'user_id' => $userIds[0],
                            'phone_number' => $phoneNumber,
                            'sms_trans_id' => time() . '-' . mt_rand(),
                            'otp_type' => 'CVO-DMS-NOTIFICATION',
                            'sms_status' => 'STATUS',
                        ]);
                    } catch (\Throwable $th) {
                        // log or ignore to keep processing
                        FacadesLog::error('SMS failed for phone: ' . $phoneNumber . ', Error: ' . $th->getMessage());
                        continue;
                    }
                }
                //* After it being sent, we will them save them to forwarded table.
            }
            /* ------------------------- CITY VETERINARY OFFICE ------------------------- */

            // foreach ($this->selected_divisions as $division) {
            //     $incomingRequest->forwards()->create([
            //         'ref_division_id' => $division,
            //     ]);
            // }

            // Get current forwarded division IDs, including soft-deleted
            $currentForwarded = $incomingRequest->forwards()->withTrashed()->pluck('ref_division_id');

            // Convert to collections for easier diffing
            $selected = collect($this->selected_divisions)->map(fn($id) => (int)$id);

            // Soft-delete divisions that are no longer selected
            $toSoftDelete = $currentForwarded->diff($selected);
            if ($toSoftDelete->isNotEmpty()) {
                $incomingRequest->forwards()->whereIn('ref_division_id', $toSoftDelete)->delete();
            }

            // Restore soft-deleted if re-selected
            $toRestore = $selected->intersect($currentForwarded);
            if ($toRestore->isNotEmpty()) {
                $incomingRequest->forwards()->withTrashed()
                    ->whereIn('ref_division_id', $toRestore)
                    ->whereNotNull('deleted_at')
                    ->restore();
            }

            // Create new forwards for divisions not yet in the DB
            $toAdd = $selected->diff($currentForwarded);
            foreach ($toAdd as $divisionId) {
                $incomingRequest->forwards()->create([
                    'ref_division_id' => $divisionId,
                ]);
            }

            $incomingRequest->update([
                'ref_status_id' => RefStatus::where('name', 'forwarded')->first()->id,
            ]);

            // Log the forwarding action - a central log per document.
            // Get the division names based on each action
            $addedNames = RefDivision::whereIn('id', $toAdd)->pluck('name')->toArray();
            $restoredNames = RefDivision::whereIn('id', $toRestore)->pluck('name')->toArray();
            $deletedNames = RefDivision::whereIn('id', $toSoftDelete)->pluck('name')->toArray();

            $logMessages = [];

            if (!empty($addedNames)) {
                $logMessages[] = 'added: ' . implode(', ', $addedNames);
            }

            if (!empty($restoredNames)) {
                $logMessages[] = 'restored: ' . implode(', ', $restoredNames);
            }

            if (!empty($deletedNames)) {
                $logMessages[] = 'removed: ' . implode(', ', $deletedNames);
            }

            $finalLogMessage = auth()->user()->name . ' updated forwarded divisions - ' . implode(' | ', $logMessages) . '.';

            activity()
                ->causedBy(auth()->user())
                ->performedOn($incomingRequest) // Equivalent to setting subject_type & subject_id manually
                ->useLog('forwarded')
                ->event('updated')
                ->log($finalLogMessage);

            $this->clear();
            $this->dispatch('hide-forward-modal');
            $this->dispatch('success', message: 'Request forwarded successfully.');
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function viewIncomingRequest(IncomingRequest $incomingRequest)
    {
        try {
            $this->no = $incomingRequest->no;

            $this->forwarded_divisions = Forwarded::where('forwardable_type', IncomingRequest::class)
                ->where('forwardable_id', $incomingRequest->id)
                ->with(['division']) // Assuming 'division' is a relationship
                ->latest()
                ->get()
                ->map(function ($forward) {
                    return [
                        'division_name' => $forward->division?->name ?? 'N/A',
                    ];
                });

            $this->office_barangay_organization = $incomingRequest->office_barangay_organization;
            $this->date_requested = $incomingRequest->formatted_date_requested;
            $this->ref_incoming_request_category_id = $incomingRequest->category->incoming_request_category_name;
            $this->date_time = $incomingRequest->formatted_date_time;
            $this->contact_person_name = $incomingRequest->contact_person_name;
            $this->contact_person_number = $incomingRequest->contact_person_number;
            $this->description = $incomingRequest->description;
            $this->ref_status_id = $incomingRequest->status->name;
            $this->remarks = $incomingRequest->remarks;
            $this->category_no = $incomingRequest->category_no; //Teodz
            $this->contact_person_email = $incomingRequest->contact_person_email;
            $this->location = $incomingRequest->location;
            $this->memo_no = $incomingRequest->memo_no;
            $this->ref_document_type_id = $incomingRequest->ref_document_type_id;
            $this->comment = $incomingRequest->comment;

            $this->preview_file = $incomingRequest->files;

            $this->dispatch('show-details-modal');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    //--------------------------------------
    public function loadRoleCustom(){
        
        $this->is_custom = auth()->user()->roles()->first()->id;
    }

    public function showAssignRequest(IncomingRequest $incomingRequest)
    {
        // dd($incomingRequest);
        try {
            $this->tempID = $incomingRequest->id;
            $this->no = $incomingRequest->no;
            $this->office_barangay_organization = $incomingRequest->office_barangay_organization;
            $this->ref_incoming_request_category_id = $incomingRequest->category->incoming_request_category_name;
            $this->category_no = $incomingRequest->category_no;
            $this->memo_no = $incomingRequest->memo_no;
            $this->user_id = $incomingRequest->username->name;

            if($incomingRequest->user_id){
                $this->assignThis = true;
            }else{
                $this->assignThis = false;
            }

            $this->dispatch('show-assign-modal');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('show-assign-modal');
            //$this->dispatch('error', message: 'Something went wrong.');
        }
        // $this->dispatch('show-assign-modal');
        //dd('gfgfg');
        // $incomingAssignRequest = IncomingRequest::updateOrCreate(
        //     [
        //         'user_id' => $userID,
        //     ]
        // );
        // // save files
        // $this->saveFiles($incomingAssignRequest);
        // $this->preview_file = $this->preview_file->filter(fn($f) => $f->id != $userID);
        // $this->dispatch('success', message: 'Assign Successfully.');
    }
    
    public function setAssignRequest($tempID)
    {
        // The tempID should be validated to ensure it's a valid ID before proceeding
        // $this->validate($this->rules(), [], $this->attributes()); 

        try {
            // Pass $tempID into the closure using the 'use' keyword
            DB::transaction(function () use ($tempID) { 
                
                // 1. Find the existing IncomingRequest record by its ID.
                $incomingRequest = IncomingRequest::find($tempID);

                // Check if the request was found
                if ($incomingRequest) {
                    // 2. Update ONLY the user_id column with the current authenticated user's ID
                    $incomingRequest->update([
                        'user_id' => Auth::user()->id,
                    ]);
                }
                
                // Dispatch events regardless of whether saveFiles was called
                $this->dispatch('hide-assign-modal');
                $this->dispatch('success', message: 'Successfully assign.');
            });
        } catch (\Throwable $th) {
            // throw $th; // Uncomment for debugging
            $this->dispatch('error', message: 'Something went wrong. Refresh the Browser.');
        }
    }

    public function removeUploadedFile($fileId)
    {
        try {
            // Find the file record
            $file = File::findOrFail($fileId);
            
            // 1. Delete from storage (check if it exists first)
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }

            // 2. Delete the database record
            $file->delete();

            // 3. Update the preview list to reflect the removal without full page reload
            // We use filter() to remove the deleted file from the local Livewire property
            $this->preview_file = $this->preview_file->filter(fn($f) => $f->id != $fileId);

            $this->dispatch('success', message: 'File removed successfully.');

        } catch (\Exception $e) {
            FacadesLog::error("Error removing uploaded file ID $fileId: " . $e->getMessage());
            $this->dispatch('error', message: 'Failed to remove file. Please check logs.');
        }
    }

    public function downloadMergedAttachments()
    {
        $filesToMerge = $this->preview_file;

        if ($filesToMerge->isEmpty()) {
            $this->dispatch('error', message: 'No files attached to merge.');
            return;
        }
        
        // --- MODIFICATION START: REVERSE ORDER ---
        // Reverse the collection so that the latest (most recently attached) files
        // are processed first, making them appear at the beginning of the merged PDF.
        $filesToMerge = $filesToMerge->reverse();
        // --- MODIFICATION END: REVERSE ORDER ---

        // --- UPDATED FILENAME GENERATION ---
        $categoryName = $this->ref_incoming_request_category_id ?? 'Unknown-Category';
        $categoryNo = $this->category_no ?? 'N-A';
        $memoNo = $this->memo_no ?? 'N-A';
        $dateTime = now()->format('YmdHis');

        // Sanitize category name: replace non-alphanumeric/spaces with hyphen
        $sanitizedCategory = preg_replace('/[^A-Za-z0-9\s-]+/', '', $categoryName);
        $sanitizedCategory = trim(preg_replace('/\s+/', '-', $sanitizedCategory), '-');
        
        $mergedFilename = sprintf(
            '%s-%s_%s_%s.pdf',
            $sanitizedCategory,
            $categoryNo,
            $memoNo,
            $dateTime
        );
        // --- END UPDATED FILENAME GENERATION ---

        
        // Use storage_path() for the temporary directory
        $tempAppDir = storage_path('app/temp/pdf_merger');
        if (!FileFacade::isDirectory($tempAppDir)) {
            if (!FileFacade::makeDirectory($tempAppDir, 0777, true)) {
                FacadesLog::error("Failed to create temporary directory: " . $tempAppDir);
                $this->dispatch('error', message: 'Failed to create temp directory for merging.');
                return;
            }
        }
        
        // Define path for the final merged PDF
        $tempMergedFilePath = $tempAppDir . '/' . $mergedFilename;

        $pdf = new Fpdi('P', 'mm', 'A4');
        $tempFilesToCleanup = [];

        try {
            // --- 1. Iterate and Prepare Files for Merging ---
            foreach ($filesToMerge as $file) {
                
                FacadesLog::info("Attempting to process file ID: " . ($file->id ?? 'N/A') . " with path: " . ($file->file_path ?? 'NULL'));

                if (empty($file->file_path)) {
                    FacadesLog::warning("Skipping file merge because 'file_path' is empty/null.");
                    continue; 
                }
                
                // Get the absolute path using the 'public' disk
                $fullPath = Storage::disk('public')->path($file->file_path);
                
                if (!file_exists($fullPath)) {
                    FacadesLog::warning("Skipping file merge: File not found on disk at path: " . $fullPath);
                    continue; 
                }

                $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
                $fileToMerge = $fullPath;
                $shouldMerge = false;

                // --- Type Check and Conversion (Images to PDF) ---
                $mimeType = mime_content_type($fullPath);

                if (strtolower($extension) === 'pdf' || strtolower($mimeType) === 'application/pdf') {
                    $shouldMerge = true;
                } elseif (in_array(strtolower($extension), ['jpg', 'jpeg', 'png']) && 
                            in_array(strtolower($mimeType), ['image/jpeg', 'image/png'])) {
                    
                    // Image Validation
                    $image_info = @getimagesize($fullPath); 
                    if ($image_info === false || ($image_info[0] ?? 0) <= 0 || ($image_info[1] ?? 0) <= 0) {
                        FacadesLog::error("SKIPPING INVALID IMAGE: Image is corrupt or dimensions are zero. Path: " . $fullPath);
                        $this->dispatch('warning', message: 'Skipping corrupted image file(s).');
                        continue; 
                    }

                    // Image Conversion: Create a temporary PDF from the image
                    $tempPdfPath = $tempAppDir . '/' . uniqid() . '.pdf'; 
                    $tempPdf = new Fpdi('P', 'mm', 'A4');
                    $tempPdf->AddPage();
                    // Add image scaled down to fit A4 width, maintaining aspect ratio (0 for height)
                    $tempPdf->Image($fullPath, 10, 10, $tempPdf->GetPageWidth() - 20, 0); 
                    $tempPdf->Output($tempPdfPath, 'F'); 

                    $fileToMerge = $tempPdfPath;
                    $tempFilesToCleanup[] = $tempPdfPath; // Mark for cleanup
                    $shouldMerge = true;
                } else {
                    FacadesLog::warning("Skipping unsupported file type: " . $fullPath);
                    continue;
                }

                // --- 4. Merge the Prepared File (Now guaranteed to be a PDF) ---
                if ($shouldMerge) {
                    try {
                        // ** Catch the specific FPDI error for unsupported compression **
                        $pageCount = $pdf->setSourceFile($fileToMerge);
                        
                        for ($i = 1; $i <= $pageCount; $i++) {
                            
                            $templateId = $pdf->importPage($i);
                            $size = $pdf->getTemplateSize($templateId);
                            
                            // Determine orientation
                            $orientation = ($size['rotation'] ?? 0) === 90 || ($size['rotation'] ?? 0) === 270 ? 'L' : 'P';
                            
                            // Add a new page matching the imported page's dimensions
                            $pdf->AddPage($orientation, [$size['width'], $size['height']]);

                            // Import the content
                            $pdf->useTemplate($templateId); 
                        }
                    } catch (PdfParserException $e) {
                        // Catches specific FPDI parsing errors (like unsupported compression)
                        FacadesLog::error("SKIPPING CORRUPT PDF: FPDI failed to process file ID " . ($file->id ?? 'N/A') . ". Error: " . $e->getMessage());
                        $this->dispatch('warning', message: 'Skipping one corrupt attachment due to unsupported format/compression.');
                    } catch (\Throwable $e) {
                        // Catches any other general error during PDF merging/import
                        FacadesLog::error("SKIPPING FILE due to general error: " . ($file->id ?? 'N/A') . ". Error: " . $e->getMessage());
                        $this->dispatch('warning', message: 'Skipping one attachment due to an unexpected merging error.');
                    }
                }
            }
            
            if ($pdf->PageNo() == 0) {
                $this->dispatch('error', message: 'No supported PDF or image files were successfully merged.');
                return;
            }

            // --- 5. Output and Stream ---
            $pdf->Output($tempMergedFilePath, 'F');
            
            // Return a Laravel download response to force the download
            return response()->download($tempMergedFilePath, $mergedFilename)
                ->deleteFileAfterSend(true);

        } catch (\Throwable $th) {
            // This catch block handles any other unexpected system error
            FacadesLog::error("SYSTEM-LEVEL PDF Merging failed: " . $th->getMessage() . " on line " . $th->getLine());
            $this->dispatch('error', message: 'System error during merging. Check logs.');
            return;

        } finally {
            // --- 6. Cleanup Temporary Files ---
            foreach ($tempFilesToCleanup as $path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }
    }


}
