<div class="mb-4">
    <label class="block text-sm font-medium mb-1">{{ $label ?? 'Thumbnail' }}</label>

    <div class="flex items-start gap-3">
        <div>
            <img src="{{ $value ?: 'https://placehold.co/640x400/lightgray/gray/jpg?text=No+Image' }}"
                 data-media-preview="{{ $id ?? 'thumbnail' }}"
                 class="w-24 h-24 object-cover border rounded bg-gray-50"
                 alt="thumbnail">
        </div>

        <div class="flex-1 space-y-2">
            <input type="text"
                   name="{{ $name ?? 'thumbnail' }}"
                   id="{{ $id ?? 'thumbnail' }}"
                   data-media-input="{{ $id ?? 'thumbnail' }}"
                   class="border rounded px-2 py-1 w-full text-sm"
                   value="{{ $value ?? '' }}">

            <button type="button"
                    onclick="openMediaManager('{{ $id ?? 'thumbnail' }}')"
                    class="px-3 py-1.5 text-xs rounded bg-blue-600 text-white">
                Choose from media
            </button>
        </div>
    </div>
</div>
