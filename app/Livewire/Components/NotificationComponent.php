<?php

namespace App\Livewire\Components;

use App\Models\IncomingDocument;
use App\Models\IncomingRequest;
use Livewire\Component;

class NotificationComponent extends Component
{
    public $notification = [];

    public function render()
    {
        return view(
            'livewire.components.notification-component',
            [
                'forwarded_incoming_requests' => $this->loadForwardedIncomingRequests(),
                'forwarded_incoming_documents' => $this->loadForwardedIncomingDocuments(),
            ]
        );
    }

    public function loadForwardedIncomingRequests()
    {
        return IncomingRequest::forwarded()
            ->get();
    }

    public function loadForwardedIncomingDocuments()
    {
        return IncomingDocument::forwarded()
            ->get();
    }

    //TODO: Optimize this component and the blade.
}
