<div class="flex flex-col items-center justify-center text-center py-16">
    <svg viewBox="0 0 260 160" class="w-56 max-w-full" xmlns="http://www.w3.org/2000/svg">
        <!-- Ground shadow -->
        <ellipse cx="130" cy="135" rx="80" ry="10" fill="#e5e7eb"/>

        <!-- Clock circle -->
        <circle cx="130" cy="70" r="42" fill="#e5e7eb"/>
        <circle cx="130" cy="70" r="34" fill="#ffffff"/>

        <!-- Clock marks -->
        <g stroke="#e5e7eb" stroke-width="2" stroke-linecap="round">
            <line x1="130" y1="36" x2="130" y2="40"/>
            <line x1="130" y1="100" x2="130" y2="104"/>
            <line x1="96" y1="70" x2="100" y2="70"/>
            <line x1="160" y1="70" x2="164" y2="70"/>
        </g>

        <!-- Clock hands -->
        <g stroke="rgb(var(--be-primary))" stroke-linecap="round" stroke-width="4">
            <line x1="130" y1="70" x2="130" y2="50"/>
            <line x1="130" y1="70" x2="152" y2="80"/>
        </g>

        <circle cx="130" cy="70" r="3" fill="rgb(var(--be-primary))"/>

        <!-- Small arrow back (recent history idea) -->
        <g transform="translate(80,40)">
            <path d="M12 20a10 10 0 1 0 4-7.8" fill="none" stroke="#cbd5f5" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round"/>
            <polyline points="16 10 16 16 10 16" fill="none" stroke="#cbd5f5" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round"/>
        </g>
    </svg>

    <h3 class="mt-4 text-base font-semibold text-gray-800 dark:text-gray-100">
        No recent activity
    </h3>
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
        Your recently opened or updated media will appear here.
    </p>
</div>
