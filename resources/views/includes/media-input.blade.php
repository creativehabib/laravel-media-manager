<div class="space-y-2 pt-1">
    <h3 class="text-xs font-medium text-slate-700">
        {{ $label ?? 'Thumbnail' }}
    </h3>

    {{-- Preview Box --}}
    <div class="flex flex-col items-center justify-center rounded-2xl">

        {{-- Image Preview --}}
        <div class="mb-2">
            <img src="{{ $value ?: 'https://placehold.co/200x150?text=No+Image' }}"
                 data-media-preview="{{ $id ?? 'thumbnail' }}"
                 class="object-cover rounded-2xl border-2 border-dashed border-gray-400 p-1"
                 alt="thumbnail preview">
        </div>

        {{-- Actions --}}
        <div class="flex flex-wrap items-center justify-center gap-2 text-[11px]">
            {{-- Choose from Media --}}
            <button
                type="button"
                onclick="openMediaManager('{{ $id ?? 'thumbnail' }}')"
                class="rounded border border-slate-300 bg-white px-2 py-1.5 text-[11px] font-medium text-slate-700 hover:bg-slate-100">
                Choose image
            </button>

            <span>or</span>
            {{-- Add from URL --}}
            <button
                type="button"
                onclick="document.getElementById('{{ $id ?? 'thumbnail' }}').focus()"
                class="rounded border border-slate-200 bg-slate-50 px-2 py-1.5 text-[11px] text-slate-600 hover:bg-slate-100">
                Add from URL
            </button>
        </div>
    </div>

    {{-- Hidden Input (Livewire binding supported) --}}
    <input type="text"
           id="{{ $id ?? 'thumbnail' }}"
           name="{{ $name ?? 'thumbnail' }}"
           data-media-input="{{ $id ?? 'thumbnail' }}"
           class="hidden"
           value="{{ $value ?? '' }}"
           @if(isset($name)) wire:model.defer="{{ $name }}" @endif
    >
</div>

