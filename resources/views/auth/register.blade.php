<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Criar Conta — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            font-family: 'Manrope', 'Segoe UI', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background:
                radial-gradient(1100px 600px at 50% 0%, rgba(75, 117, 255, 0.2), transparent 62%),
                radial-gradient(900px 500px at 50% 100%, rgba(47, 115, 255, 0.16), transparent 60%),
                linear-gradient(180deg, #071330 0%, #0a1f45 100%);
            color: #f2f7ff;
        }

        .auth-shell {
            width: min(980px, 100%);
            display: grid;
            grid-template-columns: 1fr 1.05fr;
            border-radius: 22px;
            overflow: hidden;
            border: 1px solid rgba(175, 199, 255, 0.22);
            box-shadow: 0 28px 80px rgba(3, 10, 30, 0.45);
            background: rgba(9, 25, 58, 0.74);
            backdrop-filter: blur(12px);
            transform-origin: center;
            position: relative;
        }

        .close-home {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            border: 1px solid rgba(194, 214, 255, 0.36);
            color: #dbe8ff;
            background: rgba(202, 220, 255, 0.12);
            display: grid;
            place-items: center;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 800;
            line-height: 1;
            z-index: 3;
            transition: transform .2s ease, background .2s ease, border-color .2s ease;
        }

        .close-home:hover {
            transform: scale(1.07);
            border-color: rgba(214, 229, 255, 0.64);
            background: rgba(202, 220, 255, 0.24);
        }

        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(80px); opacity: 0; }
        }

        .auth-shell.exit {
            animation: slideOutRight .45s ease forwards;
            pointer-events: none;
        }

        .register-panel {
            padding: 46px 40px;
            background: rgba(11, 29, 66, 0.85);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .register-panel h2 {
            font-size: 1.85rem;
            margin-bottom: 6px;
            font-weight: 800;
            color: #f4f8ff;
            letter-spacing: -.02em;
        }

        .subtitle {
            color: #acc1ea;
            font-size: .92rem;
            margin-bottom: 20px;
        }

        .error-box {
            background: rgba(127, 29, 29, 0.34);
            border: 1px solid rgba(248, 113, 113, 0.44);
            border-radius: 10px;
            padding: .7rem .85rem;
            color: #fecaca;
            font-size: .82rem;
            margin-bottom: .9rem;
        }

        .input-box {
            margin-bottom: .82rem;
        }

        .input-box label {
            display: block;
            color: #c2d4f7;
            font-size: .78rem;
            font-weight: 700;
            margin-bottom: .35rem;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .input-box input {
            width: 100%;
            border-radius: 10px;
            border: 1px solid rgba(149, 176, 228, 0.34);
            background: rgba(173, 198, 243, 0.08);
            color: #f2f7ff;
            font-size: .95rem;
            padding: .7rem .78rem;
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }

        .input-box input:focus {
            border-color: rgba(108, 149, 255, 0.92);
            box-shadow: 0 0 0 3px rgba(90, 138, 255, 0.2);
            background: rgba(173, 198, 243, 0.12);
        }

        .input-box input::placeholder {
            color: #8ca2ce;
        }

        .btn {
            width: 100%;
            margin-top: .95rem;
            border: none;
            border-radius: 10px;
            font-size: .96rem;
            font-weight: 800;
            color: #fff;
            padding: .78rem .85rem;
            cursor: pointer;
            background: linear-gradient(120deg, #2f73ff 0%, #4a8aff 100%);
            box-shadow: 0 12px 28px rgba(38, 95, 218, 0.34);
            transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 34px rgba(38, 95, 218, 0.4);
            filter: brightness(1.04);
        }

        .regi-link {
            margin-top: 1rem;
            color: #9ab1dd;
            font-size: .86rem;
            text-align: center;
        }

        .regi-link a {
            color: #b8ceff;
            text-decoration: none;
            font-weight: 700;
        }

        .regi-link a:hover {
            color: #d2e0ff;
            text-decoration: underline;
        }

        .brand-panel {
            padding: 52px 44px;
            background:
                radial-gradient(760px 360px at 100% 0%, rgba(84, 140, 255, 0.34), transparent 60%),
                linear-gradient(160deg, #17366a 0%, #0f2b57 54%, #0b2147 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .brand-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            padding: 7px 12px;
            border-radius: 999px;
            border: 1px solid rgba(190, 211, 255, 0.32);
            color: #dfeaff;
            font-weight: 700;
            letter-spacing: .04em;
            font-size: .73rem;
            text-transform: uppercase;
            margin-bottom: 24px;
            background: rgba(186, 207, 255, 0.08);
        }

        .brand-panel h1 {
            font-size: clamp(1.8rem, 3vw, 2.6rem);
            line-height: 1.1;
            margin-bottom: 14px;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .brand-panel p {
            color: #d1def8;
            line-height: 1.75;
            font-size: .96rem;
            max-width: 34ch;
        }

        .brand-list {
            margin-top: 24px;
            display: grid;
            gap: 10px;
        }

        .brand-list span {
            color: #c0d3fb;
            font-size: .9rem;
        }

        .theme-toggle {
            position: fixed;
            right: 20px;
            bottom: 20px;
            z-index: 9999;
        }

        .theme-toggle button {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 1.5px solid rgba(160, 186, 243, 0.34);
            background: rgba(214, 227, 255, 0.12);
            color: #fff;
            cursor: pointer;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
            backdrop-filter: blur(8px);
            transition: transform .2s ease, background .2s ease;
        }

        .theme-toggle button:hover {
            transform: scale(1.08);
            background: rgba(214, 227, 255, 0.22);
        }

        html.light-theme body {
            background:
                radial-gradient(1100px 600px at 50% 0%, rgba(84, 140, 255, 0.14), transparent 62%),
                linear-gradient(180deg, #eef3ff 0%, #e4ebfa 100%);
            color: #1f2f4f;
        }

        html.light-theme .auth-shell {
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(58, 95, 168, 0.2);
            box-shadow: 0 30px 70px rgba(18, 46, 97, 0.2);
        }

        html.light-theme .close-home {
            border-color: rgba(65, 109, 196, 0.3);
            background: rgba(82, 130, 228, 0.12);
            color: #28457e;
        }

        html.light-theme .close-home:hover {
            border-color: rgba(65, 109, 196, 0.55);
            background: rgba(82, 130, 228, 0.2);
        }

        html.light-theme .register-panel {
            background: rgba(255, 255, 255, 0.86);
        }

        html.light-theme .register-panel h2 {
            color: #1b3363;
        }

        html.light-theme .subtitle,
        html.light-theme .regi-link {
            color: #4a6190;
        }

        html.light-theme .regi-link a {
            color: #2e57ac;
        }

        html.light-theme .input-box label {
            color: #3a578f;
        }

        html.light-theme .input-box input {
            color: #1f2f4f;
            border-color: rgba(56, 95, 171, 0.3);
            background: rgba(82, 130, 228, 0.08);
        }

        html.light-theme .input-box input::placeholder {
            color: #6f84ae;
        }

        html.light-theme .brand-panel {
            background:
                radial-gradient(760px 360px at 100% 0%, rgba(84, 140, 255, 0.22), transparent 60%),
                linear-gradient(160deg, #f3f7ff 0%, #e9f0ff 52%, #e2ebff 100%);
        }

        html.light-theme .brand-chip {
            color: #2d4a85;
            border-color: rgba(65, 109, 196, 0.28);
            background: rgba(82, 130, 228, 0.1);
        }

        html.light-theme .brand-panel h1 {
            color: #1b3363;
        }

        html.light-theme .brand-panel p,
        html.light-theme .brand-list span {
            color: #435f95;
        }

        html.light-theme .theme-toggle button {
            border-color: rgba(67, 97, 161, 0.35);
            background: rgba(82, 130, 228, 0.14);
            color: #213b70;
        }

        @media (max-width: 860px) {
            .auth-shell {
                grid-template-columns: 1fr;
            }

            .register-panel,
            .brand-panel {
                padding: 32px 22px;
            }

            .brand-panel p {
                max-width: none;
            }
        }
    </style>
</head>
<body>

<div class="auth-shell" id="authShell">
    <a href="{{ url('/') }}" class="close-home" aria-label="Fechar e voltar ao inicio" title="Voltar ao inicio">&times;</a>

    <section class="register-panel">
        <h2>Criar Conta</h2>
        <p class="subtitle">Configure o seu acesso para comecar a trabalhar.</p>

        @if ($errors->any())
            <div class="error-box">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="input-box">
                <label>Nome</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Nome">
            </div>

            <div class="input-box">
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@email.com">
            </div>

            <div class="input-box">
                <label>Password</label>
                <input type="password" name="password" required placeholder="password">
            </div>

            <div class="input-box">
                <label>Confirmar Password</label>
                <input type="password" name="password_confirmation" required placeholder="password">
            </div>

            <button type="submit" class="btn">Registar</button>
        </form>

        <div class="regi-link">
            Ja tem conta? <a id="goLogin" href="{{ route('login') }}">Entrar</a>
        </div>
    </section>

    <aside class="brand-panel">
        <div class="brand-chip">Gestao Inteligente</div>
        <h1>Crie o seu acesso</h1>
        <p>
            Registe-se para gerir clientes, propostas, encomendas,
            financeiro e operacoes diarias com uma experiencia moderna e segura.
        </p>
        <div class="brand-list">
            <span>• Processo de registo rapido e intuitivo</span>
            <span>• Acesso protegido com autenticacao segura</span>
            <span>• Plataforma preparada para crescimento</span>
        </div>
    </aside>
</div>

<!-- Theme Toggle -->
<div class="theme-toggle">
    <button id="themeToggle" aria-label="Toggle dark/light theme" title="Alternar tema">
        <span id="themeIcon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></span>
    </button>
</div>

<script>
    const authShell = document.getElementById('authShell');

    document.getElementById('goLogin').addEventListener('click', function(e) {
        e.preventDefault();
        const href = this.href;
        authShell.classList.add('exit');
        setTimeout(() => { window.location.href = href; }, 450);
    });

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
        icon.innerHTML = theme === 'dark' ? moonSVG : sunSVG;
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
    initTheme();

    // Add event listener to toggle button
    document.getElementById('themeToggle').addEventListener('click', toggleTheme);
</script>

</body>
</html>
