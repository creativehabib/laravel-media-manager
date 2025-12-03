{{-- mediamanager::includes.media-modal --}}
<div
    x-data="{
        open: false,
        selected: null
    }"
    x-on:open-media-manager.window="open = true"
    x-on:close-media-manager.window="open = false"
    x-cloak
    x-show="open"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    x-on:media-selected.window="selected = $event.detail.id"
    x-on:media-unselected.window="selected = null">

    {{-- বাইরে ক্লিক করলে বন্ধ --}}
    <div class="absolute inset-0" @click="open = false"></div>

    {{-- Modal --}}
    <div class="relative bg-white dark:bg-slate-900 rounded-lg shadow-xl w-[100vw] sm:w-[90vw] lg:w-[75vw] max-h-[90vh] flex flex-col pb-2 overflow-hidden border border-gray-200 dark:border-slate-700">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 dark:border-slate-700 shrink-0">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Media gallery</h2>

            <button type="button"
                    class="inline-flex items-center gap-1 border border-gray-200 dark:border-slate-700 px-1.5 py-1.5 rounded hover:bg-gray-50 dark:hover:bg-slate-800 cursor-pointer"
                    @click="open = false">
                <i class="fa-solid fa-close"></i>
            </button>
        </div>

        {{-- Body: Livewire কম্পোনেন্ট --}}
        <div class="flex-1 overflow-y-auto bg-gray-50 dark:bg-slate-950/40">
            @livewire('media-manager', [], key('media-manager-modal'))
        </div>

        {{-- Footer: Insert / Close --}}
        <div class="px-4 py-3 border-t border-gray-200 dark:border-slate-700 flex justify-end gap-2 shrink-0 bg-white dark:bg-slate-900">
            <button type="button" @click="open = false"
                    class="px-3 py-1.5 text-xs border border-gray-200 dark:border-slate-700 rounded-md cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-800 text-gray-800 dark:text-gray-100">
                Close
            </button>

            <button
                type="button"
                @click="Livewire.dispatch('media-insert')"
                :disabled="!selected"
                class="px-3 py-1.5 text-xs rounded-md bg-blue-600 text-white cursor-pointer
                       hover:bg-blue-700
                       disabled:opacity-60 disabled:cursor-not-allowed">
                Insert
            </button>
        </div>
    </div>
</div>

