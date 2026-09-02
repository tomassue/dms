<?php

namespace App\Livewire\Components;

use App\Models\ChatMessage;
use App\Models\IncomingDocument;
use App\Models\IncomingRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoginUnreadAlert extends Component
{
    public function mount(): void
    {
        // Only alert right after an actual login - the flag is set once by
        // LoginController::authenticated() and consumed (removed) here.
        if (! session()->pull('just_logged_in')) {
            return;
        }

        $unreadChatCount = ChatMessage::whereHas('conversation', function ($query) {
            $query->forUser(Auth::id());
        })
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->count();

        $unreadNotificationCount = $this->countPendingNotifications();

        if ($unreadChatCount === 0 && $unreadNotificationCount === 0) {
            return;
        }

        $parts = [];

        if ($unreadChatCount > 0) {
            $parts[] = $unreadChatCount.' unread chat message'.($unreadChatCount === 1 ? '' : 's');
        }

        if ($unreadNotificationCount > 0) {
            $parts[] = $unreadNotificationCount.' pending notification'.($unreadNotificationCount === 1 ? '' : 's');
        }

        $this->dispatch('warning', message: 'You have '.implode(' and ', $parts).'.');
    }

    public function render()
    {
        return view('livewire.components.login-unread-alert');
    }

    protected function countPendingNotifications(): int
    {
        $requests = IncomingRequest::when(auth()->user()->hasRole('Super Admin'), function ($query) {
            // Super Admin sees all
        }, function ($query) {
            $query->when(auth()->user()->user_metadata->division == null && auth()->user()->user_metadata->position == null, function ($query) {
                return $query->received();
            }, function ($query) {
                return $query->forwarded();
            });
        })->count();

        $documents = IncomingDocument::when(auth()->user()->hasRole('Super Admin'), function ($query) {
            // Super Admin sees all
        }, function ($query) {
            $query->when(auth()->user()->user_metadata->division == null && auth()->user()->user_metadata->position == null, function ($query) {
                return $query->received();
            }, function ($query) {
                return $query->forwarded();
            });
        })->count();

        return $requests + $documents;
    }
}
