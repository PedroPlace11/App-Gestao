<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Autenticação de Dois Fatores — {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('image/logo/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="margin:0;background:#07090f;min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:system-ui,sans-serif;">

<div style="background:#111827;border:1px solid #1f2937;border-radius:12px;padding:2.5rem;width:100%;max-width:420px;box-shadow:0 25px 50px rgba(0,0,0,.5);">
    <h1 style="color:#f9fafb;font-size:1.5rem;font-weight:700;margin:0 0 .5rem;">Autenticação de Dois Fatores</h1>
    <p style="color:#9ca3af;font-size:.875rem;margin:0 0 2rem;">
        Insira o código da sua aplicação de autenticação ou um dos seus códigos de recuperação.
    </p>

    @if ($errors->any())
        <div style="background:#7f1d1d;border:1px solid #991b1b;border-radius:8px;padding:1rem;margin-bottom:1.5rem;color:#fca5a5;font-size:.875rem;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.login') }}" x-data="{ recovery: false }">
        @csrf

        <div x-show="!recovery">
            <label style="display:block;color:#d1d5db;font-size:.875rem;font-weight:500;margin-bottom:.5rem;">Código</label>
            <input type="text" name="code" inputmode="numeric" autofocus autocomplete="one-time-code"
                style="width:100%;padding:.625rem .875rem;background:#1f2937;border:1px solid #374151;border-radius:8px;color:#f9fafb;font-size:.875rem;outline:none;box-sizing:border-box;letter-spacing:.25em;text-align:center;"
                placeholder="000000">
        </div>

        <div x-show="recovery" style="display:none;">
            <label style="display:block;color:#d1d5db;font-size:.875rem;font-weight:500;margin-bottom:.5rem;">Código de Recuperação</label>
            <input type="text" name="recovery_code" autocomplete="one-time-code"
                style="width:100%;padding:.625rem .875rem;background:#1f2937;border:1px solid #374151;border-radius:8px;color:#f9fafb;font-size:.875rem;outline:none;box-sizing:border-box;"
                placeholder="xxxx-xxxx">
        </div>

        <div style="margin-top:1.5rem;display:flex;gap:.75rem;">
            <button type="submit"
                style="flex:1;padding:.75rem;background:#3b82f6;color:#fff;border:none;border-radius:8px;font-size:.9375rem;font-weight:600;cursor:pointer;"
                onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">
                Confirmar
            </button>
        </div>
    </form>
</div>

</body>
</html>
