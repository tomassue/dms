<?php

namespace App\Livewire\Components;

use App\Models\IncomingDocument;
use App\Models\IncomingRequest;
use Livewire\Component;

class NotificationComponent extends Component
{
    public $notifications = [];

    public function render()
    {
        $this->prepareNotifications();

        return view('livewire.components.notification-component', [
            'notifications' => $this->notifications
        ]);
    }

    protected function prepareNotifications()
    {
        // Load data
        $requests = IncomingRequest::when(auth()->user()->user_metadata->division == null && auth()->user()->user_metadata->position == null, function ($query) {
            return $query->received();
        }, function ($query) {
            return $query->forwarded();
        })
            ->get();

        $documents = IncomingDocument::when(auth()->user()->user_metadata->division == null && auth()->user()->user_metadata->position == null, function ($query) {
            return $query->received();
        }, function ($query) {
            return $query->forwarded();
        })
            ->get();

        // Format notifications with human-readable time
        $this->notifications = collect()
            ->merge($requests->map(function ($item) {
                return [
                    'type' => 'request',
                    'status' => $item->status->name,
                    'id' => $item->id,
                    'title' => $item->no, // or whatever field you display
                    'created_at' => $item->created_at->diffForHumans(),
                    'human_time' => $item->updated_at->diffForHumans(),
                    'raw_time' => $item->updated_at->format('Y-m-d H:i:s'),
                    // Add other relevant fields
                ];
            }))
            ->merge($documents->map(function ($item) {
                return [
                    'type' => 'document',
                    'status' => $item->status->name,
                    'id' => $item->id,
                    'title' => $item->category->name, // or whatever field you display
                    'created_at' => $item->created_at->diffForHumans(),
                    'human_time' => $item->updated_at->diffForHumans(),
                    'raw_time' => $item->updated_at->format('Y-m-d H:i:s'),
                    // Add other relevant fields
                ];
            }))
            ->sortByDesc('created_at') // Sort by newest first
            ->values(); // Reset keys
    }
}
