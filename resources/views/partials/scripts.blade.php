{{-- Media Manager Package Scripts --}}

{{-- CropperJS CDN (Must be loaded for image cropping functionality) --}}
<script src="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css">
{{-- Tabler icons CDN (For specific icons used in older toast/styles, though FontAwesome is primary) --}}
<link rel="stylesheet" href="https://unpkg.com/@tabler/icons-webfont@latest/tabler-icons.min.css">


<script>
    if (!window.__mediaCopyListenerAdded) {
        window.__mediaCopyListenerAdded = true;

        document.addEventListener('livewire:init', () => {

            // ⬇️ Livewire.on('media-copy-link') লিসেনার:
            // এটি Livewire Component (MediaManager.php) থেকে আসা 'media-copy-link' ইভেন্টটি হ্যান্ডেল করে।
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

                // Alpine Toast সিস্টেম কল
                window.dispatchEvent(new CustomEvent('media-toast', {
                    detail: { type: 'success', message: 'Link copied!' }
                }));

                console.log('Media link copied:', url);
            });

            // ⬇️ Livewire.on('media-download') লিসেনার:
            Livewire.on('media-download', (payload) => {
                const url = payload?.url || (Array.isArray(payload) ? payload[0]?.url : null);
                if (!url) return;

                const a = document.createElement('a');
                a.href = url;
                a.target = '_blank';
                a.download = '';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            });

            // ========= CROP SYSTEM (SENSITIVE PART) =========
            // [CropperJS লজিক এখানে অপরিবর্তিত আছে, যা আপনি সরবরাহ করেছেন]
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

<script>
    // ⬇️ ALPINE TOAST LOGIC (Unchanged and necessary)
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
                    initialTimeout: ttl,
                    remaining: ttl,
                    deadline: now + ttl,
                    progress: 0,
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
