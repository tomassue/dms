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

    /**
     * Discards everything up to now so the next poll only has to fetch
     * notifications created/updated after this point, instead of the
     * ever-growing full list (the actual cause of the polling lag).
     */
    public function clearNotifications()
    {
        auth()->user()->update(['notifications_cleared_at' => now()]);

        $this->prepareNotifications();
    }

    protected function prepareNotifications()
    {
        $clearedAt = auth()->user()->notifications_cleared_at;

        // Load data
        $requests = IncomingRequest::when(auth()->user()->hasRole('Super Admin'), function ($query) {
            // Super Admin sees all
        }, function ($query) {
            $query->when(auth()->user()->user_metadata->division == null && auth()->user()->user_metadata->position == null, function ($query) {
                return $query->received();
            }, function ($query) {
                return $query->forwarded();
            });
        })
            ->when($clearedAt, function ($query) use ($clearedAt) {
                $query->where('updated_at', '>', $clearedAt);
            })
            ->get();

        $documents = IncomingDocument::when(auth()->user()->hasRole('Super Admin'), function ($query) {
            // Super Admin sees all
        }, function ($query) {
            $query->when(auth()->user()->user_metadata->division == null && auth()->user()->user_metadata->position == null, function ($query) {
                return $query->received();
            }, function ($query) {
                return $query->forwarded();
            });
        })
            ->when($clearedAt, function ($query) use ($clearedAt) {
                $query->where('updated_at', '>', $clearedAt);
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
