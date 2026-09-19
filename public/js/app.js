// Application JavaScript (Standalone, Zero-Node.js)

export function applyTheme(theme) {
    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    localStorage.setItem('invoice_hub_theme', theme);
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

export function toggleTheme() {
    const isDark = document.documentElement.classList.contains('dark');
    applyTheme(isDark ? 'light' : 'dark');
}

window.toggleTheme = toggleTheme;
window.applyTheme = applyTheme;

document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    const savedTheme = localStorage.getItem('invoice_hub_theme') || 
        (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    
    applyTheme(savedTheme);

    // Event delegation for all theme toggle buttons across the app
    document.addEventListener('click', (e) => {
        const toggleBtn = e.target.closest('.theme-toggle-btn');
        if (toggleBtn) {
            e.preventDefault();
            toggleTheme();
        }
    });
});

window.reinitIcons = () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
};

// Firebase helpers
window.sendFirebaseTokenToBackend = async function(user) {
    try {
        const idToken = await user.getIdToken();
        const response = await fetch('/auth/firebase-session', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                id_token: idToken
            })
        });

        const data = await response.json();
        if (data.success && data.redirect) {
            window.location.href = data.redirect;
        } else {
            alert('Failed to establish session: ' + (data.message || 'Unknown error'));
        }
    } catch (err) {
        console.error('Session sync error:', err);
        alert('Authentication error syncing with server.');
    }
};
