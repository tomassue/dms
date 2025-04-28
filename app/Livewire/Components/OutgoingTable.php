<?php

namespace App\Livewire\Components;

use App\Models\Outgoing;
use App\Models\OutgoingOthers;
use App\Models\OutgoingPayrolls;
use App\Models\OutgoingProcurement;
use App\Models\OutgoingRis;
use App\Models\OutgoingVoucher;
use App\Models\RefStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class OutgoingTable extends Component
{
    use WithPagination, WithFileUploads;

    public $editMode;
    public $search,
        $filter_start_date,
        $filter_end_date;
    public $outgoingId;
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
                // First create/update the main outgoing record
                $outgoing = Outgoing::updateOrCreate(
                    ['id' => $this->outgoingId],
                    [
                        'date' => $this->date,
                        'details' => $this->details,
                        'destination' => $this->destination,
                        'person_responsible' => $this->person_responsible,
                        'ref_status_id' => $this->ref_status_id ?? '1', // Pending
                    ]
                );

                // Then handle the specific type
                switch ($this->type) {
                    case 'other':
                        $outgoing->outgoingable()->updateOrCreate(
                            [],
                            ['document_name' => $this->document_name]
                        );
                        break;

                    case 'payroll':
                        $outgoing->outgoingable()->updateOrCreate(
                            [],
                            ['payroll_type' => $this->payroll_type]
                        );
                        break;

                    case 'procurement':
                        $outgoing->outgoingable()->updateOrCreate(
                            [],
                            [
                                'pr_no' => $this->pr_no,
                                'po_no' => $this->po_no,
                            ]
                        );
                        break;

                    case 'ris':
                        $outgoing->outgoingable()->updateOrCreate(
                            [],
                            [
                                'document_name' => $this->document_name,
                                'ppmp_code' => $this->ppmp_code
                            ]
                        );
                        break;

                    case 'voucher':
                        $outgoing->outgoingable()->updateOrCreate(
                            [],
                            ['voucher_name' => $this->voucher_name]
                        );
                        break;
                }

                // Handle file uploads if needed
                if (!empty($this->file_id)) {
                    $outgoingable = $outgoing->outgoingable;
                    foreach ((array)$this->file_id as $file) {
                        $outgoingable->files()->create([
                            'name' => $file->getClientOriginalName(),
                            'size' => $file->getSize(),
                            'type' => $file->getMimeType(),
                            'file' => file_get_contents($file->getRealPath()),
                        ]);
                    }
                }

                $this->dispatch('hide-outgoing-modal');
                $this->dispatch('success', message: 'Outgoing saved successfully.');
            });
        } catch (\Throwable $th) {
            // You might want to log the error for debugging:
            // \Log::error('Error saving outgoing: ' . $th->getMessage());
            $this->dispatch('error', message: 'Something went wrong.');
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
