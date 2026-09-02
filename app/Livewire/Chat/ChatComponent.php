<?php

namespace App\Livewire\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Chat')]
class ChatComponent extends Component
{
    public ?int $selectedConversationId = null;

    public string $body = '';

    public string $userSearch = '';

    public ?int $lastSeenMessageId = null;

    public function mount(?int $conversation = null): void
    {
        if ($conversation && $this->userBelongsToConversation($conversation)) {
            $this->selectedConversationId = $conversation;
        }
    }

    public function render()
    {
        $userId = Auth::id();

        $conversations = ChatConversation::query()
            ->forUser($userId)
            ->with(['userOne', 'userTwo', 'latestMessage'])
            ->withCount(['messages as unread_count' => function ($query) use ($userId) {
                $query->where('sender_id', '!=', $userId)->whereNull('read_at');
            }])
            ->orderByDesc('last_message_at')
            ->get();

        $messages = collect();

        if ($this->selectedConversationId) {
            $messages = ChatMessage::where('conversation_id', $this->selectedConversationId)
                ->with('sender')
                ->orderByDesc('created_at')
                ->get();

            $latestFromOther = $messages->firstWhere('sender_id', '!=', $userId);

            if ($latestFromOther && $latestFromOther->id !== $this->lastSeenMessageId) {
                if ($this->lastSeenMessageId !== null) {
                    $this->dispatch('play-chat-notification-sound');
                }

                $this->lastSeenMessageId = $latestFromOther->id;
            }

            $this->markConversationRead($this->selectedConversationId);
        }

        $searchResults = collect();

        if (trim($this->userSearch) !== '') {
            $searchResults = User::query()
                ->where('id', '!=', $userId)
                ->where('name', 'like', '%'.$this->userSearch.'%')
                ->limit(10)
                ->get();
        }

        return view('livewire.chat.chat-component', [
            'conversations' => $conversations,
            'messages' => $messages,
            'searchResults' => $searchResults,
        ]);
    }

    public function selectConversation(int $conversationId): void
    {
        if (! $this->userBelongsToConversation($conversationId)) {
            return;
        }

        $this->selectedConversationId = $conversationId;
        $this->userSearch = '';
        $this->lastSeenMessageId = null;
    }

    public function startConversationWith(int $userId): void
    {
        if ($userId === Auth::id()) {
            return;
        }

        $conversation = ChatConversation::between(Auth::id(), $userId);

        $this->selectedConversationId = $conversation->id;
        $this->userSearch = '';
        $this->lastSeenMessageId = null;
    }

    public function sendMessage(): void
    {
        $this->validate([
            'body' => 'required|string|max:5000',
        ]);

        if (! $this->selectedConversationId || ! $this->userBelongsToConversation($this->selectedConversationId)) {
            return;
        }

        ChatMessage::create([
            'conversation_id' => $this->selectedConversationId,
            'sender_id' => Auth::id(),
            'body' => trim($this->body),
        ]);

        ChatConversation::whereKey($this->selectedConversationId)->update([
            'last_message_at' => now(),
        ]);

        $this->body = '';
    }

    protected function markConversationRead(int $conversationId): void
    {
        ChatMessage::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    protected function userBelongsToConversation(int $conversationId): bool
    {
        return ChatConversation::forUser(Auth::id())->whereKey($conversationId)->exists();
    }
}
