<div x-data="mediaManagerModal" x-on:open-media-manager.window="open($event.detail.fieldId)">
    <template x-if="isOpen">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded shadow-lg w-[90vw] max-w-5xl h-[80vh] p-3 flex flex-col">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-sm font-semibold">Media manager</h2>
                    <button type="button" @click="close()" class="text-gray-500 text-lg">&times;</button>
                </div>

                <div class="flex-1 border rounded overflow-hidden">
                    @livewire('media-manager')
                </div>

                <div class="mt-2 flex justify-end gap-2">
                    <button type="button" @click="close()" class="px-3 py-1 text-xs border rounded">
                        Close
                    </button>
                    <button type="button" @click="insertSelected()" class="px-3 py-1 text-xs rounded bg-blue-600 text-white">
                        Insert
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    window.mediaManagerModal = {
        isOpen: false,
        fieldId: null,

        open(fieldId) {
            this.fieldId = fieldId;
            this.isOpen  = true;
            window.livewire.emit('media-manager-opened');
        },

        close() {
            this.isOpen  = false;
            this.fieldId = null;
        },

        insertSelected() {
            window.livewire.emit('media-manager-insert', { fieldId: this.fieldId });
        }
    };

    window.openMediaManager = function (fieldId) {
        window.dispatchEvent(new CustomEvent('open-media-manager', {
            detail: { fieldId }
        }));
    };

    document.addEventListener('livewire:init', () => {
        Livewire.on('media-manager-selected', (payload) => {
            const fieldId = payload.fieldId;
            const url     = payload.url;

            const input   = document.querySelector('[data-media-input="'+ fieldId +'"]');
            const preview = document.querySelector('[data-media-preview="'+ fieldId +'"]');

            if (input)   input.value   = url;
            if (preview) preview.src   = url;

            if (window.mediaManagerModal) {
                window.mediaManagerModal.close();
            }
        });
    });
</script>
