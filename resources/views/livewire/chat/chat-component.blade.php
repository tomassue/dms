<div wire:poll.5s>
    <!--begin::Content-->
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Container-->
        <div class="container-xxl" id="kt_content_container">
            <div class="card">
                <div class="row g-0" style="height: 72vh;">
                    <!--begin::Conversations-->
                    <div class="col-12 col-md-4 border-end d-flex flex-column h-100">
                        <div class="p-4 border-bottom position-relative">
                            <input type="text" class="form-control" placeholder="Search people to message..." wire:model.live.debounce.300ms="userSearch" autocomplete="off">

                            @if($searchResults->isNotEmpty())
                            <div class="list-group position-absolute w-100 shadow-sm" style="z-index: 10; left: 0; top: 100%;">
                                @foreach($searchResults as $user)
                                <button type="button" class="list-group-item list-group-item-action d-flex align-items-center" wire:click="startConversationWith({{ $user->id }})">
                                    <div class="profile-picture bg-color-{{ $user->id % 5 }} symbol symbol-30px me-3">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                    {{ $user->name }}
                                </button>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <div class="flex-grow-1 overflow-auto">
                            @forelse($conversations as $conversation)
                            @php $other = $conversation->otherUser(auth()->id()); @endphp
                            <div class="d-flex align-items-center p-4 border-bottom {{ $selectedConversationId === $conversation->id ? 'bg-light-primary' : '' }}" wire:click="selectConversation({{ $conversation->id }})" style="cursor: pointer;">
                                <div class="profile-picture bg-color-{{ $other->id % 5 }} symbol symbol-40px me-3">{{ strtoupper(substr($other->name, 0, 1)) }}</div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-truncate">{{ $other->name }}</span>
                                        @if($conversation->last_message_at)
                                        <span class="text-muted fs-9 ms-2">{{ $conversation->last_message_at->diffForHumans(null, true) }}</span>
                                        @endif
                                    </div>
                                    <div class="text-muted fs-7 text-truncate">
                                        {{ $conversation->latestMessage->body ?? 'No messages yet' }}
                                    </div>
                                </div>
                                @if($conversation->unread_count > 0)
                                <span class="badge badge-circle badge-danger ms-2">{{ $conversation->unread_count }}</span>
                                @endif
                            </div>
                            @empty
                            <div class="p-4 text-muted">No conversations yet. Search for someone above to start chatting.</div>
                            @endforelse
                        </div>
                    </div>
                    <!--end::Conversations-->

                    <!--begin::Thread-->
                    <div class="col-12 col-md-8 d-flex flex-column h-100">
                        @if($selectedConversationId)
                        <div class="flex-grow-1 overflow-auto p-4 d-flex flex-column-reverse">
                            @forelse($messages as $message)
                            <div class="d-flex mb-3 {{ $message->sender_id === auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                                <div class="p-3 rounded {{ $message->sender_id === auth()->id() ? 'bg-primary text-white' : 'bg-light' }}" style="max-width: 60%;">
                                    <div style="font-size: 16px;">{{ $message->body }}</div>
                                    <div class="fs-9 mt-1 opacity-75">{{ $message->created_at->format('M d, h:i A') }}</div>
                                </div>
                            </div>
                            @empty
                            <div class="text-muted">No messages yet. Say hello!</div>
                            @endforelse
                        </div>

                        <form wire:submit.prevent="sendMessage" class="border-top">
                            <div class="d-flex p-3">
                                <input type="text" class="form-control me-2" placeholder="Type a message..." wire:model="body" autocomplete="off">
                                <button type="submit" class="btn btn-primary">Send</button>
                            </div>
                            @error('body')
                            <div class="text-danger px-3 pb-2">{{ $message }}</div>
                            @enderror
                        </form>
                        @else
                        <div class="d-flex align-items-center justify-content-center flex-grow-1 text-muted">
                            Select a conversation or search for someone to start chatting.
                        </div>
                        @endif
                    </div>
                    <!--end::Thread-->
                </div>
            </div>
        </div>
        <!--end::Container-->
    </div>
    <!--end::Content-->
</div>
