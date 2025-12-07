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

</script>
