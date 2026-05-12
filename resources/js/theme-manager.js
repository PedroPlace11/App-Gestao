/**
 * Theme Manager
 * Gerencia o tema escuro/claro da aplicação
 */

const ThemeManager = {
    STORAGE_KEY: 'theme',
    LIGHT_THEME: 'light',
    DARK_THEME: 'dark',
    DEFAULT_THEME: 'dark',

    /**
     * Initialize theme on page load
     */
    init() {
        this.applySavedTheme();
        this.setupToggleListener();
    },

    /**
     * Get saved theme or default
     */
    getSavedTheme() {
        return localStorage.getItem(this.STORAGE_KEY) || this.DEFAULT_THEME;
    },

    /**
     * Apply saved theme
     */
    applySavedTheme() {
        const theme = this.getSavedTheme();
        this.applyTheme(theme);
    },

    /**
     * Apply theme to document
     */
    applyTheme(theme) {
        const html = document.documentElement;
        
        // Remove existing theme classes
        html.classList.remove(
            `${this.LIGHT_THEME}-theme`,
            `${this.DARK_THEME}-theme`
        );
        
        // Add new theme class
        html.classList.add(`${theme}-theme`);

        // Manage Tailwind dark class
        if (theme === this.DARK_THEME) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
        
        // Save to localStorage
        localStorage.setItem(this.STORAGE_KEY, theme);
        
        // Update icon
        this.updateThemeIcon(theme);
    },

    /**
     * Toggle between themes
     */
    toggle() {
        const currentTheme = this.getSavedTheme();
        const newTheme = currentTheme === this.DARK_THEME 
            ? this.LIGHT_THEME 
            : this.DARK_THEME;
        
        this.applyTheme(newTheme);
    },

    /**
     * Update theme toggle icon
     */
    updateThemeIcon(theme) {
        const icon = document.getElementById('themeIcon');
        if (icon) {
            icon.textContent = theme === this.DARK_THEME ? '🌙' : '☀️';
        }
    },

    /**
     * Setup toggle button listener
     */
    setupToggleListener() {
        const toggleBtn = document.getElementById('themeToggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => this.toggle());
        }
    }
};

// Initialize on DOMContentLoaded or immediately if already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => ThemeManager.init());
} else {
    ThemeManager.init();
}

export default ThemeManager;
