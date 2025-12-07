@php
    $fieldId     = $id ?? 'thumbnail';
    // চাইলে config থেকে নাও
    $placeholder = 'https://placehold.co/200x150?text=No+Image';
@endphp

<div class="space-y-2 pt-1">
    <h3 class="text-xs font-medium text-slate-700">
        {{ $label ?? 'Thumbnail' }}
    </h3>

    {{-- Preview Box --}}
    <div class="flex flex-col rounded-2xl">

        {{-- Image Preview + Remove --}}
        <div class="mb-2 relative">
            <img src="{{ $value ?: $placeholder }}"
                 id="{{ $fieldId }}_preview"
                 data-media-preview="{{ $fieldId }}"
                 data-media-placeholder="{{ $placeholder }}"
                 class="object-cover rounded-2xl border-2 border-dashed border-gray-400 p-1"
                 alt="thumbnail preview">

            {{-- Remove button --}}
            <button
                type="button"
                data-media-clear="{{ $fieldId }}"
                class="absolute top-1 right-1 w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center text-xs shadow cursor-pointer
                       {{ $value ? '' : 'hidden' }}">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Actions --}}
        <div class="flex flex-wrap items-center gap-2 text-[11px]">
            {{-- Choose from Media --}}
            <button
                type="button"
                onclick="openMediaManager('{{ $fieldId }}')"
                class="rounded border border-slate-300 bg-white px-2 py-1.5 text-[11px] font-medium text-slate-700 hover:bg-slate-100">
                Choose image
            </button>

            <span>or</span>

            {{-- Add from URL --}}
            <button
                type="button"
                onclick="openMediaUrlFieldModal('{{ $fieldId }}')"
                class="rounded border border-slate-200 bg-slate-50 px-2 py-1.5 text-[11px] text-slate-600 hover:bg-slate-100 cursor-pointer">
                Add from URL
            </button>
        </div>
    </div>

    {{-- Hidden Input --}}
    <input type="text"
           id="{{ $fieldId }}"
           name="{{ $name ?? 'thumbnail' }}"
           data-media-input="{{ $fieldId }}"
           class="hidden"
           value="{{ $value ?? '' }}"
           @if(isset($name)) wire:model.defer="{{ $name }}" @endif
    >
</div>
