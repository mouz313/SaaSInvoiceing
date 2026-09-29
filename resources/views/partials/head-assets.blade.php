<!-- Google Fonts: Plus Jakarta Sans, Playfair Display, JetBrains Mono -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

<!-- Browser Favicon -->
@if(setting('app_favicon'))
    <link rel="icon" href="{{ Storage::url(setting('app_favicon')) }}">
    <link rel="apple-touch-icon" href="{{ Storage::url(setting('app_favicon')) }}">
@endif

<!-- Tailwind CSS Standalone CDN (Zero-Node.js Required) -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                fontFamily: {
                    sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                },
                colors: {
                    slate: {
                        850: '#172033',
                    }
                }
            }
        }
    }
</script>

<!-- Compiled Production CSS Fallback -->
@if(file_exists(public_path('build/assets/app-DTVkSPTQ.css')))
    <link rel="stylesheet" href="{{ asset('build/assets/app-DTVkSPTQ.css') }}">
@endif

<style>
    [x-cloak] {
        display: none !important;
    }
    /* Robust fallback for gradients against compiled Tailwind resets */
    .bg-gradient-to-r.from-blue-600.to-indigo-700,
    .bg-gradient-to-r.from-blue-600.to-indigo-600,
    .bg-gradient-to-r.from-blue-700.via-indigo-700.to-slate-900 {
        background-color: #2563eb !important;
        background-image: linear-gradient(135deg, #1d4ed8 0%, #4338ca 100%) !important;
    }

    /* Avant-Garde Kinetic Stretch & Glitch Menu Styles */
    .kinetic-menu-overlay {
        background-color: #08080a !important;
        background: radial-gradient(circle at 50% 30%, #15161c 0%, #08080a 100%) !important;
    }

    .kinetic-menu-list:hover .kinetic-menu-item:not(:hover) {
        opacity: 0.22;
        filter: blur(1.5px);
        transform: scale(0.98);
    }

    .kinetic-menu-item {
        position: relative;
        display: inline-flex;
        align-items: baseline;
        cursor: pointer;
        text-decoration: none;
        user-select: none;
        transition: opacity 0.4s cubic-bezier(0.16, 1, 0.3, 1),
                    filter 0.4s cubic-bezier(0.16, 1, 0.3, 1),
                    transform 0.4s cubic-bezier(0.16, 1, 0.3, 1),
                    color 0.3s ease;
    }

    .kinetic-menu-item .stretch-dash {
        display: inline-block;
        width: 0px;
        height: 2.5px;
        background-color: #3b82f6;
        margin: 0;
        opacity: 0;
        transform: scaleX(0);
        transform-origin: center;
        border-radius: 9999px;
        vertical-align: middle;
        align-self: center;
        transition: width 0.4s cubic-bezier(0.16, 1, 0.3, 1),
                    margin 0.4s cubic-bezier(0.16, 1, 0.3, 1),
                    opacity 0.3s ease,
                    transform 0.4s cubic-bezier(0.16, 1, 0.3, 1),
                    background-color 0.3s ease;
    }

    .kinetic-menu-item:hover .stretch-dash {
        width: 32px;
        margin: 0 10px;
        opacity: 1;
        transform: scaleX(1);
        background-color: #60a5fa;
        box-shadow: 0 0 12px rgba(96, 165, 250, 0.6);
    }

    @media (min-width: 768px) {
        .kinetic-menu-item:hover .stretch-dash {
            width: 48px;
            margin: 0 14px;
        }
    }

    .kinetic-menu-item .word-part {
        position: relative;
        display: inline-block;
        transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1),
                    letter-spacing 0.4s cubic-bezier(0.16, 1, 0.3, 1),
                    color 0.3s ease;
    }

    .kinetic-menu-item:hover .word-part.part-left {
        transform: translateX(-4px);
        letter-spacing: -0.01em;
    }

    .kinetic-menu-item:hover .word-part.part-right {
        transform: translateX(4px);
        letter-spacing: -0.01em;
    }

    .kinetic-glitch-char {
        position: relative;
        display: inline-block;
    }

    .kinetic-menu-item:hover .kinetic-glitch-char::before,
    .kinetic-menu-item:hover .kinetic-glitch-char::after {
        content: attr(data-char);
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        opacity: 0.9;
    }

    .kinetic-menu-item:hover .kinetic-glitch-char::before {
        clip-path: polygon(0 0, 100% 0, 100% 52%, 0 52%);
        transform: translate(-3px, -2px) scaleX(1.1);
        color: #60a5fa;
        text-shadow: -1px -1px 0 rgba(96, 165, 250, 0.5);
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .kinetic-menu-item:hover .kinetic-glitch-char::after {
        clip-path: polygon(0 48%, 100% 48%, 100% 100%, 0 100%);
        transform: translate(3px, 2px) scaleX(1.1);
        color: #f43f5e;
        text-shadow: 1px 1px 0 rgba(244, 63, 94, 0.5);
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
</style>

<!-- Lucide Icons (Local with CDN fallback) -->
<script src="{{ asset('js/lucide.min.js') }}"></script>
<script>
    if (typeof lucide === 'undefined') {
        document.write('<script src="https://unpkg.com/lucide@latest"><\/script>');
    }
</script>

<!-- Alpine.js (Local with CDN fallback) -->
<script defer src="{{ asset('js/alpine.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Alpine === 'undefined') {
            const script = document.createElement('script');
            script.defer = true;
            script.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js';
            document.head.appendChild(script);
        }
    });
</script>

<!-- App JavaScript (Standalone) -->
<script type="module" src="{{ asset('js/app.js') }}"></script>

<script>
    // Immediate Theme Initialization
    if (localStorage.getItem('invoice_hub_theme') === 'dark' || (!('invoice_hub_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
