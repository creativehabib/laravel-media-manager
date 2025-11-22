@php
    use Illuminate\Support\Str;
@endphp

<div class="h-full flex flex-col gap-3 text-sm relative text-gray-900 dark:text-gray-100" x-data>
    {{-- ========== TOP TOOLBAR ========== --}}
    <div class="flex flex-col gap-2 bg-white dark:bg-slate-900 rounded border border-gray-200 dark:border-slate-700 px-3 py-2 sm:flex-row sm:items-center sm:justify-between">
        {{--Left controls--}}
        <div class="flex flex-wrap items-center gap-2">
            {{-- Upload dropdown: LOCAL + URL --}}
            <div x-data="{ open: false }" class="relative">
                <button type="button"
                        @click="open = !open"
                        class="inline-flex items-center gap-1 bg-blue-600 text-white px-3 py-1.5 rounded cursor-pointer">
                    <i class="fa-solid fa-upload"></i> <span>Upload</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-cloak x-show="open" @click.away="open = false"
                     class="absolute mt-1 w-44 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded shadow z-20 text-xs py-1">
                    <button type="button"
                            @click="$refs.localUpload.click(); open=false"
                            class="w-full flex items-center gap-2 cursor-pointer px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-slate-800">
                        <i class="fa-solid fa-upload"></i> <span>Upload from local</span>
                    </button>

                    <button type="button"
                            @click="$wire.openUrlModal(); open=false"
                            class="w-full flex items-center gap-2 px-3 py-1.5 cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-800">
                        <i class="fa fa-link"></i> <span>Upload from URL</span>
                    </button>
                </div>

                <input type="file"
                       multiple
                       x-ref="localUpload"
                       wire:model="uploads"
                       class="hidden">
            </div>

            <button type="button"
                    wire:click="openFolderModal"
                    class="inline-flex items-center gap-1 border border-gray-200 dark:border-slate-700 px-3 py-1.5 rounded hover:bg-gray-50 dark:hover:bg-slate-800 cursor-pointer">
                <span>
                    <i class="fa-solid fa-folder-plus"></i>
                </span>
            </button>

            {{-- Refresh: wire:target="refreshList" (For Refreshing...) --}}
            <button type="button"
                    wire:click="refreshList"
                    wire:loading.attr="disabled"
                    wire:target="refreshList"
                    class="inline-flex items-center gap-1 border border-gray-200 dark:border-slate-700 px-3 py-1.5 rounded hover:bg-gray-50 dark:hover:bg-slate-800 cursor-pointer">

                {{-- normal icon (লোডিং না থাকলে) --}}
                <span wire:loading.remove wire:target="refreshList">
                    <i class="fa-solid fa-sync"></i>
                </span>

                {{-- loading অবস্থায় spinner icon --}}
                <span wire:loading wire:target="refreshList" class="flex items-center gap-1">
                    <i class="fa-solid fa-circle-notch animate-spin"></i>
                </span>
            </button>

            {{-- Filter dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button type="button"
                        @click="open = !open"
                        class="inline-flex items-center gap-1 border border-gray-200 dark:border-slate-700 px-3 py-1.5 rounded hover:bg-gray-50 dark:hover:bg-slate-800 cursor-pointer">
                    <i class="fa-solid fa-filter"></i>Everything
                </button>

                <div x-cloak x-show="open" @click.away="open = false"
                     class="absolute mt-1 w-56 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded shadow z-10 p-3 space-y-2 text-xs">
                    <div>
                        <label class="font-semibold block mb-1">Type</label>
                        <select wire:model.live="mime"
                                class="w-full border border-gray-300 dark:border-slate-600 rounded px-2 py-1 bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100">
                            <option value="">Everything</option>
                            <option value="image/">Images</option>
                            <option value="video/">Videos</option>
                            <option value="audio/">Audio</option>
                            <option value="application/">Docs</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-semibold block mb-1">Visibility</label>
                        <select wire:model.live="visibility"
                                class="w-full border border-gray-300 dark:border-slate-600 rounded px-2 py-1 bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100">
                            <option value="">All media</option>
                            <option value="public">Public</option>
                            <option value="private">Private</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-semibold block mb-1">Tag</label>
                        <select wire:model.live="tag"
                                class="w-full border border-gray-300 dark:border-slate-600 rounded px-2 py-1 bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100">
                            <option value="">Any tag</option>
                            @foreach($tags as $t)
                                <option value="{{ $t->name }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- All media / Trash / Recent / Favorites dropdown --}}
            <div x-data="{ openAll: false }" class="relative">
                <button type="button"
                        @click="openAll = !openAll"
                        class="inline-flex items-center gap-1 bg-blue-600 text-white px-3 py-1.5 rounded cursor-pointer">
                    <i class="fa-solid fa-globe"></i>
                    <span>
                        @if($scope === 'trash')
                            Trash
                        @elseif($scope === 'recent')
                            Recent
                        @elseif($scope === 'favorites')
                            Favorites
                        @else
                            All media
                        @endif
                    </span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-cloak x-show="openAll" @click.away="openAll = false"
                     class="absolute mt-1 w-44 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded shadow z-20 text-xs py-1">
                    <button type="button"
                            wire:click="setScope('all')"
                            class="w-full flex items-center gap-2 px-3 py-1.5 cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-800 {{ $scope === 'all' ? 'font-semibold' : '' }}">
                        <i class="fa-solid fa-globe"></i><span>All media</span>
                    </button>
                    <button type="button"
                            wire:click="setScope('trash')"
                            class="w-full flex items-center gap-2 px-3 py-1.5 cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-800 {{ $scope === 'trash' ? 'font-semibold' : '' }}">
                        <i class="fa-solid fa-trash"></i> <span>Trash</span>
                    </button>
                    <button type="button"
                            wire:click="setScope('recent')"
                            class="w-full flex items-center gap-2 px-3 py-1.5 cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-800 {{ $scope === 'recent' ? 'font-semibold' : '' }}">
                        <i class="fa-solid fa-clock-rotate-left"></i><span>Recent</span>
                    </button>
                    <button type="button"
                            wire:click="setScope('favorites')"
                            class="w-full flex items-center gap-2 px-3 py-1.5 cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-800 {{ $scope === 'favorites' ? 'font-semibold' : '' }}">
                        <i class="fa-solid fa-star text-yellow-400"></i> <span>Favorites</span>
                    </button>
                </div>
            </div>

            @if($scope === 'trash' && $files->total() > 0)
                <div class="flex items-center gap-2">

                    {{-- Empty trash button --}}
                    <button type="button"
                            wire:click="openEmptyTrashModal"
                            wire:target="openEmptyTrashModal"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded bg-red-600 text-white text-xs cursor-pointer hover:bg-red-700 disabled:opacity-60 disabled:cursor-not-allowed">

                        {{-- Normal --}}
                        <span wire:loading.remove wire:target="openEmptyTrashModal">
                <i class="fa-solid fa-trash-can"></i>
                Empty trash
            </span>

                        {{-- Loading --}}
                        <span wire:loading.flex wire:target="openEmptyTrashModal" class="items-center gap-1">
                <i class="fa-solid fa-circle-notch animate-spin"></i>
                <span>Cleaning...</span>
            </span>
                    </button>

                </div>
            @endif

        </div>

        {{-- Right: Sort + Actions + View mode --}}
        <div class="flex items-center gap-2 justify-end">
            <div class="flex items-center gap-1">
                <span class="text-xs text-gray-500 dark:text-gray-400">Sort</span>
                <select wire:model.live="sort"
                        class="border border-gray-300 dark:border-slate-600 rounded px-3 py-1.5 text-xs cursor-pointer bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100">
                    <option value="name-asc">A–Z</option>
                    <option value="name-desc">Z–A</option>
                    <option value="newest">Newest</option>
                    <option value="oldest">Oldest</option>
                </select>
            </div>

            {{-- Actions dropdown --}}
            @php
                // Livewire Component থেকে $selected প্রপার্টি ব্যবহার
                $selected = $this->selected;
            @endphp
            <div x-data="{ open: false }" class="relative">
                <button type="button"
                        @click="if(@js($selectedId) !== null) open = !open"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded border border-gray-200 dark:border-slate-700 cursor-pointer
                               {{ $selectedId ? 'bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800' : 'bg-gray-100 dark:bg-slate-800 text-gray-400 cursor-not-allowed' }}">
                    <i class="fa-solid fa-hand-pointer"></i>
                    Actions
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-cloak x-show="open" @click.away="open = false"
                     class="absolute right-0 mt-1 w-44 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded shadow-lg z-30 text-xs py-1">

                    <button type="button"
                            wire:click="openPreview"
                            class="w-full px-3 py-1.5 text-left hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                        <i class="fa-regular fa-eye"></i> <span>Preview</span>
                    </button>

                    @if($selected && Str::startsWith($selected->mime_type, 'image/'))
                        <button type="button"
                                wire:click="openCropModal({{ $selected->id }})"
                                class="w-full flex items-center gap-2 px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                            <i class="fa-solid fa-crop-simple"></i>
                            <span>Crop image</span>
                        </button>
                    @endif

                    <button type="button"
                            wire:click="openRenameModal"
                            class="w-full px-3 py-1.5 text-left hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                        <i class="fa-regular fa-pen-to-square"></i> <span>Rename</span>
                    </button>

                    <button type="button"
                            wire:click="makeCopy"
                            class="w-full flex items-center gap-2 px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                        <i class="fa-regular fa-copy"></i> <span>Make a copy</span>
                    </button>

                    <button type="button"
                            wire:click="openAltTextModal"
                            class="w-full flex items-center gap-2 px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                        <i class="fa-regular fa-pen-to-square"></i> <span>ALT text</span>
                    </button>

                    <button type="button"
                            wire:click="copyLink"
                            class="w-full flex items-center gap-2 px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                        <i class="fa-solid fa-link"></i> <span>Copy link</span>
                    </button>

                    <button type="button"
                            wire:click="copyIndirectLink"
                            class="w-full flex items-center gap-2 px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i> <span>Copy indirect link</span>
                    </button>

                    <button type="button"
                            wire:click="share"
                            class="w-full flex items-center gap-2 px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                        <i class="fa-solid fa-share"></i> <span>Share</span>
                    </button>

                    <button type="button"
                            wire:click="addToFavorite"
                            class="w-full flex items-center gap-2 px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                        <i class="fa-solid fa-star text-yellow-400"></i>
                        <span>{{ ($selected && $selected->is_favorite) ? 'Remove favorite' : 'Add favorite' }}</span>
                    </button>

                    <button type="button"
                            wire:click="download"
                            class="w-full flex items-center gap-2 px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                        <i class="fa-solid fa-download"></i> <span>Download</span>
                    </button>

                    <hr class="my-1 border-gray-200 dark:border-slate-700">

                    <button type="button"
                            wire:click="moveToTrash"
                            class="w-full flex items-center gap-2 px-3 py-1.5 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 cursor-pointer">
                        <i class="fa-solid fa-trash"></i> <span>Move to trash</span>
                    </button>
                </div>
            </div>

            <div class="flex border border-gray-200 dark:border-slate-700 rounded overflow-hidden">
                <button type="button"
                        wire:click="setViewMode('grid')"
                        class="px-3 py-1.5 text-xs cursor-pointer {{ $viewMode === 'grid' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-slate-900' }}">
                    <i class="fa-solid fa-table-cells-large"></i>
                </button>
                <button type="button"
                        wire:click="setViewMode('list')"
                        class="px-3 py-1.5 text-xs cursor-pointer {{ $viewMode === 'list' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-slate-900' }}">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- SEARCH + BREADCRUMB --}}
    <div class="bg-white dark:bg-slate-900 rounded border border-gray-200 dark:border-slate-700 px-3 py-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-300">
            <i class="fa-solid fa-image"></i>
            <button type="button" wire:click="setFolder(null)"
                    class="hover:underline cursor-pointer {{ $folder_id ? '' : 'font-semibold text-gray-800 dark:text-gray-100' }}">
                All media
            </button>
            @if($folder_id)
                <span>/</span>
                <span>folder #{{ $folder_id }}</span>
            @endif
        </div>

        <div class="flex items-center gap-2 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <input type="text"
                       wire:model.live.debounce="q"
                       placeholder="Search in current folder"
                       id="liveSearch"
                       class="border border-gray-300 dark:border-slate-600 rounded pl-8 pr-2 py-1.5 text-xs w-full bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-slate-500">
                <span class="absolute left-2 top-1.5 text-gray-400 dark:text-gray-500 text-xs"><i class="fa-solid fa-search"></i> </span>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="flex flex-col gap-3 lg:flex-row">
        <div class="flex-1 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded p-3 max-h-[70vh] overflow-y-auto relative">

            {{-- 🔥 EMPTY STATES FOR ALL SCOPES --}}
            @if($files->total() === 0 && $folders->count() === 0)
                <div class="h-[55vh] flex flex-col items-center justify-center text-center text-gray-500 dark:text-gray-300">

                    {{-- Illustration (নিজের মত path বদলে নাও) --}}
                    @if($scope === 'trash')
                        @include('mediamanager::svg.trash-empty')
                    @elseif($scope === 'favorites')
                        @include('mediamanager::svg.favorites-empty')
                    @elseif($scope === 'recent')
                        @include('mediamanager::svg.recent-empty')
                    @else {{-- all --}}
                    @include('mediamanager::svg.all-empty')
                    @endif
                </div>

            @else
                {{-- 🔽 NORMAL LIST (FOLDERS + FILES) --}}

                @if($folders->count())
                    <div class="mb-3">
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2">
                            @foreach($folders as $f)
                                <button type="button"
                                        wire:click="setFolder({{ $f->id }})"
                                        class="border border-gray-200 dark:border-slate-700 rounded bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700 px-2 py-3 flex flex-col items-center justify-center text-[11px]
                                               {{ $folder_id == $f->id ? 'ring-2 ring-blue-400' : '' }}">
                                    <span class="text-xl mb-1">📁</span>
                                    <span class="truncate w-full text-center">{{ $f->name }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Files (GRID view) --}}
                @if($viewMode === 'grid')
                    <div class="relative min-h-[200px]">

                        {{-- 🔄 SEARCH/FILTER/SCOPE LOADING OVERLAY (Loading...) --}}
                        <div wire:loading.flex
                             wire:target="q, mime, visibility, tag, sort, loadMore, setScope, setFolder"
                             class="absolute inset-0 bg-white/70 dark:bg-slate-900/70 backdrop-blur-sm z-20 flex items-center justify-center rounded">
                            <div class="flex flex-col items-center gap-2 text-xs">
                                <i class="fa-solid fa-circle-notch animate-spin text-lg"></i>
                                <span>Loading...</span>
                            </div>
                        </div>

                        <div wire:loading.remove
                             wire:target="q, mime, visibility, tag, sort, loadMore, setScope, setFolder"
                             class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-2">
                            @forelse($files as $file)
                                <button type="button"
                                        wire:click="selectMedia({{ $file->id }})"
                                        wire:dblclick="openPreview({{ $file->id }})"
                                        x-on:contextmenu.prevent="$wire.call('openContextMenu', {{ $file->id }}, $event.clientX, $event.clientY)"
                                        class="relative flex flex-col items-center text-[11px] cursor-pointer rounded p-1.5 bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700 border-2
                                            @if($selectedId === $file->id)
                                                border-blue-500 ring-2 ring-blue-500/60 bg-blue-50 dark:bg-blue-900/30
                                            @else
                                                border-transparent
                                            @endif">

                                    @if(Str::startsWith($file->mime_type, 'image/'))
                                        <img src="{{ $file->url }}?t={{now()->timestamp}}"
                                             class="w-full h-20 object-cover mb-1 rounded"
                                             alt="{{ $file->alt }}">
                                    @else
                                        <div class="w-full h-20 flex items-center justify-center bg-gray-200 dark:bg-slate-700 rounded mb-1">
                                            📄
                                        </div>
                                    @endif

                                    <div class="w-full truncate text-center">
                                        {{ $file->name }}
                                    </div>

                                    @if($selectedId === $file->id)
                                        <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center text-[10px] shadow">
                                            <i class="fa fa-check"></i>
                                        </span>
                                    @endif
                                </button>
                            @empty
                                <div class="col-span-2 sm:col-span-3 md:col-span-4 lg:col-span-6 xl:col-span-8 text-center text-xs text-gray-400 dark:text-gray-500 py-8">
                                    No media found in this folder.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @else
                    {{-- LIST view --}}
                    <table class="w-full text-[11px]">
                        <tbody>
                        @forelse($files as $file)
                            <tr wire:click="selectMedia({{ $file->id }})" wire:dblclick="openPreview({{ $file->id }})" class="border-b cursor-pointer border-gray-200 dark:border-slate-700 last:border-0 hover:bg-gray-50 dark:hover:bg-slate-800
                               @if($selectedId === $file->id) bg-blue-50 dark:bg-blue-900/30 @endif"
                                x-on:contextmenu.prevent="$wire.call('openContextMenu', {{ $file->id }}, $event.clientX, $event.clientY)">
                                <td class="py-1">
                                    <div class="flex items-center gap-2">
                                        @if(Str::startsWith($file->mime_type, 'image/'))
                                            <img src="{{ $file->url }}?t={{ now()->timestamp }}" class="w-8 h-8 object-cover rounded" alt="">
                                        @else
                                            <span class="w-8 h-8 flex items-center justify-center bg-gray-200 dark:bg-slate-700 rounded text-xs">📄</span>
                                        @endif
                                        <span class="truncate text-left">
                                            {{ $file->name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-1 text-gray-500 dark:text-gray-400">
                                    {{ $file->mime_type }}
                                </td>
                                <td class="py-1 text-gray-500 dark:text-gray-400">
                                    {{ number_format($file->size/1024, 1) }} KB
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="py-4 text-center text-xs text-gray-400 dark:text-gray-500">
                                    No media found in this folder.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                @endif

                {{-- LOAD MORE BUTTON --}}
                @if($files->hasMorePages())
                    <div class="mt-3 text-center">
                        <button type="button"
                                wire:click="loadMore"
                                wire:target="loadMore"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-4 py-1.5 text-xs rounded border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700 cursor-pointer">

                            <span wire:loading.remove wire:target="loadMore">Load more</span>

                            <span wire:loading.flex wire:target="loadMore" class="items-center gap-2">
                                <i class="fa-solid fa-circle-notch animate-spin"></i>
                                <span>Loading...</span>
                            </span>
                        </button>
                    </div>
                @endif
            @endif {{-- end empty-states / list switch --}}

            {{-- 🔄 FULL REFRESH LOADING OVERLAY (Refreshing...) --}}
            {{-- Targets: refreshList (only the dedicated refresh button) --}}
            <div wire:loading.flex
                 wire:target="refreshList"
                 class="absolute inset-0 bg-white/60 dark:bg-slate-900/60 z-40 items-center justify-center text-xs backdrop-blur">
                <div class="flex flex-col items-center gap-2">
                    <i class="fa-solid fa-circle-notch animate-spin text-lg"></i>
                    <span>Refreshing...</span>
                </div>
            </div>

        </div>

        {{-- RIGHT: preview panel --}}
        <div class="w-full lg:w-72 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded p-3 text-xs text-gray-700 dark:text-gray-200 hidden lg:block max-h-[70vh] overflow-y-auto">

            @if($selected)
                {{-- Preview --}}
                <div class="w-full aspect-square border border-gray-200 dark:border-slate-700 rounded flex items-center justify-center mb-3 bg-gray-50 dark:bg-slate-800">
                    @if(Str::startsWith($selected->mime_type, 'image/'))
                        {{-- Add a unique query parameter to force browser refresh --}}
                        <img src="{{ $selected->url }}?t={{ $selected->updated_at?->timestamp }}" class="w-full h-full object-contain rounded" alt="{{ $selected->alt }}">
                    @else
                        <div class="flex items-center justify-center text-4xl">📄</div>
                    @endif
                </div>

                <div class="space-y-3">
                    <div>
                        <div class="font-semibold">Name</div>
                        <div class="bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded px-2 py-1 text-[11px] truncate">
                            {{ $selected->name }}
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-semibold">Full URL</span>
                        </div>

                        <div class="flex items-center gap-1">
                            <input type="text"
                                   class="flex-1 border border-gray-300 dark:border-slate-600 rounded px-2 py-1 text-[11px] bg-gray-50 dark:bg-slate-800 text-gray-900 dark:text-gray-100"
                                   readonly
                                   value="{{ $selected->url }}">

                            <button type="button"
                                    wire:click="copyLink"
                                    class="px-2 py-1 border border-gray-200 dark:border-slate-700 rounded bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700 cursor-pointer"
                                    title="Copy URL">
                                <i class="fa-regular fa-clone text-[11px]"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <div class="font-semibold">Size</div>
                        <div class="bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded px-2 py-1 text-[11px]">
                            {{ number_format($selected->size / 1024, 2) }} KB
                        </div>
                    </div>

                    <div>
                        <div class="font-semibold">Uploaded at</div>
                        <div class="bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded px-2 py-1 text-[11px]">
                            {{ $selected->created_at?->format('Y-m-d H:i:s') ?? '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="font-semibold">Modified at</div>
                        <div class="bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded px-2 py-1 text-[11px]">
                            {{ $selected->updated_at?->format('Y-m-d H:i:s') ?? '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="font-semibold">Alt text</div>
                        <div class="bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded px-2 py-1 text-[11px] truncate">
                            {{ $selected->alt ?? '—' }}
                        </div>
                    </div>

                    @if(!empty($selected->width) || !empty($selected->height))
                        <div>
                            <div class="font-semibold">Dimensions</div>
                            <div class="bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded px-2 py-1 text-[11px]">
                                {{ $selected->width ?? '—' }}px
                                @if(!empty($selected->height))
                                    × {{ $selected->height }}px
                                @endif
                            </div>
                        </div>
                    @endif

                    <div>
                        <div class="font-semibold">MIME type</div>
                        <div class="bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded px-2 py-1 text-[11px] truncate">
                            {{ $selected->mime_type }}
                        </div>
                    </div>
                </div>
            @else
                <div class="w-full aspect-square border border-gray-200 dark:border-slate-700 rounded flex items-center justify-center mb-3 bg-gray-50 dark:bg-slate-800">
                    <i class="fa-solid fa-image text-gray-400 dark:text-gray-500 text-8xl"></i>
                </div>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                    কোনো ফাইল সিলেক্ট করলে এখানে প্রিভিউ ও তথ্য দেখাবে।
                </p>
            @endif
        </div>
    </div>

    {{-- URL UPLOAD MODAL --}}
    @if($showUrlModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40"
             wire:click.self="closeUrlModal">

            <div class="bg-white dark:bg-slate-900 rounded shadow-lg w-full max-w-md p-4 border border-gray-200 dark:border-slate-700">

                <h3 class="text-sm font-semibold mb-3">Upload from URL</h3>

                <div class="mb-3">
                    <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">File URL</label>
                    <input type="text"
                           wire:model.defer="urlInput"
                           class="w-full border border-gray-300 dark:border-slate-600 rounded px-3 py-2 text-xs bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-slate-500"
                           placeholder="https://example.com/image.jpg" autofocus>

                    @error('urlInput')
                    <div class="text-red-500 text-[11px] mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 mt-2">
                    <button type="button"
                            wire:click="closeUrlModal"
                            class="px-3 py-1.5 text-xs border border-gray-200 dark:border-slate-700 rounded cursor-pointer bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700">
                        Cancel
                    </button>

                    {{-- 🔥 Upload button with spinner --}}
                    <button type="button"
                            wire:click="uploadFromUrl"
                            wire:target="uploadFromUrl"
                            wire:loading.attr="disabled"
                            class="px-3 py-1.5 text-xs rounded bg-blue-600 text-white cursor-pointer flex items-center gap-1">

                        <span wire:loading.remove wire:target="uploadFromUrl">
                            Upload
                        </span>

                        <span wire:loading.flex wire:target="uploadFromUrl" class="items-center gap-1">
                            <i class="fa-solid fa-circle-notch animate-spin"></i>
                            Uploading...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ALT TEXT MODAL --}}
    @if($showAltModal)
        @php
            $current = $selectedId ? $files->firstWhere('id', $selectedId) : null;
            $ext = $current ? strtoupper(pathinfo($current->name, PATHINFO_EXTENSION) ?: 'FILE') : '';
        @endphp

        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
             wire:click.self="closeAltTextModal">
            <div class="bg-white dark:bg-slate-900 rounded shadow-lg w-full max-w-md border border-gray-200 dark:border-slate-700">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-slate-700">
                    <h3 class="text-sm font-semibold">Alt text</h3>
                    <button type="button"
                            wire:click="closeAltTextModal"
                            class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-lg leading-none">&times;</button>
                </div>

                <div class="p-4 space-y-3">
                    <div class="border border-gray-300 dark:border-slate-600 rounded flex items-center overflow-hidden">
                        @if($ext)
                            <span class="px-3 py-2 text-[11px] uppercase bg-gray-100 dark:bg-slate-800 border-r border-gray-200 dark:border-slate-700 text-gray-500 dark:text-gray-300">
                                {{ $ext }}
                            </span>
                        @endif
                        <input type="text"
                               wire:model.defer="altTextInput"
                               class="flex-1 px-3 py-2 text-sm outline-none bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-slate-500"
                               placeholder="Describe this image for accessibility">
                    </div>
                    @error('altTextInput')
                    <div class="text-red-500 text-[11px] mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="px-4 py-3 border-t border-gray-200 dark:border-slate-700 flex justify-end gap-2">
                    <button type="button"
                            wire:click="closeAltTextModal"
                            class="px-3 py-1.5 text-xs border border-gray-200 dark:border-slate-700 rounded bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700">
                        Close
                    </button>
                    <button type="button"
                            wire:click="saveAltText"
                            wire:target="saveAltText"
                            wire:loading.attr="disabled"
                            class="px-3 py-1 text-xs rounded bg-blue-600 text-white cursor-pointer flex items-center gap-1">
                        <span wire:loading.remove wire:target="saveAltText">
                            Save Change
                        </span>

                        <span wire:loading.flex wire:target="saveAltText" class="items-center gap-1">
                            <i class="fa-solid fa-circle-notch animate-spin"></i>
                            Saving...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- RENAME MODAL --}}
    @if($showRenameModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
             wire:click.self="closeRenameModal">
            <div class="bg-white rounded shadow-lg w-full max-w-sm">
                <div class="flex items-center justify-between px-4 py-3 border-b">
                    <h3 class="text-sm font-semibold">Rename file</h3>
                    <button type="button"
                            wire:click="closeRenameModal"
                            class="text-gray-400 hover:text-gray-700 text-lg leading-none">&times;</button>
                </div>

                <div class="p-4 space-y-3">
                    <label class="block text-xs text-gray-600 mb-1">New name</label>
                    <input type="text"
                           wire:model.defer="renameInput"
                           class="w-full border rounded px-2 py-1.5 text-sm">
                    @error('renameInput')
                    <div class="text-red-500 text-[11px] mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="px-4 py-3 border-t flex justify-end gap-2">
                    <button type="button"
                            wire:click="closeRenameModal"
                            class="px-3 py-1.5 text-xs border rounded">
                        Cancel
                    </button>

                    <button type="button"
                            wire:click="saveRename"
                            wire:target="saveRename"
                            wire:loading.attr="disabled"
                            class="px-3 py-1 text-xs rounded bg-blue-600 text-white cursor-pointer flex items-center gap-1">
                        <span wire:loading.remove wire:target="saveRename">
                            Save
                        </span>

                        <span wire:loading.flex wire:target="saveRename" class="items-center gap-1">
                            <i class="fa-solid fa-circle-notch animate-spin"></i>
                            Saving...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- IMAGE CROP MODAL --}}
    @if($showCropModal && $cropFileId)
        @php
            $cropFile = $files->firstWhere('id', $cropFileId)
                ?? \Habib\MediaManager\Models\MediaFile::find($cropFileId);
        @endphp

        @if($cropFile && Str::startsWith($cropFile->mime_type, 'image/'))
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                 wire:click.self="closeCropModal">
                <div class="bg-white dark:bg-slate-900 rounded shadow-lg w-full max-w-5xl border border-gray-200 dark:border-slate-700 flex flex-col overflow-hidden">

                    {{-- Header --}}
                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-slate-700">
                        <h3 class="text-sm font-semibold">Crop</h3>
                        <button type="button"
                                wire:click="closeCropModal"
                                class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-lg leading-none cursor-pointer">
                            &times;
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="px-4 py-4 flex gap-4">
                        {{-- Left: image --}}
                        <div class="flex-1 max-h-[70vh] overflow-auto border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 flex items-center justify-center">
                            <img
                                id="cropper-image"
                                src="{{ $cropFile->url }}"
                                class="max-w-full h-auto block"
                                alt="Crop image">
                        </div>

                        {{-- Right: controls --}}
                        <div class="w-56 space-y-4 text-xs text-gray-700 dark:text-gray-200">
                            {{-- Optimize / compress options --}}
                            <div class="border-t border-gray-200 dark:border-slate-700 pt-3 mt-2 space-y-2">
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" wire:model="cropOptimize"
                                           class="rounded border-gray-300 dark:border-slate-600">
                                    <span>Optimize / compress</span>
                                </label>

                                <div class="flex gap-2">
                                    <div class="flex-1">
                                        <label class="block mb-1">Max width</label>
                                        <input type="number" min="0"
                                               wire:model="cropMaxWidth"
                                               class="w-full border border-gray-300 dark:border-slate-600 rounded px-2 py-1 bg-white dark:bg-slate-900"
                                               placeholder="auto">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block mb-1">Max height</label>
                                        <input type="number" min="0"
                                               wire:model="cropMaxHeight"
                                               class="w-full border border-gray-300 dark:border-slate-600 rounded px-2 py-1 bg-white dark:bg-slate-900"
                                               placeholder="auto">
                                    </div>
                                </div>

                                <div>
                                    <label class="block mb-1">Quality (10–100)</label>
                                    <input type="number" min="10" max="100"
                                           wire:model="cropQuality"
                                           class="w-full border border-gray-300 dark:border-slate-600 rounded px-2 py-1 bg-white dark:bg-slate-900">
                                </div>

                                <div>
                                    <label class="block mb-1">Format</label>
                                    <select wire:model="cropFormat"
                                            class="w-full border border-gray-300 dark:border-slate-600 rounded px-2 py-1 bg-white dark:bg-slate-900">
                                        <option value="keep">Keep original</option>
                                        <option value="webp">Convert to WebP</option>
                                        <option value="jpeg">Convert to JPEG</option>
                                        <option value="png">Convert to PNG</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label for="cropper-height" class="block mb-1 font-semibold">Height</label>
                                <input type="number"
                                       id="cropper-height"
                                       class="w-full border border-gray-300 dark:border-slate-600 rounded px-2 py-1 bg-white dark:bg-slate-900">
                            </div>

                            <div>
                                <label for="cropper-width" class="block mb-1 font-semibold">Width</label>
                                <input type="number"
                                       id="cropper-width"
                                       class="w-full border border-gray-300 dark:border-slate-600 rounded px-2 py-1 bg-white dark:bg-slate-900">
                            </div>

                            <div class="pt-1">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox"
                                           id="cropper-aspect"
                                           class="rounded border-gray-300 dark:border-slate-600">
                                    <span>Aspect ratio</span>
                                </label>
                                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                    Aspect ratio অন করলে current crop ratio lock থাকবে।
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-slate-700 flex justify-end gap-2">
                        <button type="button"
                                wire:click="closeCropModal"
                                class="px-3 py-1.5 text-xs border border-gray-200 dark:border-slate-700 rounded bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700 cursor-pointer">
                            Close
                        </button>

                        <button type="button"
                                id="cropper-apply-btn"
                                class="px-3 py-1.5 text-xs rounded bg-blue-600 text-white cursor-pointer flex items-center gap-1 hover:bg-blue-700">
                            <span>Crop</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif


    {{-- EMPTY TRASH CONFIRM MODAL --}}
    @if($showEmptyTrashModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
             wire:click.self="closeEmptyTrashModal">
            <div class="bg-white dark:bg-slate-900 rounded shadow-lg w-full max-w-md border border-gray-200 dark:border-slate-700">

                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-slate-700">
                    <h3 class="text-sm font-semibold">Empty trash</h3>
                    <button type="button"
                            wire:click="closeEmptyTrashModal"
                            class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-lg leading-none">
                        &times;
                    </button>
                </div>

                <div class="px-4 py-5 text-sm">
                    <p>
                        This action is irreversible. Are you sure you want to permanently delete all items in trash?
                    </p>
                </div>

                <div class="px-4 py-3 border-t border-gray-200 dark:border-slate-700 flex justify-end gap-2">
                    <button type="button"
                            wire:click="closeEmptyTrashModal"
                            class="px-3 py-1.5 text-xs border border-gray-200 dark:border-slate-700 rounded bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700">
                        Close
                    </button>

                    <button type="button"
                            wire:click="confirmEmptyTrash"
                            wire:target="confirmEmptyTrash"
                            wire:loading.attr="disabled"
                            class="px-3 py-1.5 text-xs rounded bg-red-600 text-white cursor-pointer flex items-center gap-1 hover:bg-red-700 disabled:opacity-60 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="confirmEmptyTrash">
                        Confirm
                    </span>
                        <span wire:loading.flex wire:target="confirmEmptyTrash" class="items-center gap-1">
                        <i class="fa-solid fa-circle-notch animate-spin"></i>
                        <span>Deleting...</span>
                    </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- DELETE PERMANENTLY CONFIRM MODAL --}}
    @if($showDeletePermanentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
             wire:click.self="closeDeletePermanentModal">
            <div class="bg-white dark:bg-slate-900 rounded shadow-lg w-full max-w-md border border-gray-200 dark:border-slate-700">

                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-slate-700">
                    <h3 class="text-sm font-semibold">Delete permanently</h3>
                    <button type="button"
                            wire:click="closeDeletePermanentModal"
                            class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-lg leading-none">
                        &times;
                    </button>
                </div>

                <div class="px-4 py-5 text-sm">
                    <p>
                        This action is irreversible. Are you sure you want to permanently delete this item?
                    </p>
                </div>

                <div class="px-4 py-3 border-t border-gray-200 dark:border-slate-700 flex justify-end gap-2">
                    <button type="button"
                            wire:click="closeDeletePermanentModal"
                            class="px-3 py-1.5 text-xs border border-gray-200 dark:border-slate-700 rounded bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700">
                        Close
                    </button>

                    <button type="button"
                            wire:click="confirmDeletePermanent"
                            wire:target="confirmDeletePermanent"
                            wire:loading.attr="disabled"
                            class="px-3 py-1.5 text-xs rounded bg-red-600 text-white cursor-pointer flex items-center gap-1 hover:bg-red-700 disabled:opacity-60 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="confirmDeletePermanent">
                        Confirm
                    </span>
                        <span wire:loading.flex wire:target="confirmDeletePermanent" class="items-center gap-1">
                        <i class="fa-solid fa-circle-notch animate-spin"></i>
                        <span>Deleting...</span>
                    </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MOVE TO TRASH CONFIRM MODAL --}}
    @if($showMoveToTrashModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
             wire:click.self="closeMoveToTrashModal">
            <div class="bg-white dark:bg-slate-900 rounded shadow-lg w-full max-w-md border border-gray-200 dark:border-slate-700">

                {{-- Header --}}
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-slate-700">
                    <h3 class="text-sm font-semibold">Move items to trash</h3>
                    <button type="button"
                            wire:click="closeMoveToTrashModal"
                            class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-lg leading-none">
                        &times;
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-4 py-5 space-y-4 text-sm">
                    <p>Are you sure you want to move this item to trash?</p>

                    <div class="flex items-start gap-2">
                        <input type="checkbox"
                               id="skipTrash"
                               wire:model="skipTrash"
                               class="mt-0.5 rounded border-gray-300 dark:border-slate-600">
                        <div>
                            <label for="skipTrash" class="text-sm font-medium cursor-pointer">
                                Skip trash
                            </label>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                If it is checked, the file will be deleted permanently without moving to trash.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-4 py-3 border-t border-gray-200 dark:border-slate-700 flex justify-end gap-2">
                    <button type="button"
                            wire:click="closeMoveToTrashModal"
                            class="px-3 py-1.5 text-xs border border-gray-200 dark:border-slate-700 rounded bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700">
                        Close
                    </button>

                    <button type="button"
                            wire:click="confirmMoveToTrash"
                            wire:target="confirmMoveToTrash"
                            wire:loading.attr="disabled"
                            class="px-3 py-1.5 text-xs rounded bg-red-600 text-white cursor-pointer flex items-center gap-1 hover:bg-red-700 disabled:opacity-60 disabled:cursor-not-allowed">

                    <span wire:loading.remove wire:target="confirmMoveToTrash">
                        Confirm
                    </span>

                        <span wire:loading.flex wire:target="confirmMoveToTrash" class="items-center gap-1">
                        <i class="fa-solid fa-circle-notch animate-spin"></i>
                        <span>Processing...</span>
                    </span>
                    </button>
                </div>
            </div>
        </div>
    @endif


    {{-- ========== RIGHT CLICK CONTEXT MENU ========== --}}

    @if($contextMenu['show'])
        <div class="fixed inset-0 z-40" wire:click="closeContextMenu"></div>

        <div class="fixed z-50 bg-white dark:bg-slate-900 border rounded shadow-lg w-44 text-xs py-1 dark:border-slate-800"
             style="top: {{ $contextMenu['y'] }}px; left: {{ $contextMenu['x'] }}px;">

            {{-- যদি Trash scope এ থাকি তাহলে এই মেনু --}}
            @if($scope === 'trash')
                <button type="button"
                        wire:click="openPreview({{ $contextMenu['fileId'] }})"
                        class="w-full px-3 py-1.5 text-left hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                    <i class="fa-regular fa-eye"></i> <span>Preview</span>
                </button>

                <button type="button"
                        wire:click="openRenameModal"
                        class="w-full px-3 py-1.5 text-left hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                    <i class="fa-regular fa-pen-to-square"></i> <span>Rename</span>
                </button>

                <button type="button"
                        wire:click="download"
                        class="w-full px-3 py-1.5 text-left hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                    <i class="fa-solid fa-download"></i> <span>Download</span>
                </button>

                <button type="button"
                        wire:click="openDeletePermanentModal({{ $contextMenu['fileId'] }})"
                        class="w-full px-3 py-1.5 text-left hover:bg-red-50 dark:hover:bg-slate-800 text-red-600 cursor-pointer">
                    <i class="fa-solid fa-trash-can"></i> <span>Delete permanently</span>
                </button>

                <button type="button"
                        wire:click="restoreFromTrash"
                        class="w-full px-3 py-1.5 text-left hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                    <i class="fa-solid fa-rotate-left"></i> <span>Restore</span>
                </button>

            @else
                {{-- normal (all / recent / favorites) context menu --}}
                <button type="button"
                        wire:click="openPreview({{ $contextMenu['fileId'] }})"
                        class="w-full px-3 py-1.5 text-left hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                    <i class="fa-regular fa-eye"></i> <span>Preview</span>
                </button>

                @if(Str::startsWith(optional($files->firstWhere('id', $contextMenu['fileId']))->mime_type, 'image/'))
                    <button type="button"
                            wire:click="openCropModal({{ $contextMenu['fileId'] }})"
                            class="w-full px-3 py-1.5 text-left hover:bg-gray-100 cursor-pointer">
                        <i class="fa-solid fa-crop-simple"></i> <span>Crop image</span>
                    </button>
                @endif

                <button type="button"
                        wire:click="openRenameModal"
                        class="w-full px-3 py-1.5 text-left hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                    <i class="fa-regular fa-pen-to-square"></i> <span>Rename</span>
                </button>

                <button type="button"
                        wire:click="makeCopy"
                        class="w-full px-3 py-1.5 text-left hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                    <i class="fa-solid fa-copy"></i> <span>Make a copy</span>
                </button>

                <button type="button"
                        wire:click="openAltTextModal"
                        class="w-full px-3 py-1.5 text-left hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                    <i class="fa-regular fa-pen-to-square"></i> <span>Alt text</span>
                </button>

                <button type="button"
                        wire:click="copyLink"
                        class="w-full px-3 py-1.5 text-left hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                    <i class="fa-solid fa-link"></i> <span>Copy link</span>
                </button>

                <button type="button"
                        wire:click="copyIndirectLink"
                        class="w-full px-3 py-1.5 text-left hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> <span>Copy indirect link</span>
                </button>

                <button type="button"
                        wire:click="addToFavorite"
                        class="w-full flex items-center gap-2 px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                    <i class="fa-solid fa-star text-yellow-400"></i>
                    <span>{{ ($selected && $selected->is_favorite) ? 'Remove favorite' : 'Add favorite' }}</span>
                </button>

                <button type="button"
                        wire:click="download"
                        class="w-full px-3 py-1.5 text-left hover:bg-gray-100 dark:hover:bg-slate-800 cursor-pointer">
                    <i class="fa-solid fa-download"></i> <span>Download</span>
                </button>

                <button type="button"
                        wire:click="moveToTrash"
                        class="w-full px-3 py-1.5 text-left hover:bg-red-50 text-red-600 dark:hover:bg-slate-800 cursor-pointer">
                    <i class="fa-solid fa-trash"></i> <span>Move to trash</span>
                </button>
            @endif
        </div>
    @endif

    {{-- FULLSCREEN IMAGE PREVIEW (Botble style) --}}
    @if($showPreview && $selectedId)
        @php
            $previewFile = \Habib\MediaManager\Models\MediaFile::withTrashed()->find($selectedId);
        @endphp

        @if($previewFile)
            <div
                class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center"
                wire:click.self="closePreview"
                x-data="{
                isFullscreen: false,
                toggleFullscreen() {
                    const el = this.$refs.previewWrapper;

                    // already fullscreen?
                    if (!document.fullscreenElement) {
                        if (el.requestFullscreen) {
                            el.requestFullscreen();
                        } else if (el.webkitRequestFullscreen) {   // Safari
                            el.webkitRequestFullscreen();
                        } else if (el.msRequestFullscreen) {       // old IE
                            el.msRequestFullscreen();
                        }
                        this.isFullscreen = true;
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        } else if (document.webkitExitFullscreen) {
                            document.webkitExitFullscreen();
                        } else if (document.msExitFullscreen) {
                            document.msExitFullscreen();
                        }
                        this.isFullscreen = false;
                    }
                }
            }"
                x-on:keydown.escape.window="$wire.call('closePreview')"
                x-ref="previewWrapper"
            >

                {{-- Close icon --}}
                <button type="button"
                        wire:click="closePreview"
                        class="absolute top-4 right-4 text-white/80 hover:text-white text-2xl leading-none cursor-pointer">
                    <i class="fa-solid fa-close"></i>
                </button>

                {{-- 🔍 Fullscreen toggle button --}}
                <button type="button"
                        @click.stop="toggleFullscreen()"
                        class="absolute top-4 right-16 text-white/80 hover:text-white text-xl cursor-pointer">
                    <i class="fa-solid fa-expand"></i>
                </button>

                {{-- Centered big image --}}
                <img src="{{ $previewFile->url }}?t={{ $previewFile->updated_at?->timestamp }}"
                     alt="{{ $previewFile->alt ?? $previewFile->name }}"
                     class="max-w-[95vw] max-h-[90vh] object-contain rounded shadow-2xl select-none">
            </div>
        @endif
    @endif


    {{-- ========== CREATE FOLDER MODAL ========== --}}
    @if($showFolderModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
             wire:click.self="closeFolderModal">
            <div class="bg-white dark:bg-slate-900 rounded shadow-lg w-full max-w-sm border border-gray-200 dark:border-slate-700">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-slate-700">
                    <h3 class="text-sm font-semibold">Create new folder</h3>
                    <button type="button"
                            wire:click="closeFolderModal"
                            class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 text-lg leading-none cursor-pointer">
                        &times;
                    </button>
                </div>

                <div class="p-4 space-y-3">
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-300 mb-1">Folder name</label>
                        <input type="text"
                               wire:model.defer="newFolderName"
                               class="w-full border border-gray-300 dark:border-slate-600 rounded px-2 py-1.5 text-sm bg-white dark:bg-slate-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-slate-500"
                               placeholder="e.g. banners, categories">
                        @error('newFolderName')
                        <div class="text-red-500 text-[11px] mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="px-4 py-3 border-t border-gray-200 dark:border-slate-700 flex justify-end gap-2">
                    <button type="button"
                            wire:click="closeFolderModal"
                            class="px-3 py-1.5 text-xs border border-gray-200 dark:border-slate-700 rounded cursor-pointer bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700">
                        Cancel
                    </button>
                    <button type="button"
                            wire:click="createFolder"
                            wire:loading.attr="disabled"
                            class="px-3 py-1.5 text-xs rounded bg-blue-600 text-white cursor-pointer">
                        Create
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div wire:loading.flex
         wire:target="uploads"
         class="absolute inset-0 z-50 bg-white/70 backdrop-blur flex items-center justify-center text-xs">

        <div class="flex flex-col items-center gap-2">
            <i class="fa-solid fa-circle-notch animate-spin text-lg"></i>
            <span>Uploading...</span>
        </div>
    </div>
</div>

<script>
    if (!window.__mediaCopyListenerAdded) {
        window.__mediaCopyListenerAdded = true;

        document.addEventListener('livewire:init', () => {
            Livewire.on('media-copy-link', (payload) => {
                const url = payload?.url || (Array.isArray(payload) ? payload[0]?.url : null);
                if (!url) return;

                const copyFallback = (text) => {
                    const temp = document.createElement('input');
                    temp.value = text;
                    document.body.appendChild(temp);
                    temp.select();
                    document.execCommand('copy');
                    document.body.removeChild(temp);
                };

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).catch(() => copyFallback(url));
                } else {
                    copyFallback(url);
                }

                // চাইলে এখানে তোমার নিজের toast system কল করতে পারো
                // উদাহরণ: window.dispatchEvent(new CustomEvent('media-toast', {...}))
                console.log('Media link copied:', url);
            });

            // ⬇️ Download
            Livewire.on('media-download', (payload) => {
                const url = payload?.url || (Array.isArray(payload) ? payload[0]?.url : null);
                if (!url) return;

                // নতুন ট্যাবে / same tab-এ ওপেন করতে পারো
                const a = document.createElement('a');
                a.href = url;
                a.target = '_blank';   // চাইলে '_self' করে দিতে পারো
                a.download = '';       // ব্রাউজারকে hint দেয় "download" করার জন্য
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            });

            // ========= CROP SYSTEM (SENSITIVE PART) =========
            let cropper = null;

            Livewire.on('init-cropper', (payload) => {
                const component = Livewire.find(payload.id);

                setTimeout(() => {
                    const img    = document.getElementById('cropper-image');
                    const hInput = document.getElementById('cropper-height');
                    const wInput = document.getElementById('cropper-width');
                    const aspect = document.getElementById('cropper-aspect');
                    const btn    = document.getElementById('cropper-apply-btn');

                    if (!img || typeof Cropper === 'undefined') {
                        console.error('CropperJS or image not found');
                        return;
                    }

                    if (cropper) {
                        cropper.destroy();
                        cropper = null;
                    }

                    // Aspect ratio-কে ডিফল্টভাবে আনচেক নিশ্চিত করি (Fix for Free Cropping)
                    if (aspect) {
                        aspect.checked = false;
                    }


                    cropper = new Cropper(img, {
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.8,
                        responsive: true,
                        background: false,
                        aspectRatio: NaN, // নিশ্চিত করি যে ডিফল্ট মোড free-form

                        ready() {
                            const data = cropper.getData(true);
                            if (hInput) hInput.value = Math.round(data.height || 0);
                            if (wInput) wInput.value = Math.round(data.width || 0);

                            // initial free crop mode যদি চেক না থাকে
                            if(aspect && !aspect.checked) {
                                cropper.setAspectRatio(NaN);
                            }
                        },

                        crop() {
                            const data = cropper.getData(true);
                            // যদি ইনপুট ফিল্ড active না থাকে, তবে cropper এর মান দেখাও
                            if (hInput && document.activeElement !== hInput) {
                                hInput.value = Math.round(data.height || 0);
                            }
                            if (wInput && document.activeElement !== wInput) {
                                wInput.value = Math.round(data.width || 0);
                            }
                        },
                    });

                    // ========= Aspect ratio toggle (Fixed Logic) =========
                    if (aspect && !aspect.dataset.bound) {
                        aspect.dataset.bound = '1';
                        aspect.addEventListener('change', () => {
                            if (!cropper) return;

                            if (aspect.checked) {
                                const data  = cropper.getData(true);
                                const ratio = data.width && data.height
                                    ? data.width / data.height
                                    : NaN;

                                if (ratio && !isNaN(ratio)) {
                                    cropper.setAspectRatio(ratio);
                                } else {
                                    // Fallback: যদি কোনো এরিয়া সিলেক্ট করা না থাকে, তবে 16:9 ডিফল্ট
                                    cropper.setAspectRatio(16 / 9);
                                }

                            } else {
                                // Free Mode
                                cropper.setAspectRatio(NaN);
                            }
                        });
                    }

                    // ========= Height/width ইনপুট থেকে crop আপডেট (Fixed Logic) =========
                    const bindSizeInput = (input, dimension) => {
                        if (!input || input.dataset.bound) return;

                        input.dataset.bound = '1';

                        input.addEventListener('input', () => {
                            if (!cropper) return;

                            const val = parseInt(input.value || '0', 10);
                            if (!val || val <= 0) return;

                            const data = cropper.getData(true);
                            const currentRatio = data.width / data.height;

                            let newData = { x: data.x, y: data.y, width: data.width, height: data.height };

                            if (dimension === 'height') {
                                newData.height = val;

                                if (aspect && aspect.checked && !isNaN(currentRatio)) {
                                    // Aspect Ratio Lock থাকলে width-কেও আপডেট করি
                                    newData.width = Math.round(val * currentRatio);
                                    if (wInput) wInput.value = newData.width;
                                }
                            } else { // dimension === 'width'
                                newData.width = val;

                                if (aspect && aspect.checked && !isNaN(currentRatio)) {
                                    // Aspect Ratio Lock থাকলে height-কেও আপডেট করি
                                    newData.height = Math.round(val / currentRatio);
                                    if (hInput) hInput.value = newData.height;
                                }
                            }

                            // নতুন ডাটা দিয়ে ক্রপার আপডেট
                            cropper.setData(newData);
                        });
                    };

                    bindSizeInput(hInput, 'height');
                    bindSizeInput(wInput, 'width');

                    // ========= Crop button: Livewire call with pixel data =========
                    if (btn && !btn.dataset.bound) {
                        btn.dataset.bound = '1';

                        btn.addEventListener('click', () => {
                            if (!cropper || !component) return;

                            // সিলেক্ট করা এরিয়ার pixel data সংগ্রহ
                            const data = cropper.getData(true);

                            component.call('saveCroppedImage', {
                                x: Math.round(data.x),
                                y: Math.round(data.y),
                                width: Math.round(data.width),
                                height: Math.round(data.height),
                            });
                        });
                    }
                }, 50);
            });
        });
    }
</script>
