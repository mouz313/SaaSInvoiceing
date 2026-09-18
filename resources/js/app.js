import { createIcons, icons } from 'lucide';
import './firebase-auth';

export function applyTheme(theme) {
    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
    localStorage.setItem('invoice_hub_theme', theme);
    createIcons({ icons });
}

export function toggleTheme() {
    const isDark = document.documentElement.classList.contains('dark');
    applyTheme(isDark ? 'light' : 'dark');
}

window.toggleTheme = toggleTheme;
window.applyTheme = applyTheme;

document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });

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
    createIcons({ icons });
};