{{-- ===================== ADD FROM URL (FIELD) MODAL ===================== --}}
<div
    x-data="addFromUrlFieldModal()"
    x-on:open-add-from-url-field-modal.window="openModal($event.detail.fieldId)"
    x-show="show"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-[999] flex items-center justify-center bg-black/50 backdrop-blur-sm">

    {{-- Backdrop --}}
    <div class="absolute inset-0" @click="closeModal()"></div>

    {{-- Card --}}
    <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700
                transform transition-all"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                Add image from URL
            </h3>

            <button @click="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-5 py-4 space-y-4">
            {{-- URL input --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                    Image URL
                </label>
                <input type="url"
                       x-model="url"
                       @input="loadPreview()"
                       placeholder="https://example.com/image.jpg"
                       class="w-full rounded-lg border border-slate-300 dark:border-slate-600 px-3 py-2 text-sm
                              bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
            </div>

            {{-- Preview / Loader / Error --}}
            <template x-if="previewLoading">
                <div class="w-full flex justify-center py-4">
                    <i class="fa-solid fa-spinner animate-spin text-slate-500 text-xl"></i>
                </div>
            </template>

            <template x-if="preview && !previewLoading">
                <img :src="preview"
                     class="w-full rounded-lg border border-slate-300 dark:border-slate-700 shadow-sm">
            </template>

            <template x-if="error">
                <p class="text-xs text-red-500" x-text="error"></p>
            </template>

            {{-- Download toggle --}}
            <div class="flex items-center justify-between pt-2">
                <span class="text-sm text-slate-700 dark:text-slate-300">
                    Download image to local storage
                </span>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" x-model="download" class="hidden">
                    <div class="w-10 h-5 rounded-full p-1 flex items-center transition"
                         :class="download ? 'bg-blue-600' : 'bg-slate-400'">
                        <div class="w-4 h-4 rounded-full bg-white shadow transform transition"
                             :class="download ? 'translate-x-5' : ''"></div>
                    </div>
                </label>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex justify-end gap-2 px-5 py-4 border-t border-slate-200 dark:border-slate-700">
            <button @click="closeModal()"
                    class="px-4 py-1.5 text-xs rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 cursor-pointer">
                Cancel
            </button>

            <button @click="apply()"
                    :disabled="!url || error"
                    class="px-4 py-1.5 text-xs rounded bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 cursor-pointer">
                Save
            </button>
        </div>
    </div>
</div>


<x-mediamanager::media-toast position="top-right" timeout="6000" max="4" />

{{-- JS --}}
<script>
    // 👉 global function: field থেকে modal ওপেন করার জন্য
    window.openMediaUrlFieldModal = function (fieldId) {
        // চাইলে _mediaTargetField set করে রাখতে পারো
        window._mediaTargetField = fieldId;

        window.dispatchEvent(new CustomEvent('open-add-from-url-field-modal', {
            detail: { fieldId }
        }));
    };

    // 👉 AlpineJS component for Add-from-URL modal
    function addFromUrlFieldModal() {
        return {
            show: false,
            fieldId: null,
            url: '',
            preview: null,
            previewLoading: false,
            error: null,
            download: true,

            openModal(fieldId) {
                this.show     = true;
                this.fieldId  = fieldId;
                this.url      = '';
                this.preview  = null;
                this.error    = null;
                this.download = true;
            },

            closeModal() {
                this.show = false;
            },

            loadPreview() {
                this.preview = null;
                this.error   = null;

                if (!this.url) return;

                this.previewLoading = true;

                const img = new Image();
                img.onload = () => {
                    this.preview        = this.url;
                    this.previewLoading = false;
                };
                img.onerror = () => {
                    this.error          = 'Invalid image URL';
                    this.previewLoading = false;
                };
                img.src = this.url;
            },

            apply() {
                // target field input + preview
                const fieldId = this.fieldId;

                let input = document.querySelector('[data-media-input="' + fieldId + '"]')
                    || document.getElementById(fieldId);

                let preview = document.querySelector('[data-media-preview="' + fieldId + '"]')
                    || document.getElementById(fieldId + '_preview');

                if (!input) {
                    this.closeModal();
                    return;
                }

                if (!this.download) {
                    // 🔹 শুধু URL সেট করবে (লোকাল ডাউনলোড না করে)
                    input.value = this.url;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    if (preview) preview.src = this.url;

                    this.closeModal();
                    return;
                }

                // 🔥 Download == true → Livewire-এর মাধ্যমে সার্ভারে ডাউনলোড করব
                if (window.Livewire) {
                    Livewire.dispatch('download-from-url', {
                        fieldId: fieldId,
                        url: this.url,
                    });
                }

                this.closeModal();
            }
        }
    }
</script>
<script>
    document.addEventListener('livewire:init', () => {
        // কোন input field / editor টার্গেট সেটা রাখব
        window._mediaTargetField   = null;
        window._mediaEditorCallback = null;

        /**
         * Input + Preview এর জন্য
         * উদাহরণ: openMediaManager('site_logo')
         */
        window.openMediaManager = function (fieldName) {
            window._mediaTargetField    = fieldName;
            window._mediaEditorCallback = null;

            window.dispatchEvent(new CustomEvent('open-media-manager'));
        };

        /**
         * CKEditor বা অন্য যেকোনো editor এর জন্য
         * উদাহরণ: openMediaManagerForEditor((url, data) => { ... })
         */
        window.openMediaManagerForEditor = function (callback) {
            window._mediaTargetField    = null;
            window._mediaEditorCallback = callback;

            window.dispatchEvent(new CustomEvent('open-media-manager'));
        };

        // Livewire → media-selected ইভেন্ট
        Livewire.on('media-selected', (...params) => {
            let data;

            // Case 1: dispatch('media-selected', [ 'id' => ..., 'url' => ... ])
            if (params.length === 1 && typeof params[0] === 'object') {
                data = params[0];
            } else {
                // Case 2: dispatch('media-selected', id: .., url: .., name: .., mime: ..)
                const [id, url, name, mime] = params;
                data = { id, url, name, mime };
            }

            const url = data?.url;
            if (!url) return;

            // 1️⃣ যদি editor callback থাকে → ওখানেই হ্যান্ডেল
            if (typeof window._mediaEditorCallback === 'function') {
                try {
                    window._mediaEditorCallback(url, data);
                } catch (e) {
                    console.error('Media editor callback error:', e);
                }

                window.dispatchEvent(new CustomEvent('close-media-manager'));
                window._mediaEditorCallback = null;
                return;
            }

            // 2️⃣ Normal input + preview মোড
            const field = window._mediaTargetField;
            if (!field) {
                window.dispatchEvent(new CustomEvent('close-media-manager'));
                return;
            }

            // ---- ইনপুট আপডেট ----
            let input = document.querySelector('[data-media-input="'+field+'"]');
            if (!input) input = document.getElementById(field);

            if (input) {
                input.value = url;
                // ✅ Livewire property আপডেটের জন্য input ইভেন্ট
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }

            // ---- প্রিভিউ আপডেট ----
            let preview = document.querySelector('[data-media-preview="'+field+'"]');
            if (!preview) preview = document.getElementById(field + '_preview');

            if (preview) {
                preview.src = url;
            }

            window.dispatchEvent(new CustomEvent('close-media-manager'));
            window._mediaTargetField    = null;
            window._mediaEditorCallback = null;
        });


        // ⬇️ URL থেকে সার্ভার-সাইড ডাউনলোড করার পরে ফিল্ড আপডেট
        Livewire.on('media-url-downloaded', (payload) => {
            const fieldId = payload?.fieldId;
            const url     = payload?.url;

            if (!fieldId || !url) return;

            let input = document.querySelector('[data-media-input="' + fieldId + '"]')
                || document.getElementById(fieldId);

            let preview = document.querySelector('[data-media-preview="' + fieldId + '"]')
                || document.getElementById(fieldId + '_preview');

            if (input) {
                input.value = url;
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }

            if (preview) {
                preview.src = url;
            }
        });

    });
</script>
