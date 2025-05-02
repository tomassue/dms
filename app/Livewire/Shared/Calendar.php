<?php

namespace App\Livewire\Shared;

use App\Models\IncomingRequest;
use Illuminate\Support\Facades\URL;
use Livewire\Component;

class Calendar extends Component
{
    public $incomingRequest, //* Holds incoming request details
        $previewFile = [];

    public function clear()
    {
        $this->reset();
    }

    public function render()
    {
        return view(
            'livewire.shared.calendar',
            [
                'incoming_requests' => $this->loadIncomingRequests(),
            ]
        );
    }

    public function loadIncomingRequests()
    {
        return IncomingRequest::query()
            ->get()
            ->map(function ($item) {
                $colors = [
                    'pending'    => '#f1416c', // Red
                    'processed'  => '#7239ea', // Purple
                    'forwarded'  => '#ffc700', // Yellow
                    'completed'  => '#00d9d9', // Neon Blue
                    'cancelled'  => '#181c32', // Black
                    'received'   => '#181c32', // Black
                ];

                return [
                    'id'              => $item->id,
                    'title'           => $item->office_barangay_organization,
                    'start'           => $item->date_time,
                    // 'end'             => $item->date_time,
                    'allDay'          => false,
                    'backgroundColor' => $colors[$item->status->name] ?? '#E4A11B',
                    'borderColor'     => $colors[$item->status->name] ?? '#E4A11B',
                ];
            });
    }

    public function showDetails(IncomingRequest $incomingRequest)
    {
        try {
            $this->incomingRequest = $incomingRequest;

            if ($incomingRequest->files()->exists()) {
                $this->previewFile = $incomingRequest->files;
            }

            $this->dispatch('show-detailsModal');
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

        $this->dispatch('open-file', url: $signedURL);
    }
}
