<!-- Theme Toggle Component -->
<div class="theme-toggle">
    <button id="themeToggle" aria-label="Toggle dark/light theme" title="Alternar tema">
        <span id="themeIcon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></span>
    </button>
</div>

<style>
    /* Theme Toggle Styles */
    .theme-toggle {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .theme-toggle button {
        background: rgba(255, 255, 255, 0.15);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        width: 50px;
        height: 50px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        transition: all 0.3s ease;
        box-shadow: 0 8px 24px rgba(0,0,0,.2);
        color: white;
        backdrop-filter: blur(10px);
    }

    .theme-toggle button:hover {
        transform: scale(1.1);
        background: rgba(255, 255, 255, 0.25);
        box-shadow: 0 12px 32px rgba(0,0,0,.3);
    }

    /* Light mode styles */
    html.light-theme {
        --bg-primary: #f5f5f5;
        --bg-secondary: #ffffff;
        --text-primary: #1c1c1c;
        --text-secondary: #555555;
    }

    html.light-theme body {
        background: var(--bg-primary);
        color: var(--text-primary);
    }

    html.light-theme .theme-toggle button {
        background: rgba(0, 0, 0, 0.1);
        border: 2px solid rgba(0, 0, 0, 0.2);
        color: #1c1c1c;
    }

    html.light-theme .theme-toggle button:hover {
        background: rgba(0, 0, 0, 0.15);
    }

    /* Dark mode styles (default) */
    html.dark-theme {
        --bg-primary: #0F2442;
        --bg-secondary: #1a3a52;
        --text-primary: #f5f5f5;
        --text-secondary: #888;
    }

    html.dark-theme body {
        background: var(--bg-primary);
        color: var(--text-primary);
    }
</style>

<script>
    // Initialize theme on page load
    function initTheme() {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.classList.remove('light-theme', 'dark-theme');
        document.documentElement.classList.add(savedTheme + '-theme');
        if (savedTheme === 'dark') document.documentElement.classList.add('dark');
        else document.documentElement.classList.remove('dark');
        updateThemeIcon(savedTheme);
    }

    const moonSVG = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
    const sunSVG = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>';

    function updateThemeIcon(theme) {
        const icon = document.getElementById('themeIcon');
        if (icon) {
            icon.innerHTML = theme === 'dark' ? moonSVG : sunSVG;
        }
    }

    function toggleTheme() {
        const html = document.documentElement;
        const isDark = html.classList.contains('dark-theme');
        const newTheme = isDark ? 'light' : 'dark';
        
        html.classList.remove('light-theme', 'dark-theme');
        html.classList.add(newTheme + '-theme');
        if (newTheme === 'dark') html.classList.add('dark');
        else html.classList.remove('dark');
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
    }

    // Initialize theme on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }

    // Add event listener to toggle button
    const themeToggleBtn = document.getElementById('themeToggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', toggleTheme);
    }
</script>
