<?php

namespace App\Livewire\Shared\Incoming;

use App\Models\Apo\IncomingDocument as ApoIncomingDocument;
use App\Models\File;
use App\Models\Forwarded;
use App\Models\IncomingDocument;
use App\Models\RefDivision;
use App\Models\RefIncomingDocumentCategory;
use App\Models\RefStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class Documents extends Component
{
    use WithPagination, WithFileUploads;

    public $page = 'incoming documents'; // For recent-forwards-directive
    public $editMode;
    public $search;
    public $incomingDocumentId;
    public $selected_divisions = [],
        $forwarded_divisions = [];
    public $preview_file = [];
    public $activity_log = [];

    /* ---------------------------- begin::Properties --------------------------- */
    public $ref_incoming_document_category_id,
        $document_info,
        $date,
        $ref_status_id,
        $remarks,
        $file_id = []; // for file upload - MorphMany
    //* APO
    public $source;
    /* ----------------------------- end::Properties ---------------------------- */

    public function rules()
    {
        $rules = [
            'ref_incoming_document_category_id' => 'required|exists:ref_incoming_documents_categories,id',
            'document_info' => 'required',
            'date' => 'required|date'
        ];

        if (auth()->user()->hasRole('APO')) {
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

    public function clear()
    {
        $this->reset();
        $this->resetValidation();

        $this->dispatch('reset-files');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function loadIncomingDocuments()
    {
        return IncomingDocument::query()
            ->with('apoDocument')
            ->paginate(10);
    }

    public function loadRefIncomingDocumentCategory()
    {
        return RefIncomingDocumentCategory::all();
    }

    public function loadRefStatus()
    {
        return RefStatus::all();
    }

    public function loadDivisions()
    {
        return RefDivision::where('role_id', auth()->user()->roles()->first()->id)
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
            $this->ref_incoming_document_category_id = $incomingDocument->ref_incoming_document_category_id;
            $this->document_info = $incomingDocument->document_info;
            $this->date = $incomingDocument->date;
            $this->ref_status_id = $incomingDocument->ref_status_id;
            $this->remarks = $incomingDocument->remarks;
            $this->preview_file = $incomingDocument->files;

            if (auth()->user()->hasRole('APO')) {
                $this->source = $incomingDocument->apoDocument->source ?? '';
            }

            $this->incomingDocumentId = $incomingDocument->id;
            $this->editMode = true;
            $this->dispatch('show-incoming-document-modal');
        } catch (\Throwable $th) {
            $this->dispatch('error', message: 'Something went wrong.');
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
            'ref_incoming_document_category_id' => $this->ref_incoming_document_category_id,
            'document_info' => $this->document_info,
            'date' => $this->date,
            'remarks' => $this->remarks
        ];

        return IncomingDocument::updateOrCreate(
            ['id' => $this->incomingDocumentId ?? null],
            $data
        );
    }


    protected function saveApoIncomingDocument($incomingDocument)
    {
        if (!auth()->user()->hasRole('APO')) return; // Return if not APO

        return ApoIncomingDocument::updateOrCreate(
            ['incoming_document_id' => $incomingDocument->id], // Update if exists. Otherwise, create
            [
                'source' => $this->source,
            ]
        );
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

    public function viewFile($id)
    {
        $signedURL = URL::temporarySignedRoute(
            'file.view',
            now()->addMinutes(10),
            ['id' => $id]
        );

        // Dispatch an event to the browser to open the URL in a new tab
        $this->dispatch('open-file', url: $signedURL);
    }

    public function activityLog($id)
    {
        try {
            // Shows activity log
            $this->activity_log = Activity::whereIn('subject_type', [IncomingDocument::class, ApoIncomingDocument::class])
                ->whereIn('log_name', ['incoming_document', 'apo_incoming_document'])
                ->whereNot('event', 'created')
                ->where('subject_id', $id)
                ->with(['causer.user_metadata.division']) // ✅ Eager-load nested relations
                ->latest()
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'description' => $activity->description,
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
                                    $oldValue = $oldValue !== 'N/A' ? RefIncomingDocumentCategory::find($oldValue)?->name : 'N/A';
                                    $newValue = $newValue !== 'N/A' ? RefIncomingDocumentCategory::find($newValue)?->name : 'N/A';
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

            // 2. Get Forward records (only ref_division_id)
            $this->forwarded_divisions = Forwarded::where('forwardable_id', $id)
                ->with(['division']) // Assuming 'division' is a relationship
                ->latest()
                ->get()
                ->map(function ($forward) {
                    return [
                        'division_name' => $forward->division?->name ?? 'N/A',
                    ];
                });

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
            $incomingDocument = IncomingDocument::find($this->incomingDocumentId);

            foreach ($this->selected_divisions as $division) {
                $incomingDocument->forwards()->create([
                    'ref_division_id' => $division,
                ]);
            }

            $incomingDocument->update([
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
