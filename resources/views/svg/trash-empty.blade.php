<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 420 180" width="500">

    <style>
        :root {
            --line: #9ca3af;
            --stroke: #4b5563;
            --text-main: #111827;
            --text-sub: #6b7280;
            --file-blue: #2563eb;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --line: #4b5563;
                --stroke: #e5e7eb;
                --text-main: #f3f4f6;
                --text-sub: #9ca3af;
                --file-blue: #3b82f6;
            }
        }
    </style>

    <!-- Ground line -->
    <line x1="40" y1="105" x2="380" y2="105" stroke="var(--line)" stroke-width="1"/>

    <!-- Trash bin body -->
    <rect x="250" y="60" width="40" height="40" rx="3"
          fill="none" stroke="var(--stroke)" stroke-width="2"/>

    <!-- Trash bin stripes -->
    <line x1="258" y1="62" x2="258" y2="100" stroke="var(--stroke)" stroke-width="1.5"/>
    <line x1="270" y1="62" x2="270" y2="100" stroke="var(--stroke)" stroke-width="1.5"/>
    <line x1="282" y1="62" x2="282" y2="100" stroke="var(--stroke)" stroke-width="1.5"/>

    <!-- Trash bin lid -->
    <rect x="246" y="50" width="48" height="6" rx="2"
          fill="none" stroke="var(--stroke)" stroke-width="1.5"
          transform="rotate(-15 270 53)"/>

    <!-- Person legs -->
    <rect x="175" y="70" width="8" height="32" rx="3" fill="#2f2e41"/>
    <rect x="190" y="70" width="8" height="32" rx="3" fill="#2f2e41"/>

    <!-- Shoes -->
    <rect x="172" y="100" width="14" height="4" rx="2" fill="#2f2e41"/>
    <rect x="188" y="100" width="14" height="4" rx="2" fill="#2f2e41"/>

    <!-- Body -->
    <rect x="172" y="44" width="28" height="30" rx="7" fill="#d9d9d9"/>

    <!-- Head -->
    <circle cx="186" cy="32" r="10" fill="#feb8b8"/>

    <!-- Hair -->
    <path d="M178 32c1-7 6-11 13-11 5 0 8 2 10 5v5c-2-2-5-4-10-4-4 0-8 1-13 5z"
          fill="#2f2e41"/>

    <!-- Arm -->
    <rect x="194" y="48" width="20" height="6" rx="3"
          fill="#d9d9d9" transform="rotate(-25 194 51)"/>

    <!-- Hand -->
    <circle cx="212" cy="43" r="4" fill="#feb8b8"/>

    <!-- Blue file -->
    <rect x="216" y="48" width="22" height="26" rx="2"
          fill="var(--file-blue)" transform="rotate(-25 227 61)"/>
    <line x1="220" y1="54" x2="232" y2="54" stroke="#ffffff" stroke-width="1.5"/>
    <line x1="220" y1="58" x2="232" y2="58" stroke="#ffffff" stroke-width="1.5"/>
    <line x1="220" y1="62" x2="229" y2="62" stroke="#ffffff" stroke-width="1.5"/>

    <!-- Small rock/bush -->
    <ellipse cx="115" cy="100" rx="18" ry="4" fill="var(--line)"/>
    <circle cx="108" cy="96" r="5" fill="#f3f3f3"/>
    <circle cx="119" cy="97" r="4" fill="#f3f3f3"/>

    <!-- Text: title -->
    <text x="210" y="140"
          text-anchor="middle"
          font-size="14"
          font-family="system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif"
          fill="var(--text-main)">
        Trash is empty
    </text>

    <!-- Text: subtitle -->
    <text x="210" y="158"
          text-anchor="middle"
          font-size="11"
          font-family="system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif"
          fill="var(--text-sub)">
        There are no files or folders in your trash currently
    </text>
</svg>
