<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestao Inteligente - Sistema de Gestao Empresarial</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-main: #09123a;
            --bg-soft: #11225a;
            --panel: #142a67;
            --line: rgba(168, 194, 255, 0.24);
            --line-soft: rgba(168, 194, 255, 0.15);
            --text: #f5f8ff;
            --muted: #b6c7ee;
            --primary: #2f73ff;
            --primary-hover: #4685ff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Manrope', 'Segoe UI', sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            color: var(--text);
            background:
                radial-gradient(1200px 650px at 50% 0%, rgba(75, 117, 255, 0.18), transparent 62%),
                radial-gradient(900px 500px at 50% 100%, rgba(52, 86, 186, 0.22), transparent 65%),
                linear-gradient(180deg, #030a26 0%, var(--bg-main) 54%, #040d2e 100%);
            overflow-x: hidden;
            position: relative;
        }

        /* Topbar Styles */
        .topbar {
            border-bottom: 1px solid var(--line-soft);
            backdrop-filter: blur(10px);
            background: rgba(9, 18, 58, 0.5);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-inner {
            width: min(1200px, calc(100% - 36px));
            margin: 0 auto;
            min-height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            line-height: 0;
        }

        .brand img {
            display: block;
            height: 44px;
            width: auto;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav a {
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            padding: 10px 16px;
            border-radius: 8px;
            border: 1.5px solid var(--line);
            color: #d9e7ff;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            background: rgba(20, 42, 103, 0.3);
        }

        .nav a:hover {
            transform: translateY(-2px);
            border-color: rgba(184, 206, 255, 0.8);
            background: rgba(47, 115, 255, 0.15);
            box-shadow: 0 8px 24px rgba(47, 115, 255, 0.2);
        }

        .nav .primary {
            background: linear-gradient(135deg, var(--primary), #4685ff);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 8px 24px rgba(47, 115, 255, 0.3);
        }

        .nav .primary:hover {
            background: linear-gradient(135deg, var(--primary-hover), #5a93ff);
            box-shadow: 0 12px 32px rgba(47, 115, 255, 0.4);
            transform: translateY(-3px);
        }

        /* Page Layout */
        .page {
            width: min(1200px, calc(100% - 36px));
            margin: 0 auto;
            padding: 80px 0 80px;
        }

        /* Hero Section */
        .hero {
            text-align: center;
            max-width: 900px;
            margin: 0 auto;
            animation: fade-up 0.8s ease both;
            padding: 0 20px;
        }

        .hero h1 {
            font-size: clamp(36px, 6vw, 72px);
            line-height: 1.1;
            letter-spacing: -0.5px;
            margin-bottom: 20px;
            text-wrap: balance;
            background: linear-gradient(135deg, #f5f8ff, #d2def8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: clamp(15px, 1.5vw, 18px);
            color: #d2def8;
            line-height: 1.8;
            max-width: 720px;
            margin: 0 auto 40px;
            font-weight: 500;
        }

        .hero .cta {
            display: inline-flex;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            border-radius: 10px;
            padding: 14px 32px;
            background: linear-gradient(135deg, var(--primary), #4685ff);
            border: 2px solid transparent;
            box-shadow: 0 12px 32px rgba(47, 115, 255, 0.35);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .hero .cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255, 0.2), rgba(255,255,255, 0));
            transition: left 0.5s ease;
        }

        .hero .cta:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(47, 115, 255, 0.45);
        }

        .hero .cta:hover::before {
            left: 100%;
        }

        /* Features Section */
        .features {
            margin: 100px auto 0;
            width: min(1100px, 100%);
            border: 1.5px solid var(--line);
            border-radius: 20px;
            padding: 48px 32px 40px;
            background: linear-gradient(180deg, rgba(20, 42, 103, 0.6), rgba(13, 30, 76, 0.4));
            animation: fade-up 0.9s ease both;
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .features::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(75, 117, 255, 0.5), transparent);
        }

        .features h2 {
            text-align: center;
            font-size: clamp(28px, 4.5vw, 48px);
            margin-bottom: 40px;
            background: linear-gradient(135deg, #f5f8ff, #d2def8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Cards Grid */
        .cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .card {
            border: 1.5px solid var(--line);
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(169, 193, 248, 0.12), rgba(47, 115, 255, 0.08));
            padding: 20px 18px;
            min-height: 140px;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(75, 117, 255, 0.5), transparent);
        }

        .card:hover {
            transform: translateY(-8px);
            border-color: rgba(75, 117, 255, 0.6);
            background: linear-gradient(135deg, rgba(169, 193, 248, 0.2), rgba(47, 115, 255, 0.15));
            box-shadow: 0 16px 40px rgba(47, 115, 255, 0.2);
        }

        .card .icon {
            font-size: 24px;
            margin-bottom: 10px;
            display: inline-block;
        }

        .card h3 {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 10px;
            line-height: 1.4;
            color: #f6f9ff;
            letter-spacing: 0.3px;
        }

        .card p {
            font-size: 13px;
            color: #a9c1f8;
            line-height: 1.6;
            font-weight: 500;
        }

        /* Floating Elements */
        .floating {
            position: fixed;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            display: grid;
            gap: 12px;
            z-index: 20;
        }

        .floating span {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 1.5px solid var(--line);
            background: rgba(20, 42, 103, 0.9);
            color: #a9c1f8;
            font-size: 12px;
            font-weight: 700;
            display: grid;
            place-items: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .floating span:hover {
            border-color: rgba(75, 117, 255, 0.8);
            background: rgba(47, 115, 255, 0.2);
            transform: scale(1.15);
            box-shadow: 0 8px 20px rgba(47, 115, 255, 0.3);
        }

        /* Footer */
        .footer {
            border-top: 1px solid var(--line-soft);
            padding: 20px 10px;
            text-align: center;
            color: #9bb0dd;
            font-size: 12px;
            background: rgba(5, 14, 45, 0.8);
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        /* Animations */
        @keyframes fade-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

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
            --bg-main: #f5f5f5;
            --bg-soft: #ffffff;
            --panel: #efefef;
            --line: rgba(0, 0, 0, 0.15);
            --line-soft: rgba(0, 0, 0, 0.08);
            --text: #1c1c1c;
            --muted: #666666;
            --primary: #2f73ff;
            --primary-hover: #4685ff;
        }

        html.light-theme body {
            background:
                radial-gradient(1200px 650px at 50% 0%, rgba(75, 117, 255, 0.08), transparent 62%),
                radial-gradient(900px 500px at 50% 100%, rgba(52, 86, 186, 0.08), transparent 65%),
                linear-gradient(180deg, #ffffff 0%, var(--bg-main) 54%, #f8f9fc 100%);
        }

        html.light-theme .topbar {
            background: rgba(245, 245, 245, 0.6);
            border-bottom-color: rgba(0, 0, 0, 0.1);
        }

        html.light-theme .nav a {
            border-color: rgba(0, 0, 0, 0.2);
            background: rgba(0, 0, 0, 0.05);
            color: #333;
        }

        html.light-theme .nav a:hover {
            border-color: rgba(47, 115, 255, 0.6);
            background: rgba(47, 115, 255, 0.1);
        }

        html.light-theme .hero h1 {
            background: linear-gradient(135deg, #1f315f, #2f4f8f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        html.light-theme .hero p {
            color: #3f4e70;
        }

        html.light-theme .features {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(242, 246, 255, 0.92));
            border-color: rgba(31, 49, 95, 0.16);
        }

        html.light-theme .features h2 {
            background: linear-gradient(135deg, #1f315f, #2f4f8f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        html.light-theme .card {
            border-color: rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.8);
        }

        html.light-theme .card:hover {
            border-color: rgba(47, 115, 255, 0.5);
            background: rgba(47, 115, 255, 0.08);
        }

        html.light-theme .card h3 {
            color: #1c1c1c;
        }

        html.light-theme .card p {
            color: #555;
        }

        html.light-theme .floating span {
            border-color: rgba(31, 49, 95, 0.2);
            background: rgba(47, 79, 143, 0.12);
            color: #2f4f8f;
        }

        html.light-theme .theme-toggle button {
            background: rgba(0, 0, 0, 0.1);
            border: 2px solid rgba(0, 0, 0, 0.2);
            color: #1c1c1c;
        }

        html.light-theme .theme-toggle button:hover {
            background: rgba(0, 0, 0, 0.15);
        }

        html.light-theme .footer {
            color: #3f4e70;
            background: rgba(244, 247, 255, 0.9);
            border-top-color: rgba(31, 49, 95, 0.14);
        }

        /* Dark mode styles (default) */
        html.dark-theme {
            --bg-main: #09123a;
            --bg-soft: #11225a;
            --panel: #142a67;
            --line: rgba(168, 194, 255, 0.24);
            --line-soft: rgba(168, 194, 255, 0.15);
            --text: #f5f8ff;
            --muted: #b6c7ee;
            --primary: #2f73ff;
            --primary-hover: #4685ff;
        }

        /* Responsive Design */
        @media (max-width: 980px) {
            .page {
                padding-top: 60px;
            }

            .cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 14px;
            }

            .features {
                margin-top: 60px;
                padding: 36px 24px;
            }

            .hero h1 {
                margin-bottom: 16px;
            }
        }

        @media (max-width: 640px) {
            .topbar-inner,
            .page {
                width: min(1200px, calc(100% - 20px));
            }

            .brand img {
                height: 36px;
            }

            .nav {
                gap: 8px;
            }

            .nav a {
                padding: 8px 12px;
                font-size: 11px;
            }

            .hero h1 {
                font-size: 28px;
                margin-bottom: 12px;
            }

            .hero p {
                font-size: 14px;
                margin-bottom: 24px;
            }

            .features {
                padding: 24px 16px 20px;
                margin-top: 40px;
                border-radius: 16px;
            }

            .features h2 {
                margin-bottom: 24px;
                font-size: 24px;
            }

            .cards {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .card {
                padding: 16px 14px;
                min-height: 120px;
            }

            .card h3 {
                font-size: 13px;
            }

            .card p {
                font-size: 12px;
            }

            .floating {
                display: none;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <a class="brand" href="{{ url('/') }}" aria-label="Página inicial">
                <img src="{{ asset('image/logo/logo.png') }}" alt="Administração">
            </a>

            @if (Route::has('login'))
                <nav class="nav">
                    @auth
                        <a href="{{ url('/dashboard') }}">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}">Entrar</a>
                        @if (Route::has('register'))
                            <a class="primary" href="{{ route('register') }}">Registrar</a>
                        @endif
                    @endauth
                </nav>
            @endif
        </div>
    </header>

    <main class="page">
        <section class="hero">
            <h1>Gerencie sua empresa com inteligência e eficiência</h1>
            <p>
                Solução completa para gestão de clientes, fornecedores, propostas, encomendas e financeiro.
                Centralize dados, automatize processos e tome decisões informadas numa plataforma integrada.
            </p>

            @if (Route::has('register'))
                <a class="cta" href="{{ route('register') }}">Começar agora</a>
            @elseif (Route::has('login'))
                <a class="cta" href="{{ route('login') }}">Começar agora</a>
            @endif
        </section>

        <section class="features">
            <h2>Funcionalidades Principais</h2>

            <div class="cards">
                <article class="card">
                    <div class="icon">👥</div>
                    <h3>Clientes e Fornecedores</h3>
                    <p>Gestão unificada de entidades, com filtragem por tipo, validação de NIF e integração com VIES.</p>
                </article>
                <article class="card">
                    <div class="icon">📋</div>
                    <h3>Propostas e Encomendas</h3>
                    <p>Crie propostas, converta com um clique em encomendas e exporte documentos em PDF profissional.</p>
                </article>
                <article class="card">
                    <div class="icon">💰</div>
                    <h3>Financeiro Integrado</h3>
                    <p>Controle faturas de fornecedor, anexos, comprovativos de pagamento e estado financeiro em tempo real.</p>
                </article>
                <article class="card">
                    <div class="icon">📅</div>
                    <h3>Calendário Operacional</h3>
                    <p>Agenda com FullCalendar para atividades, partilha de conhecimento e filtros por utilizador e entidade.</p>
                </article>
                <article class="card">
                    <div class="icon">🔐</div>
                    <h3>Gestão de Acessos</h3>
                    <p>Perfis, grupos de permissões e auditoria de logs para garantir governança e segurança de acesso.</p>
                </article>
                <article class="card">
                    <div class="icon">⚙️</div>
                    <h3>Configuração e Personalização</h3>
                    <p>Parametrize países, funções, tipos, IVA, empresa e demais dados mestres num único lugar.</p>
                </article>
            </div>
        </section>


    </main>

    <footer class="footer">
        &copy; {{ now()->year }} Gestão Inteligente - Sistema de Gestão Empresarial. Todos os direitos reservados.
    </footer>

    <!-- Theme Toggle -->
    <div class="theme-toggle">
        <button id="themeToggle" aria-label="Toggle dark/light theme" title="Alternar tema">
            <span id="themeIcon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></span>
        </button>
    </div>

    <script>
        // Initialize theme on page load
        function initTheme() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.classList.remove('light-theme', 'dark-theme');
            document.documentElement.classList.add(savedTheme + '-theme');
            updateThemeIcon(savedTheme);
        }

        const moonSVG = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
        const sunSVG = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>';

        function updateThemeIcon(theme) {
            const icon = document.getElementById('themeIcon');
            icon.innerHTML = theme === 'dark' ? moonSVG : sunSVG;
        }

        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark-theme');
            const newTheme = isDark ? 'light' : 'dark';

            html.classList.remove('light-theme', 'dark-theme');
            html.classList.add(newTheme + '-theme');
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
        document.getElementById('themeToggle').addEventListener('click', toggleTheme);
    </script>
</body>
</html>
