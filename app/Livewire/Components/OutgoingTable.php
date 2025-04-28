<?php

namespace App\Livewire\Components;

use App\Models\Outgoing;
use App\Models\OutgoingOthers;
use App\Models\OutgoingPayrolls;
use App\Models\OutgoingProcurement;
use App\Models\OutgoingRis;
use App\Models\OutgoingVoucher;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class OutgoingTable extends Component
{
    use WithPagination, WithFileUploads;

    public $editMode;
    public $search;
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
                'outgoings' => $this->loadOutgoings()
            ]
        );
    }

    public function loadOutgoings()
    {
        return Outgoing::query()
            ->paginate(10);
    }

    public function saveOutgoing()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                switch ($this->type) {
                    case 'other':
                        $outgoing_other = OutgoingOthers::updateOrCreate(
                            [
                                'id' => $this->outgoingId,
                            ],
                            [
                                'document_name' => $this->document_name,
                            ]
                        );

                        $this->saveMainOutgoing($outgoing_other);
                        $this->saveFiles($outgoing_other);

                        $this->dispatch('hide-outgoing-modal');
                        $this->dispatch('success', message: 'Outgoing saved successfully.');
                        break;
                    case 'payroll':
                        $outgoing_payroll = OutgoingPayrolls::updateOrCreate(
                            [
                                'id' => $this->outgoingId
                            ],
                            [
                                'payroll_type' => $this->payroll_type,
                            ]
                        );

                        $this->saveMainOutgoing($outgoing_payroll);
                        $this->saveFiles($outgoing_payroll);

                        $this->dispatch('hide-outgoing-modal');
                        $this->dispatch('success', message: 'Outgoing saved successfully.');
                        break;
                    case 'procurement':
                        $outgoing_procurement = OutgoingProcurement::updateOrCreate(
                            ['id' => $this->outgoingId],
                            [
                                'pr_no' => $this->pr_no,
                                'po_no' => $this->po_no,
                            ]
                        );

                        $this->saveMainOutgoing($outgoing_procurement);
                        $this->saveFiles($outgoing_procurement);

                        $this->dispatch('hide-outgoing-modal');
                        $this->dispatch('success', message: 'Outgoing saved successfully.');
                        break;
                    case 'ris':
                        $outgoing_ris = OutgoingRis::updateOrCreate(
                            ['id' => $this->outgoingId],
                            [
                                'document_name' => $this->document_name,
                                'ppmp_code' => $this->ppmp_code
                            ]
                        );

                        $this->saveMainOutgoing($outgoing_ris);
                        $this->saveFiles($outgoing_ris);

                        $this->dispatch('hide-outgoing-modal');
                        $this->dispatch('success', message: 'Outgoing saved successfully.');
                        break;
                    case 'voucher':
                        $outgoing_voucher = OutgoingVoucher::updateOrCreate(
                            ['id' => $this->outgoingId],
                            [
                                'voucher_name' => $this->voucher_name
                            ]
                        );

                        $this->saveMainOutgoing($outgoing_voucher);
                        $this->saveFiles($outgoing_voucher);

                        $this->dispatch('hide-outgoing-modal');
                        $this->dispatch('success', message: 'Outgoing saved successfully.');
                        break;
                }
            });
        } catch (\Throwable $th) {
            // throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }

    protected function saveMainOutgoing($model)
    {
        $outgoing = $model->outgoing()->updateOrCreate(
            [
                'id' => $this->outgoingId,
            ],
            [
                'date' => $this->date,
                'details' => $this->details,
                'destination' => $this->destination,
                'person_responsible' => $this->person_responsible,
                'ref_status_id' => $this->ref_status_id ?? '1', // Pending
            ]
        );

        return $outgoing;
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

    //TODO: UPDATE for outgoing.
    public function editOutgoing(Outgoing $outgoing)
    {
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
            $this->dispatch('error', message: 'Something went wrong.');
        }
    }
}
