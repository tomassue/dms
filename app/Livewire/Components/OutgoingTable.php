<?php

namespace App\Livewire\Components;

use App\Models\Outgoing;
use Livewire\Component;
use Livewire\WithPagination;

class OutgoingTable extends Component
{
    use WithPagination;

    public $editMode;
    public $search;
    public $outgoingId;
    public $type;
    /* ---------------------------- begin:: OUTGOING ---------------------------- */
    public $date,
        $details,
        $destination,
        $person_responsible,
        $ref_status_id;
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


    public function clear()
    {
        $this->reset();
        $this->resetValidation();
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
}
