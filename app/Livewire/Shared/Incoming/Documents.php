<?php

namespace App\Livewire\Shared\Incoming;

use App\Models\Apo\IncomingDocument as ApoIncomingDocument;
use App\Models\File;
use App\Models\Forwarded;
use App\Models\IncomingDocument;
use App\Models\NumberMessage;
use App\Models\RefDivision;
use App\Models\RefIncomingDocumentCategory;
use App\Models\RefStatus;
use App\Models\SmsSender;
use App\Models\UserMetadata;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;
//Teodz
use Illuminate\Support\Facades\Log as FacadesLog;
use App\Models\FilesDirectory;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Fpdi\PdfParser\StreamReader;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File as FileFacade;
use setasign\Fpdi\PdfParser\PdfParserException; // Added for specific error handling

#[Title('Incoming Documents')]
class Documents extends Component
{
    use WithPagination, WithFileUploads;

    public $page = 'incoming documents'; // For recent-forwards-directive
    public $editMode;
    public $search,
        $filter_start_date,
        $filter_end_date,
        $filter_category,
        $filter_status;
    public $incomingDocumentId;
    public $selected_divisions = [],
        $forwarded_divisions = [];
    public $preview_file = [];
    public $activity_log = [];

    /* ---------------------------- begin::Properties --------------------------- */
    public $no,
        $ref_incoming_document_category_id,
        $document_info,
        $date,
        $ref_status_id,
        $remarks,
        $category_no,
        $file_id = []; // for file upload - MorphMany
    //* APO
    public $source;
    /* ----------------------------- end::Properties ---------------------------- */

    public function rules()
    {
        $rules = [
            'no' => 'required|unique:incoming_documents,no,' . $this->incomingDocumentId,
            'ref_incoming_document_category_id' => 'required|exists:ref_incoming_documents_categories,id',
            'document_info' => 'required',
            'category_no' => 'string|nullable',
            'date' => 'required|date'
        ];

        if (auth()->user()->hasRole('APOO')) {
            $rules += [
                'source' => 'required',
            ];
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'ref_incoming_document_category_id' => 'document category',
            'file_id' => 'file',
        ];
    }

    #[On('filter')]
    public function filter($start_date, $end_date, $document_category, $status)
    {
        $this->filter_start_date = $start_date;
        $this->filter_end_date = $end_date;
        $this->filter_category = $document_category;
        $this->filter_status = $status;
    }

