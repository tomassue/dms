<?php

namespace App\Livewire\Shared\Incoming;

use App\Models\IncomingDocument;
use Livewire\Component;
use Livewire\WithPagination;

class Documents extends Component
{
    use WithPagination;

    public function render()
    {
        return view(
            'livewire.shared.incoming.documents',
            [
                'incoming_documents' => $this->loadIncomingDocuments()
            ]
        );
    }

    public function loadIncomingDocuments()
    {
        return IncomingDocument::query()
            ->paginate(10);
    }
}
