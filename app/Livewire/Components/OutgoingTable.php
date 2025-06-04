<?php

namespace App\Livewire\Components;

use App\Models\File;
use App\Models\Outgoing;
use App\Models\OutgoingOthers;
use App\Models\OutgoingPayrolls;
use App\Models\OutgoingProcurement;
use App\Models\OutgoingRis;
use App\Models\OutgoingVoucher;
use App\Models\RefStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class OutgoingTable extends Component
{
    use WithPagination, WithFileUploads;

    public $editMode;
    public $search,
        $filter_start_date,
        $filter_end_date,
        $filter_status;
    public $outgoingId, $typeId;
    public $type;
    public $preview_file = [];
    public $activity_log = [];
    /* ---------------------------- begin:: OUTGOING ---------------------------- */
    public $date,
        $details,
        $destination,
        $person_responsible,
        $ref_status_id,
        $file_id = []; // for file upload - MorphMany
    public $document_name; // Used by others and ris
    /* ----------------------------- end:: OUTGOING ----------------------------- */

    /* ------------------------ begin:: OUTGOING PAYROLL ------------------------ */
    public $payroll_type;
    /* ------------------------- end:: OUTGOING PAYROLL ------------------------- */

    /* ---------------------- begin:: OUTGOING PROCUREMENT ---------------------- */
    public $pr_no,
        $po_no;
    /* ----------------------- end:: OUTGOING PROCUREMENT ----------------------- */

    /* -------------------------- begin:: OUTGOING RIS -------------------------- */
    public $ppmp_code;
    /* --------------------------- end:: OUTGOING RIS --------------------------- */

    /* ------------------------ begin:: OUTGOING VOUCHERS ----------------------- */
    public $voucher_name;
    /* ------------------------- end:: OUTGOING VOUCHERS ------------------------ */

    public function rules()
    {
        $rules = [
            'type' => 'required',
            'date' => 'required|date',
            'details' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'person_responsible' => 'required|string|max:255'
        ];

        if ($this->editMode) {
            $rules['ref_status_id'] = 'required|integer';
        }

        switch ($this->type) {
            case 'other':
                $rules['document_name'] = 'required|string|max:255';
                break;
            case 'payroll':
                $rules['payroll_type'] = 'required';
                break;
            case 'procurement':
                $rules['pr_no'] = 'required|string|max:255';
                $rules['po_no'] = 'required|string|max:255';
                break;
            case 'ris':
                $rules['ppmp_code'] = 'required|string|max:255';
                break;
            case 'voucher':
                $rules['voucher_name'] = 'required|string|max:255';
                break;
        }

        return $rules;
    }
    #[On('filter')]
    public function filter($start_date, $end_date, $status)
    {
        $this->filter_start_date = $start_date;
        $this->filter_end_date = $end_date;
        $this->filter_status = $status;
    }

    #[On('clear-filter-data')]
    public function clear()
    {
        $this->reset();
        $this->resetValidation();

        $this->dispatch('reset-files');
    }

    public function render()
    {
        return view(
            'livewire.components.outgoing-table',
            [
                'outgoings' => $this->loadOutgoings(),
                'status' => $this->loadStatus(), // for status dropdown
            ]
        );
    }

    public function loadOutgoings()
    {
        return Outgoing::query()
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->when($this->filter_start_date && $this->filter_end_date, function ($query) {
                $query->dateRange($this->filter_start_date, $this->filter_end_date);
            })
            ->when($this->filter_status, function ($query) {
                $query->where('ref_status_id', $this->filter_status);
            })
            ->paginate(10);
    }

    public function loadStatus()
    {
        return RefStatus::outgoing()
            ->get();
    }

    public function saveOutgoing()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                //
                // 1) type first: use $this->typeId, not $this->outgoingId
                //
                switch ($this->type) {
                    case 'other':
                        $type = OutgoingOthers::updateOrCreate(
                            ['id' => $this->typeId],
                            ['document_name' => $this->document_name]
                        );
                        break;

                    case 'payroll':
                        $type = OutgoingPayrolls::updateOrCreate(
                            ['id' => $this->typeId],
                            ['payroll_type' => $this->payroll_type]
                        );
                        break;

                    case 'procurement':
                        $type = OutgoingProcurement::updateOrCreate(
                            ['id' => $this->typeId],
                            [
                                'pr_no' => $this->pr_no,
                                'po_no' => $this->po_no,
                            ]
                        );
                        break;

                    case 'ris':
                        $type = OutgoingRis::updateOrCreate(
                            ['id' => $this->typeId],
                            [
                                'document_name' => $this->document_name,
                                'ppmp_code'     => $this->ppmp_code,
                            ]
                        );
                        break;

                    case 'voucher':
                        $type = OutgoingVoucher::updateOrCreate(
                            ['id' => $this->typeId],
                            ['voucher_name' => $this->voucher_name]
                        );
                        break;

                    default:
                        throw new \Exception("Unknown type: {$this->type}");
                }

                // remember for next save
                $this->typeId = $type->id;

                //
                // 2) Parent second: same as before
                //
                $outgoing = $this->saveMainOutgoing($type);

                //
                // 3) Files stay on the type
                //
                $this->saveFiles($type);

                //
                // 4) cleanup & feedback
                //
                $this->clear();
                $this->dispatch('hide-outgoing-modal');
                $this->dispatch('success', message: 'Outgoing saved.');
            });
        } catch (\Throwable $th) {
            // \Log::error($th);
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    /**
     * Create the parent via the type's morph relation on create,
     * or update the existing Outgoing on edit.
     */
    protected function saveMainOutgoing($type)
    {
        $data = [
            'date'               => $this->date,
            'details'            => $this->details,
            'destination'        => $this->destination,
            'person_responsible' => $this->person_responsible,
            'ref_status_id'      => $this->ref_status_id ?? '1',
            'office_id'          => auth()->user()->roles()->first()->id,
            'ref_division_id'    => auth()->user()->user_metadata->ref_division_id
        ];

        if ($this->outgoingId) {
            // EDIT mode
            $out = Outgoing::findOrFail($this->outgoingId);
            $out->update($data);
        } else {
            // CREATE mode
            $out = $type->outgoing()->create($data);
            $this->outgoingId = $out->id;
        }

        return $out;
    }

    protected function saveFiles($child)
    {
        if (empty($this->file_id)) {
            return;
        }
        foreach ((array)$this->file_id as $file) {
            $child->files()->create([
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'type' => $file->getMimeType(),
                'file' => file_get_contents($file->getRealPath()),
            ]);
        }
    }


    public function editOutgoing(Outgoing $outgoing)
    {
        try {
            $this->outgoingId = $outgoing->id;
            $this->editMode = true;

            $this->date = $outgoing->date;
            $this->details = $outgoing->details;
            $this->destination = $outgoing->destination;
            $this->person_responsible = $outgoing->person_responsible;
            $this->ref_status_id = $outgoing->ref_status_id;

            // Load type and its ID
            $type = $outgoing->outgoingable;
            $this->typeId = $type->id;

            switch ($outgoing->outgoingable_type) {
                case 'App\Models\OutgoingOthers':
                    $this->type = 'other';
                    $this->document_name = $outgoing->outgoingable->document_name;
                    break;
                case 'App\Models\OutgoingPayrolls':
                    $this->type = 'payroll';
                    $this->payroll_type = $outgoing->outgoingable->payroll_type;
                    break;
                case 'App\Models\OutgoingProcurement':
                    $this->type = 'procurement';
                    $this->pr_no = $outgoing->outgoingable->pr_no;
                    $this->po_no = $outgoing->outgoingable->po_no;
                    break;
                case 'App\Models\OutgoingRis':
                    $this->type = 'ris';
                    $this->document_name = $outgoing->outgoingable->document_name;
                    $this->ppmp_code = $outgoing->outgoingable->ppmp_code;
                    break;
                case 'App\Models\OutgoingVoucher':
                    $this->type = 'voucher';
                    $this->voucher_name = $outgoing->outgoingable->voucher_name;
                    break;
            }

            $this->preview_file = $outgoing->outgoingable->files;

            $this->dispatch('show-outgoing-modal');
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    public function activityLog($id)
    {
        try {
            // Step 1: Get OutgoingModel
            $outgoing = Outgoing::with('outgoingable')->findOrFail($id);

            // Step 2: Collect related file IDs if any
            $fileIds = File::where('fileable_type', Outgoing::class)
                ->where('fileable_id', $id)
                ->pluck('id');

            // Optional: Include file IDs from the polymorphic model
            if ($outgoing->outgoingable) {
                $fileIds = $fileIds->merge(
                    File::where('fileable_type', get_class($outgoing->outgoingable))
                        ->where('fileable_id', $outgoing->outgoingable->id)
                        ->pluck('id')
                );
            }

            // Step 3: Fetch activity logs for OutgoingModel and its morph
            $outgoingLogs = Activity::where(function ($query) use ($outgoing) {
                $query->where('subject_type', Outgoing::class)
                    ->where('subject_id', $outgoing->id);
            })
                ->orWhere(function ($query) use ($outgoing) {
                    $query->where('subject_type', get_class($outgoing->outgoingable))
                        ->where('subject_id', $outgoing->outgoingable->id);
                })
                ->with(['causer.user_metadata.division'])
                ->get();

            // Step 4: Fetch file logs
            $fileLogs = Activity::where('subject_type', File::class)
                ->whereIn('subject_id', $fileIds)
                ->with(['causer.user_metadata.division'])
                ->get();

            // Step 5: Combine and sort
            $this->activity_log = $outgoingLogs->merge($fileLogs)
                ->sortByDesc('created_at')
                ->values()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'file_log_description' => $activity->description,
                        'causer' => $activity->causer?->name ?? 'System',
                        'division' => $activity->causer?->user_metadata?->division?->name
                            ? '[' . $activity->causer->user_metadata->division->name . ']'
                            : '',
                        'created_at' => Carbon::parse($activity->created_at)->format('M d, Y h:i A'),
                        'changes' => collect($activity->properties['attributes'] ?? [])
                            ->except(['id', 'created_at', 'updated_at', 'deleted_at', 'outgoingable_type', 'outgoingable_id', 'office_id'])
                            ->map(function ($newValue, $key) use ($activity) {
                                $oldValue = $activity->properties['old'][$key] ?? 'N/A';
                                $fieldName = match ($key) {
                                    'file_id' => 'Files',
                                    'ref_status_id' => 'Status',
                                    // Add additional mappings here
                                    default => ucfirst(str_replace('_', ' ', $key)),
                                };

                                // Handle status
                                if ($key === "ref_status_id") {
                                    $oldValue = $oldValue !== 'N/A' ? RefStatus::find($oldValue)?->name : 'N/A';
                                    $newValue = $newValue !== 'N/A' ? RefStatus::find($newValue)?->name : 'N/A';
                                }

                                // Handle dates
                                if (in_array($key, ['date', 'deleted_at'])) {
                                    $oldValue = $oldValue !== 'N/A' ? Carbon::parse($oldValue)->format('M d, Y') : 'N/A';
                                    $newValue = $newValue !== 'N/A' ? Carbon::parse($newValue)->format('M d, Y') : 'N/A';
                                }

                                // Handle file IDs
                                if ($key === 'file_id') {
                                    $oldValue = is_string($oldValue) ? json_decode($oldValue, true) : $oldValue;
                                    $newValue = is_string($newValue) ? json_decode($newValue, true) : $newValue;

                                    $oldValue = is_array($oldValue)
                                        ? implode(', ', File::whereIn('id', $oldValue)->pluck('name')->toArray())
                                        : 'N/A';
                                    $newValue = is_array($newValue)
                                        ? implode(', ', File::whereIn('id', $newValue)->pluck('name')->toArray())
                                        : 'N/A';
                                }

                                return [
                                    'field' => $fieldName,
                                    'old' => $oldValue,
                                    'new' => $newValue,
                                ];
                            })
                            ->values()
                            ->toArray(),
                    ];
                });

            $this->dispatch('show-activity-log-modal');
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
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
}