    #[On('clear-filter-data')]
    public function clear()
    {
        $this->reset();
        $this->resetValidation();

        $this->dispatch('reset-files');
        $this->dispatch('reset-division-select');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function generateReferenceNo()
    {
        $this->no = IncomingDocument::generateUniqueReference('INCD-', 8); // Pre-generate reference number to show in the input field (disabled).
    }

    public function loadIncomingDocuments()
    {
        return IncomingDocument::query()
            ->with('apoDocument')
            ->when($this->search, function ($query) {
                $query->where(function ($q){
                $q->where('category_no', 'like', '%' . $this->search . '%')
                    ->orWhere('document_info', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filter_start_date && $this->filter_end_date, function ($query) {
                $query->DateRangeFilter($this->filter_start_date, $this->filter_end_date);
            })
            ->when($this->filter_category, function ($query) {
                $query->where('ref_incoming_document_category_id', $this->filter_category);
            })
            ->when($this->filter_status, function ($query) {
                $query->where('ref_status_id', $this->filter_status);
            })
            ->latest()
            ->paginate(10);
    }

    public function loadRefIncomingDocumentCategory()
    {
        return RefIncomingDocumentCategory::all();
    }

    public function loadRefStatus()
    {
        // return RefStatus::incoming()
        //     ->get();
            
        return RefStatus::get();
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

    /**
     * loadRecentForwards
     * * Returns the last 10 forwarded requests to our directive file.
     * path: livewire.directives.recent-forwards-directive
     */
    public function loadRecentForwards()
    {
        return Forwarded::query()
            ->Documents()
            ->latest()
            ->take(10)
            ->get();
    }

    public function render()
    {
        return view(
            'livewire.shared.incoming.documents',
            [
                'incoming_documents' => $this->loadIncomingDocuments(),
                'incoming_documents_categories' => $this->loadRefIncomingDocumentCategory(), // Incoming Document Category dropdown
                'recent_forwards' => $this->loadRecentForwards(),
                'status' => $this->loadRefStatus(), // Status dropdown
                'divisions' => $this->loadDivisions(), // Division dropdown
            ]
        );
    }

    public function editIncomingDocument(IncomingDocument $incomingDocument)
    {
        try {
            if (!Auth::user()->hasRole('Super Admin')) {
                // Mark all forwarded documents to this division (division level) as opened
                $incomingDocument->forwards()
                    ->where('ref_division_id', auth()->user()->user_metadata->ref_division_id)
                    ->update([
                        'is_opened' => true
                    ]);

                // Log the activity of opening the document
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($incomingDocument) // Equivalent to setting subject_type & subject_id manually
                    ->useLog('incoming_document')
                    ->event('updated')
                    ->withProperties(['is_opened' => true])
                    ->log('Opened incoming document ' . ($incomingDocument->no ?? '') . ': ' . (auth()->user()?->user_metadata?->division?->name ?? 'System'));

                // Check if all divisions have opened their copies
                $this->checkAllDivisionsOpened($incomingDocument);
            }

            $this->no = $incomingDocument->no;
            $this->ref_incoming_document_category_id = $incomingDocument->ref_incoming_document_category_id;
            $this->document_info = $incomingDocument->document_info;
            $this->date = $incomingDocument->date;
            $this->ref_status_id = $incomingDocument->ref_status_id;
            $this->preview_file = $incomingDocument->files;

            //* Hide it so that other divisions won't see it. Remarks inputted can only be seen inside activity log modal.
            //// $this->remarks = $incomingDocument->remarks;

            if (auth()->user()->hasRole('APOO')) {
                $this->source = $incomingDocument->apoDocument->source ?? '';
            }

            $this->incomingDocumentId = $incomingDocument->id;
            $this->editMode = true;
            $this->dispatch('show-incoming-document-modal');
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    protected function checkAllDivisionsOpened(IncomingDocument $incomingDocument)
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
             * if (incomingDocument->ref_status_id == RefStatus::where('name', 'forwarded')->first()->id)
             * * We update the status to "received" if all divisions have opened their forwarded documents.
             * Only update status when the status is "forwarded".
             */
            if ($incomingDocument->ref_status_id == RefStatus::where('name', 'forwarded')->first()->id) {
                $unopenedForwards = $incomingDocument->forwards()
                    ->where('is_opened', false)
                    ->exists();

                if (!$unopenedForwards) {
                    $incomingDocument->update([
                        'ref_status_id' => RefStatus::where('name', 'received')->first()->id
                    ]);

                    // $this->dispatch('error', message: 'All divisions have opened this request.');
                }
            }
        }
    }

    public function saveIncomingDocument()
    {
        $this->validate($this->rules(), [], $this->attributes());

        try {
            DB::transaction(function () {
                // Save main document (without file_id in initial create)
                $incomingDocument = $this->saveMainIncomingDocument();

                // Handle file uploads polymorphically
                $this->saveFiles($incomingDocument);

                // Save APO data if applicable
                $this->saveApoIncomingDocument($incomingDocument);

                $this->clear();
                $this->dispatch('hide-incoming-document-modal');
                $this->dispatch('success', message: 'Incoming Document successfully saved with files.');
            });
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    protected function saveMainIncomingDocument()
    {
        $data = [
            'no' => $this->no,
            'ref_incoming_document_category_id' => $this->ref_incoming_document_category_id,
            'document_info' => $this->document_info,
            'date' => $this->date,
            'ref_status_id' => $this->ref_status_id ?? '1', //! Default value set in the database is not working. - Set to pending.
            'remarks' => $this->remarks,
            'category_no' => $this->category_no,
            'office_id' => auth()->user()->roles()->first()->id
        ];

        return IncomingDocument::updateOrCreate(
            ['id' => $this->incomingDocumentId ?? null],
            $data
        );
    }


    protected function saveApoIncomingDocument($incomingDocument)
    {
        if (!auth()->user()->hasRole('APOO')) return; // Return if not APO

        return ApoIncomingDocument::updateOrCreate(
            ['incoming_document_id' => $incomingDocument->id ?? null], // Update if exists. Otherwise, create
            [
                'source' => $this->source,
            ]
        );
    }

    protected function saveFiles($model)
    {
        if (empty($this->file_id)) return null;

        $uploadedFiles = [];
        $storageDisk = 'public'; 
        $storagePath = 'incoming_documents_files'; 

        foreach ((array)$this->file_id as $file) {
            // 💡 CRITICAL FIX: Ensure $file is a valid Livewire TemporaryUploadedFile object.
            // If the upload failed or the array contains stale/invalid data, this check prevents the error.
            if (empty($file) || !($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile)) {
                continue; // Skip this invalid item and move to the next.
            }

            // 1. Store the file on the disk (this handles Livewire's TemporaryUploadedFile)
            $filePath = $file->store($storagePath, $storageDisk);
            
            // 2. Create the File model record with the path
            $uploadedFiles[] = $model->files()->create([
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'type' => $file->getMimeType(),
                'file_path' => $filePath, 
                'disk' => $storageDisk,    
                // fileable_id and fileable_type are auto-set by morphMany
            ]);
        }

        return $uploadedFiles;
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
            // Step 1: Get all file IDs related to this IncomingDocument
            $fileIds = File::where('fileable_type', IncomingDocument::class)
                ->where('fileable_id', $id)
                ->pluck('id');

            // Step 2: Fetch IncomingDocument activity
            $apoIds = ApoIncomingDocument::where('incoming_document_id', $id)->pluck('id'); // Get related ApoIncomingDocument IDs

            // Step 2: Fetch IncomingDocument & ApoIncomingDocument activities
            $incomingDocumentLogs = Activity::where(function ($query) use ($id, $apoIds) {
                $query->where(function ($q) use ($id) {
                    $q->where('subject_type', IncomingDocument::class)
                        ->where('subject_id', $id);
                })->orWhere(function ($q) use ($apoIds) {
                    $q->where('subject_type', ApoIncomingDocument::class)
                        ->whereIn('subject_id', $apoIds);
                });
            })
                ->whereIn('log_name', ['incoming_document', 'apo_incoming_document', 'forwarded']) // Filter by log names
                ->where('event', '!=', 'created') // same as ->whereNot('event', 'created')
                ->with(['causer.user_metadata.division'])
                ->get();

            // Step 3: Fetch File activity logs
            $fileLogs = Activity::where('subject_type', File::class)
                ->whereIn('subject_id', $fileIds)
                ->with(['causer.user_metadata.division'])
                ->get();

            // Step 4: Combine and sort by created_at DESC
            $this->activity_log = $incomingDocumentLogs->merge($fileLogs)
                ->sortByDesc('created_at')
                ->values()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'file_log_description' => $activity->description, // File activity log
                        'causer' => $activity->causer?->name ?? 'System',
                        'division' => $activity->causer?->user_metadata?->division?->name ? '[' . $activity->causer?->user_metadata?->division?->name . ']' : '', // ✅ Access nested data
                        'created_at' => Carbon::parse($activity->created_at)->format('M d, Y h:i A'),
                        'changes' => collect($activity->properties['attributes'] ?? [])
                            ->except(['id', 'created_at', 'updated_at', 'deleted_at', 'incoming_document_id']) // Exclude
                            ->map(function ($newValue, $key) use ($activity) {
                                $oldValue = $activity->properties['old'][$key] ?? 'N/A';

                                // Custom field name mapping
                                $fieldName = match ($key) {
                                    'file_id' => 'Files',
                                    'ref_status_id' => 'Status',
                                    'ref_incoming_document_category_id' => 'Category',
                                    'document_info' => 'Info',
                                    'ref_division_id' => 'Division',
                                    'is_opened' => 'Opened',
                                    // Add other field mappings here as needed
                                    // 'another_field' => 'Friendly Name',
                                    default => ucfirst(str_replace('_', ' ', $key))
                                };

                                // Format date fields
                                if (in_array($key, ['deleted_at'])) {
                                    $oldValue = $oldValue !== 'N/A' ? Carbon::parse($oldValue)->format('M d, Y') : 'N/A';
                                    $newValue = $newValue !== 'N/A' ? Carbon::parse($newValue)->format('M d, Y') : 'N/A';
                                }

                                if ($key === 'date') {
                                    $oldValue = $oldValue !== 'N/A' ? Carbon::parse($oldValue)->format('M d, Y') : 'N/A';
                                    $newValue = $newValue !== 'N/A' ? Carbon::parse($newValue)->format('M d, Y') : 'N/A';
                                }

                                // Replace foreign keys with related names
                                if ($key === 'ref_incoming_document_category_id') {
                                    $oldValue = $oldValue !== 'N/A' ? RefIncomingDocumentCategory::find($oldValue)?->incoming_document_category_name : 'N/A';
                                    $newValue = $newValue !== 'N/A' ? RefIncomingDocumentCategory::find($newValue)?->incoming_document_category_name : 'N/A';
                                }

                                if ($key === "ref_status_id") {
                                    $oldValue = $oldValue !== 'N/A' ? RefStatus::find($oldValue)?->name : 'N/A';
                                    $newValue = $newValue !== 'N/A' ? RefStatus::find($newValue)?->name : 'N/A';
                                }

                                if ($key === "ref_division_id") {
                                    $oldValue = $oldValue !== 'N/A' ? RefDivision::find($oldValue)?->name : 'N/A';
                                    $newValue = $newValue !== 'N/A' ? RefDivision::find($newValue)?->name : 'N/A';
                                }

                                // Replace boolean values with "Yes" or "No"
                                if ($key === "is_opened") {
                                    $oldValue = $oldValue !== 'N/A' ? $oldValue ? 'Yes' : 'No' : 'N/A';
                                    $newValue = $newValue !== 'N/A' ? $newValue ? 'Yes' : 'No' : 'N/A';
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
                                    'field' => $fieldName, // Format key
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
     * getForwardedDivisions
     * * This function is used to get the forwarded divisions of the incoming document.
     * * It will return the forwarded divisions of the incoming document.
     */
    public function getForwardedDivisions(IncomingDocument $incomingDocument)
    {
        try {
            $forwarded_divisions = $incomingDocument->forwards()
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

    /**
     * forward
     * * This function is used to forward the incoming document to the selected divisions.
     * * It will validate the selected divisions and then create a new forward for each division.
     */
    public function forward()
    {
        $this->validate([
            'selected_divisions' => 'required|min:1',
            'selected_divisions.*' => 'exists:ref_divisions,id',
        ], [], [
            'selected_divisions' => 'division'
        ]);

        try {
            $incomingDocument = IncomingDocument::find($this->incomingDocumentId);

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
                    if (empty($phoneNumber)) {
                        Log::error('SMS not sent: Phone number is empty for division ID');
                        continue; // Log and skip if no phone number
                    }

                    /**
                     * We enclosed the SMS sending code in a try-catch block to handle any exceptions that might occur during the SMS sending process.
                     * If an exception occurs, we will log the error message and continue to the next iteration of the loop.
                     * This allows us to send the SMS to the next phone number without stopping the entire process.
                     */
                    try {
                        $message = "APO-DMS NOTIFICATION\n\n" .
                            "An incoming document with a reference no. of " . $incomingDocument->no . " and an info of " . $incomingDocument->document_info . ", " .
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
                        Log::error('SMS failed for phone: ' . $phoneNumber . ', Error: ' . $th->getMessage());
                        continue;
                    }
                }
                //* After it being sent, we will them save them to forwarded table.
            }
            /* ------------------------- CITY VETERINARY OFFICE ------------------------- */

            // foreach ($this->selected_divisions as $division) {
            //     $incomingDocument->forwards()->create([
            //         'ref_division_id' => $division,
            //     ]);
            // }

            // Get current forwarded division IDs, including soft-deleted
            $currentForwarded = $incomingDocument->forwards()->withTrashed()->pluck('ref_division_id');

            // Convert to collections for easier diffing
            $selected = collect($this->selected_divisions)->map(fn($id) => (int)$id);

            // Soft-delete divisions that are no longer selected
            $toSoftDelete = $currentForwarded->diff($selected);
            if ($toSoftDelete->isNotEmpty()) {
                $incomingDocument->forwards()->whereIn('ref_division_id', $toSoftDelete)->delete();
            }

            // Restore soft-deleted if re-selected
            $toRestore = $selected->intersect($currentForwarded);
            if ($toRestore->isNotEmpty()) {
                $incomingDocument->forwards()->withTrashed()
                    ->whereIn('ref_division_id', $toRestore)
                    ->whereNotNull('deleted_at')
                    ->restore();
            }

            // Create new forwards for divisions not yet in the DB
            $toAdd = $selected->diff($currentForwarded);
            foreach ($toAdd as $divisionId) {
                $incomingDocument->forwards()->create([
                    'ref_division_id' => $divisionId,
                ]);
            }

            // Update the status of the incoming document to "forwarded"
            // This is to indicate that the document has been forwarded to the selected divisions.
            $incomingDocument->update([
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
                ->performedOn($incomingDocument) // Equivalent to setting subject_type & subject_id manually
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

    public function viewIncomingDocument(IncomingDocument $incomingDocument)
    {
        try {
            $this->forwarded_divisions = Forwarded::where('forwardable_type', IncomingDocument::class)
                ->where('forwardable_id', $incomingDocument->id)
                ->with(['division']) // Assuming 'division' is a relationship
                ->latest()
                ->get()
                ->map(function ($forward) {
                    return [
                        'division_name' => $forward->division?->name ?? 'N/A',
                    ];
                });

            $this->ref_incoming_document_category_id = $incomingDocument->category->incoming_document_category_name;
            $this->document_info = $incomingDocument->document_info;
            $this->date = Carbon::parse($incomingDocument->date)->format('M d, Y');
            $this->ref_status_id = $incomingDocument->status->name;
            $this->remarks = $incomingDocument->remarks;
            $this->category_no = $incomingDocument->category_no;

            /* ----------------------------------- APO ---------------------------------- */
            if (Auth::user()->hasRole('APOO')) {
                $this->source = $incomingDocument->apoDocument->source;
            }
            /* ----------------------------------- APO ---------------------------------- */

            $this->dispatch('show-details-modal');
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
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
