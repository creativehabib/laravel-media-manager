{{-- Media Manager Package Styles --}}

{{-- Tabler icons (package এর ভেতর থেকেই লোড হবে) --}}
<link rel="stylesheet"
      href="https://unpkg.com/@tabler/icons-webfont@latest/tabler-icons.min.css">

<style>
    .mm-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 99999;
        display: none;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 13px;
        gap: 6px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        color: #fff;
        align-items: center;
    }
    .mm-toast-success { background: #16a34a; }
    .mm-toast-error   { background: #dc2626; }
    .mm-toast-info    { background: #2563eb; }
    .mm-toast-warning { background: #ca8a04; }
</style>
