<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Gestão') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Light mode styles */
        html.light-theme {
            --bg-primary: #e8edf5;
            --bg-secondary: #ffffff;
            --text-primary: #1c1c1c;
            --text-secondary: #555555;
            --color-table-header: #f1f5f9;
            --color-row-hover: #f1f5f9;
            --color-text-main: #0f172a;
            --color-text-sub: #475569;
        }

        html.light-theme body {
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        /* Dark mode styles (default) */
        html.dark-theme {
            --bg-primary: #0F2442;
            --bg-secondary: #1a3a52;
            --text-primary: #f5f5f5;
            --text-secondary: #888;
            --color-table-header: #253347;
            --color-row-hover: #263550;
            --color-text-main: #f1f5f9;
            --color-text-sub: #94a3b8;
        }

        html.dark-theme body {
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        /* Fix: dark mode layout overrides (hardcoded bg colors in AppLayout) */
        html.dark-theme .app-shell,
        html.dark-theme .main-content {
            background: #0f172a !important;
        }

        html.dark-theme .topbar {
            background: #1e293b !important;
            border-bottom-color: #334155 !important;
        }

        html.dark-theme .topbar-brand {
            color: #f1f5f9 !important;
        }

        html.dark-theme .topbar-user {
            background: #0f172a !important;
            border-color: #334155 !important;
        }

        html.dark-theme .topbar-username {
            color: #cbd5e1 !important;
        }

        html.dark-theme .mobile-menu-btn {
            color: #94a3b8 !important;
        }

        /* Fix: Tailwind CSS variable overrides for dark mode */
        html.dark-theme,
        html.dark {
            --color-background: #0f172a;
            --color-foreground: #f1f5f9;
            --color-card: #1e293b;
            --color-card-foreground: #f1f5f9;
            --color-popover: #1e293b;
            --color-popover-foreground: #f1f5f9;
            --color-primary: #60a5fa;
            --color-primary-foreground: #0f172a;
            --color-secondary: #1e293b;
            --color-secondary-foreground: #f1f5f9;
            --color-muted: #1e293b;
            --color-muted-foreground: #94a3b8;
            --color-accent: #1e293b;
            --color-accent-foreground: #f1f5f9;
            --color-destructive: #f87171;
            --color-destructive-foreground: #0f172a;
            --color-border: #334155;
            --color-input: #334155;
            --color-ring: #60a5fa;
        }
    </style>
</head>
<body>
    <script>
        window.__AUTH_USER__ = @json(auth()->user());
        
        // Initialize theme immediately on page load
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.classList.add(savedTheme + '-theme');
            if (savedTheme === 'dark') document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
        })();
    </script>
    <div id="app"></div>
    
    @include('components.theme-toggle')
</body>
</html>
