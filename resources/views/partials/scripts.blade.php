{{-- Media Manager Package Scripts --}}
<script>
    function showMMToast(type, message) {
        let toast = document.createElement('div');
        toast.classList.add('mm-toast');

        switch (type) {
            case 'success': toast.classList.add('mm-toast-success'); break;
            case 'error':   toast.classList.add('mm-toast-error'); break;
            case 'warning': toast.classList.add('mm-toast-warning'); break;
            default:        toast.classList.add('mm-toast-info'); break;
        }

        toast.innerHTML = `<i class="ti ti-info-circle"></i> <span>${message}</span>`;
        document.body.appendChild(toast);

        toast.style.display = 'flex';
        toast.style.opacity = 1;

        setTimeout(() => {
            toast.style.transition = 'opacity .3s';
            toast.style.opacity = 0;
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }

    document.addEventListener('DOMContentLoaded', () => {

        // Toast
        window.addEventListener('media-toast', e => {
            const detail = e.detail || {};
            showMMToast(detail.type || 'info', detail.message || '');
        });

        // Copy link
        window.addEventListener('media-copy-link', e => {
            if (!e.detail || !e.detail.url) return;
            navigator.clipboard.writeText(e.detail.url);
            showMMToast('success', 'Link copied!');
        });

        // Download
        window.addEventListener('media-download', e => {
            if (!e.detail || !e.detail.url) return;
            window.open(e.detail.url, '_blank');
        });

        // Share
        window.addEventListener('media-share', e => {
            if (!e.detail || !e.detail.url) return;

            if (navigator.share) {
                navigator.share({ url: e.detail.url }).catch(() => {});
            } else {
                navigator.clipboard.writeText(e.detail.url);
                showMMToast('info', 'Share not supported, URL copied instead.');
            }
        });
    });
</script>
