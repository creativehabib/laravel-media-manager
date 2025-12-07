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

    <div class="absolute inset-0" @click="open = false"></div>

    <div class="relative bg-white dark:bg-slate-900 rounded-lg shadow-xl w-[100vw] sm:w-[90vw] lg:w-[75vw] max-h-[90vh] flex flex-col pb-2 overflow-hidden border border-gray-200 dark:border-slate-700">
        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 dark:border-slate-700 shrink-0">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Media gallery</h2>

            <button type="button"
                    class="inline-flex items-center gap-1 border border-gray-200 dark:border-slate-700 px-1.5 py-1.5 rounded hover:bg-gray-50 dark:hover:bg-slate-800 cursor-pointer"
                    @click="open = false">
                <i class="fa-solid fa-close"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto bg-gray-50 dark:bg-slate-950/40">
            @livewire('media-manager', [], key('media-manager-modal'))
        </div>

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

    <div class="absolute inset-0" @click="closeModal()"></div>

    <div class="relative w-full max-w-md bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700
                transform transition-all"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                Add image from URL
            </h3>

            <button @click="closeModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="px-5 py-4 space-y-4">
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

{{-- ========== COMMON JS HELPERS (set/clear field) ========== --}}
<script>
    // field এ URL সেট + preview + remove button টগল
    window.mediaSetField = function (fieldId, url) {
        let input = document.querySelector('[data-media-input="' + fieldId + '"]')
            || document.getElementById(fieldId);

        let preview = document.querySelector('[data-media-preview="' + fieldId + '"]')
            || document.getElementById(fieldId + '_preview');

        let clearBtn = document.querySelector('[data-media-clear="' + fieldId + '"]');

        if (input) {
            input.value = url || '';
            input.dispatchEvent(new Event('input', {bubbles: true}));
        }

        if (preview && url) {
            preview.src = url;
        }

        if (clearBtn) {
            if (url) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }
        }
    };

    // field ক্লিয়ার করা (placeholder এ ফেরত)
    window.mediaClearField = function (fieldId) {
        let input = document.querySelector('[data-media-input="' + fieldId + '"]')
            || document.getElementById(fieldId);

        let preview = document.querySelector('[data-media-preview="' + fieldId + '"]')
            || document.getElementById(fieldId + '_preview');

        let clearBtn = document.querySelector('[data-media-clear="' + fieldId + '"]');
        let placeholder = preview ? preview.getAttribute('data-media-placeholder') : null;

        if (input) {
            input.value = '';
            input.dispatchEvent(new Event('input', {bubbles: true}));
        }

        if (preview && placeholder) {
            preview.src = placeholder;
        }

        if (clearBtn) {
            clearBtn.classList.add('hidden');
        }
    };

    // ❌ আইকনে ক্লিক করলে ক্লিয়ার
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-media-clear]');
        if (!btn) return;

        const fieldId = btn.getAttribute('data-media-clear');
        if (!fieldId) return;

        window.mediaClearField(fieldId);
    });
</script>

{{-- JS – URL modal + Livewire events --}}
<script>
    // field থেকে URL modal ওপেন
    window.openMediaUrlFieldModal = function (fieldId) {
        window._mediaTargetField = fieldId;

        window.dispatchEvent(new CustomEvent('open-add-from-url-field-modal', {
            detail: { fieldId }
        }));
    };

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
                const fieldId = this.fieldId;

                // download == false → শুধু ফিল্ডে URL সেট
                if (!this.download) {
                    window.mediaSetField(fieldId, this.url);
                    this.closeModal();
                    return;
                }

                // download == true → Livewire-এ পাঠাই
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
        window._mediaTargetField    = null;
        window._mediaEditorCallback = null;

        window.openMediaManager = function (fieldName) {
            window._mediaTargetField    = fieldName;
            window._mediaEditorCallback = null;

            window.dispatchEvent(new CustomEvent('open-media-manager'));
        };

        window.openMediaManagerForEditor = function (callback) {
            window._mediaTargetField    = null;
            window._mediaEditorCallback = callback;

            window.dispatchEvent(new CustomEvent('open-media-manager'));
        };

        // Media select → field / editor আপডেট
        Livewire.on('media-selected', (...params) => {
            let data;

            if (params.length === 1 && typeof params[0] === 'object') {
                data = params[0];
            } else {
                const [id, url, name, mime] = params;
                data = { id, url, name, mime };
            }

            const url = data?.url;
            if (!url) return;

            // editor callback থাকলে
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

            // normal input + preview
            const field = window._mediaTargetField;
            if (!field) {
                window.dispatchEvent(new CustomEvent('close-media-manager'));
                return;
            }

            window.mediaSetField(field, url);

            window.dispatchEvent(new CustomEvent('close-media-manager'));
            window._mediaTargetField    = null;
            window._mediaEditorCallback = null;
        });

        // URL থেকে ডাউনলোড শেষ → field আপডেট
        Livewire.on('media-url-downloaded', (payload) => {
            const fieldId = payload?.fieldId;
            const url     = payload?.url;

            if (!fieldId || !url) return;

            window.mediaSetField(fieldId, url);
        });
    });
</script>
