<?php

namespace App\Livewire\Components;

use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class ChatNotificationComponent extends Component
{
    public ?int $previousUnreadCount = null;

    public ?int $lastAlertedMessageId = null;

    public function mount(): void
    {
        $this->previousUnreadCount = $this->getUnreadCount();
        $this->lastAlertedMessageId = $this->getLatestUnreadMessage()?->id;
    }

    public function render()
    {
        $unreadCount = $this->getUnreadCount();

        if ($this->previousUnreadCount !== null && $unreadCount > $this->previousUnreadCount) {
            $this->dispatch('play-chat-notification-sound');

            $latestMessage = $this->getLatestUnreadMessage();

            if ($latestMessage && $latestMessage->id !== $this->lastAlertedMessageId) {
                $this->dispatch('warning', message: 'New message from '.($latestMessage->sender->name ?? 'Someone').': '.Str::limit($latestMessage->body, 80));
                $this->lastAlertedMessageId = $latestMessage->id;
            }
        }

        $this->previousUnreadCount = $unreadCount;

        return view('livewire.components.chat-notification-component', [
            'unreadCount' => $unreadCount,
        ]);
    }

    protected function getUnreadCount(): int
    {
        return ChatMessage::whereHas('conversation', function ($query) {
            $query->forUser(Auth::id());
        })
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->count();
    }

    protected function getLatestUnreadMessage(): ?ChatMessage
    {
        return ChatMessage::whereHas('conversation', function ($query) {
            $query->forUser(Auth::id());
        })
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->with('sender')
            ->orderByDesc('id')
            ->first();
    }
}
