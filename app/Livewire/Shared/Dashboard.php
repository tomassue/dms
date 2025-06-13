<?php

namespace App\Livewire\Shared;

use App\Models\IncomingDocument;
use App\Models\IncomingRequest;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Dashboard')]
class Dashboard extends Component
{
    use WithPagination;

    public function render()
    {
        return view(
            'livewire.shared.dashboard',
            [
                'pending_incoming_requests' => $this->loadPendingIncomingRequests(),
                'forwarded_incoming_requests' => $this->loadForwardedIncomingRequests(),
                'completed_incoming_requests' => $this->loadCompletedIncomingRequests(),
                'total_incoming_requests' => $this->loadTotalIncomingRequests(),
                'incoming_requests' => $this->loadIncomingRequests(),
                'incoming_documents' => $this->loadIncomingDocuments(),
            ]
        );
    }

    public function loadPendingIncomingRequests()
    {
        return IncomingRequest::pending()
            ->get();
    }

    public function loadForwardedIncomingRequests()
    {
        return IncomingRequest::forwarded()
            ->get();
    }

    public function loadCompletedIncomingRequests()
    {
        return IncomingRequest::completed()
            ->get();
    }

    public function loadTotalIncomingRequests()
    {
        return IncomingRequest::count();
    }

    public function loadIncomingRequests()
    {
        return IncomingRequest::paginate(5, pageName: 'incoming_requests');
    }

    public function loadIncomingDocuments()
    {
        return IncomingDocument::paginate(5, pageName: 'incoming_documents');
    }
}
