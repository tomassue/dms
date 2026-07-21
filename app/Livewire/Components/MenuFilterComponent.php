<?php

namespace App\Livewire\Components;

use App\Models\RefDocumentType;
use App\Models\RefIncomingDocumentCategory;
use App\Models\RefIncomingRequestCategory;
use App\Models\RefStatus;
use Dom\DocumentType as DomDocumentType;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MenuFilterComponent extends Component
{
    public $page, $context;
    //* APO
    public $start_date,
        $end_date,
        $request_category,
        $document_category,
        $status,
        $outgoing_category,
        $doctype;

    public function mount($page, $context = null)
    {
        $this->page = $page;
        $this->context = $context;
    }

    public function filter()
    {
        $this->dispatch(
            'filter',
            start_date: $this->start_date,
            end_date: $this->end_date,
            request_category: $this->request_category,
            document_category: $this->document_category,
            doctype: $this->doctype,
            status: $this->status,
            outgoing_category: $this->outgoing_category
        );
    }

    public function clear()
    {
        $this->resetExcept('page');

        $this->dispatch('clear-filter-date'); // date range picker

        $this->dispatch('clear-filter-data'); // Clear ALL filter data for parent components
    }

    public function render()
    {
        return view('livewire.components.menu-filter-component', [
            'category_request_dropdown' => $this->loadRequestCategory(),
            'category_document_dropdown' => $this->loadDocumentCategory(),
            'doctype_dropdown' => $this->loadDocumentType(),
            'status_dropdown' => $this->loadStatus(), // Status dropdown
        ]);
    }

    public function loadRequestCategory()
    {
        $request_category = RefIncomingRequestCategory::get();
        return $request_category;
    }

    public function loadDocumentCategory()
    {
        $document_category = RefIncomingDocumentCategory::get();
        return $document_category;
    }

    public function loadDocumentType()
    {
        $doctype = RefDocumentType::get();
        return $doctype;
    }

    public function loadStatus()
    {
    switch ($this->page) {
            case 'outgoing':
                $status = RefStatus::outgoing()->get();
                break;
            case 'incoming':
                // $status = RefStatus::incoming()->get();
                $status = RefStatus::get();
                break;
            default:
                $status = RefStatus::all();
                break;
        }

        return $status;
    }
}
