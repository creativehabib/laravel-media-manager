@php
    $defaultPosition = config('mediamanager.toast.position', 'bottom-right');
    $defaultTimeout  = config('mediamanager.toast.timeout', 3000);
    $defaultMax      = config('mediamanager.toast.max', 4);
@endphp

<div
    x-data="mediaToast({
        position: '{{ $position ?? $defaultPosition }}',
        timeout: {{ $timeout ?? $defaultTimeout }},
        max: {{ $max ?? $defaultMax }},
    })"
    x-on:media-toast.window="enqueue($event.detail)"
    class="fixed z-[9999] pointer-events-none"
    :class="positionClass"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition.opacity.duration.300ms
            x-transition.scale.origin.top.duration.300ms
            class="mb-2 max-w-xs w-80 rounded-md shadow-lg text-sm pointer-events-auto
                   bg-white text-slate-900 border border-slate-200 flex flex-col"
            :class="typeCardClass(toast.type)"
            @mouseenter="pause(toast.id)"
            @mouseleave="resume(toast.id)"
        >
            {{-- content --}}
            <div class="px-4 py-3">
                <div class="flex items-start gap-2">
                    <span class="mt-0.5">
                        <i class="fa-solid"
                           :class="iconClass(toast.type)"
                           aria-hidden="true"></i>
                    </span>
                    <div class="flex-1">
                        <p x-text="toast.message"></p>
                    </div>
                    <button
                        type="button"
                        class="ml-2 text-xs opacity-60 hover:opacity-100"
                        @click="close(toast.id)"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            {{-- progress bar --}}
            <div class="h-[3px] w-full bg-slate-200/70 rounded-b-md overflow-hidden">
                <div
                    class="h-full"
                    :class="progressBarClass(toast.type)"
                    :style="`width: ${toast.progress}%; transition: width 60ms linear;`"
                ></div>
            </div>
        </div>
    </template>
</div>

{{-- Alpine helper --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('mediaToast', (config) => ({
            toasts: [],
            position: config.position || 'bottom-right',
            timeout: config.timeout || 3000,
            max: config.max || 3,

            // ───── Position class ─────
            get positionClass() {
                switch (this.position) {
                    case 'top-right':    return 'top-4 right-4';
                    case 'top-left':     return 'top-4 left-4';
                    case 'bottom-left':  return 'bottom-4 left-4';
                    case 'bottom-right':
                    default:             return 'bottom-4 right-4';
                }
            },

            // Card border/left strip
            typeCardClass(type) {
                switch (type) {
                    case 'warning': return 'border-l-4 border-yellow-500';
                    case 'error':   return 'border-l-4 border-red-600';
                    case 'info':    return 'border-l-4 border-blue-600';
                    case 'success':
                    default:        return 'border-l-4 border-green-500';
                }
            },

            // Progress bar color
            progressBarClass(type) {
                switch (type) {
                    case 'warning': return 'bg-yellow-500';
                    case 'error':   return 'bg-red-600';
                    case 'info':    return 'bg-blue-600';
                    case 'success':
                    default:        return 'bg-green-500';
                }
            },

            // Icon
            iconClass(type) {
                switch (type) {
                    case 'warning': return 'fa-triangle-exclamation text-yellow-500';
                    case 'error':   return 'fa-circle-xmark text-red-600';
                    case 'info':    return 'fa-circle-info text-blue-600';
                    case 'success':
                    default:        return 'fa-circle-check text-green-500';
                }
            },

            // Helper: toast find
            getToast(id) {
                return this.toasts.find(t => t.id === id);
            },

            // enqueue new toast
            enqueue(detail) {
                const id  = Date.now() + Math.random();
                const ttl = detail.timeout ?? this.timeout;
                const now = Date.now();

                const toast = {
                    id,
                    message: detail.message || '',
                    type: detail.type || 'success',
                    visible: true,

                    // timer/progress
                    initialTimeout: ttl,   // total duration
                    remaining: ttl,        // কত ms বাকি
                    deadline: now + ttl,   // সময় শেষের টাইমস্ট্যাম্প
                    progress: 0,           // 0 → 100
                    paused: false,
                    _raf: null,
                };

                this.toasts.push(toast);

                // queue limit
                if (this.toasts.length > this.max) {
                    const old = this.toasts.shift();
                    if (old && old._raf) cancelAnimationFrame(old._raf);
                }

                // animation loop শুরু
                this.startLoop(toast.id);
            },

            // main loop: progress bar + auto close
            startLoop(id) {
                const step = () => {
                    const toast = this.getToast(id);
                    if (!toast) return;

                    if (!toast.visible) {
                        if (toast._raf) cancelAnimationFrame(toast._raf);
                        return;
                    }

                    if (!toast.paused) {
                        const now = Date.now();
                        toast.remaining = Math.max(toast.deadline - now, 0);

                        // 0 → 100%
                        const ratio = 1 - (toast.remaining / toast.initialTimeout);
                        toast.progress = Math.min(Math.max(ratio * 100, 0), 100);

                        if (toast.remaining <= 0) {
                            this.close(id);
                            return;
                        }
                    }

                    toast._raf = requestAnimationFrame(step);
                };

                const toast = this.getToast(id);
                if (!toast) return;
                toast._raf = requestAnimationFrame(step);
            },

            // hover → pause
            pause(id) {
                const toast = this.getToast(id);
                if (!toast) return;
                toast.paused = true;
            },

            // mouse leave → resume
            resume(id) {
                const toast = this.getToast(id);
                if (!toast) return;

                toast.paused   = false;
                toast.deadline = Date.now() + toast.remaining;
            },

            // close with fade-out
            close(id) {
                const toast = this.getToast(id);
                if (!toast) return;

                toast.visible = false;

                if (toast._raf) {
                    cancelAnimationFrame(toast._raf);
                    toast._raf = null;
                }

                // transition শেষ হওয়ার পর আসলেই remove
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 300);
            },
        }))
    });
</script>
