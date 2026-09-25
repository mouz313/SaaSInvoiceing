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
</style>

<!-- Lucide Icons Standalone CDN -->
<script src="https://unpkg.com/lucide@latest"></script>

<!-- Alpine.js Standalone CDN -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

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
