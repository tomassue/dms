<!-- begin::Chat -->
<div class="d-flex align-items-center ms-1 ms-lg-3" wire:poll>
    <a href="{{ route('chat') }}" class="btn btn-icon btn-active-light-info position-relative w-30px h-30px w-md-40px h-md-40px">
        <i class="bi bi-chat-dots"></i>
        @if($unreadCount > 0)
        <span class="badge badge-circle badge-danger position-absolute translate-middle top-0 start-100" style="font-size: 10px;">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
        @endif
    </a>
</div>
<!-- end::Chat -->

@script
<script>
    function playChatNotificationSound() {
        try {
            window.__chatAudioCtx = window.__chatAudioCtx || new (window.AudioContext || window.webkitAudioContext)();
            const ctx = window.__chatAudioCtx;

            if (ctx.state === 'suspended') {
                ctx.resume();
            }

            const now = ctx.currentTime;

            [880, 1175].forEach((freq, i) => {
                const oscillator = ctx.createOscillator();
                const gainNode = ctx.createGain();

                oscillator.type = 'sine';
                oscillator.frequency.value = freq;

                const start = now + i * 0.12;
                gainNode.gain.setValueAtTime(0.0001, start);
                gainNode.gain.exponentialRampToValueAtTime(0.2, start + 0.02);
                gainNode.gain.exponentialRampToValueAtTime(0.0001, start + 0.25);

                oscillator.connect(gainNode);
                gainNode.connect(ctx.destination);
                oscillator.start(start);
                oscillator.stop(start + 0.25);
            });
        } catch (e) {
            // Audio not available/blocked - fail silently
        }
    }

    $wire.on('play-chat-notification-sound', () => {
        playChatNotificationSound();
    });
</script>
@endscript
