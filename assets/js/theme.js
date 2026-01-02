const themeSelect = document.getElementById('theme-select');
const htmlElement = document.documentElement;

// 1. Initialize Theme from LocalStorage or System
const savedTheme = localStorage.getItem('epistora-theme') || 'system';
themeSelect.value = savedTheme;
applyTheme(savedTheme);

themeSelect.addEventListener('change', (e) => {
    const selected = e.target.value;
    localStorage.setItem('epistora-theme', selected);
    applyTheme(selected);
});

function applyTheme(theme) {
    if (theme === 'system') {
        const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        htmlElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
    } else {
        htmlElement.setAttribute('data-theme', theme);
    }
}

// Watch for System changes while in 'System' mode
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
    if (localStorage.getItem('epistora-theme') === 'system') {
        applyTheme('system');
    }
});