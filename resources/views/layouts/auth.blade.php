<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PosSales') }} — @yield('title', 'Login')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --auth-bg: #f0f4f8;
            --auth-surface: #ffffff;
            --auth-border: #e2e8f0;
            --auth-text: #0f172a;
            --auth-muted: #64748b;
            --auth-accent: #0e7490;
            --auth-accent-soft: #e0f2fe;
            --auth-radius: 16px;
            --auth-shadow: 0 4px 32px rgba(15, 23, 42, 0.08);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            font-family: 'Inter', system-ui, sans-serif;
            background:
                radial-gradient(ellipse 80% 60% at 10% 20%, rgba(14, 116, 144, 0.06), transparent),
                radial-gradient(ellipse 60% 50% at 90% 80%, rgba(15, 23, 42, 0.04), transparent),
                var(--auth-bg);
            -webkit-font-smoothing: antialiased;
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
            background: var(--auth-surface);
            border: 1px solid var(--auth-border);
            border-radius: var(--auth-radius);
            padding: 2.5rem 2.25rem 2rem;
            box-shadow: var(--auth-shadow);
            animation: fadeUp 0.45s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-brand { text-align: center; margin-bottom: 1.75rem; }

        .auth-logo {
            width: 64px;
            height: 64px;
            border-radius: 14px;
            background: var(--auth-accent-soft);
            margin: 0 auto 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #bae6fd;
        }

        .auth-logo svg {
            width: 30px;
            height: 30px;
            stroke: var(--auth-accent);
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .auth-company {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            color: var(--auth-text);
            margin-bottom: 4px;
        }

        .auth-company span { color: var(--auth-accent); }

        .auth-tagline {
            font-size: 0.68rem;
            color: var(--auth-muted);
            letter-spacing: 0.14em;
            text-transform: uppercase;
            font-weight: 600;
        }

        .auth-divider {
            border: none;
            border-top: 1px solid var(--auth-border);
            margin: 1.5rem 0;
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 10px;
            color: #065f46;
            font-size: 0.8125rem;
            padding: 10px 14px;
            margin-bottom: 1.25rem;
        }

        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            color: #991b1b;
            font-size: 0.8125rem;
            padding: 10px 14px;
            margin-bottom: 1.25rem;
        }

        .pos-label {
            display: block;
            font-size: 0.6875rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--auth-muted);
            margin-bottom: 7px;
        }

        .pos-field { margin-bottom: 1.1rem; }

        .pos-input-wrap { position: relative; }

        .pos-input-wrap .pos-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            stroke: var(--auth-accent);
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            opacity: 0.65;
            pointer-events: none;
        }

        .pos-input {
            width: 100%;
            background: #f8fafc;
            border: 1px solid var(--auth-border);
            border-radius: 10px;
            color: var(--auth-text);
            font-size: 0.9rem;
            font-family: inherit;
            padding: 11px 13px 11px 40px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .pos-input::placeholder { color: #94a3b8; }

        .pos-input:focus {
            border-color: var(--auth-accent);
            box-shadow: 0 0 0 3px rgba(14, 116, 144, 0.12);
            background: var(--auth-surface);
        }

        .pos-input.is-invalid { border-color: #f87171; }

        .invalid-feedback {
            display: block;
            color: #dc2626;
            font-size: 0.75rem;
            margin-top: 5px;
        }

        .pos-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 0.2rem 0 1.5rem;
        }

        .pos-check-label {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 0.8125rem;
            color: var(--auth-muted);
            cursor: pointer;
            user-select: none;
        }

        .pos-check-label input[type="checkbox"] {
            accent-color: var(--auth-accent);
            width: 15px;
            height: 15px;
            cursor: pointer;
        }

        .pos-link {
            font-size: 0.75rem;
            color: var(--auth-accent);
            text-decoration: none;
            font-weight: 500;
        }

        .pos-link:hover { text-decoration: underline; }

        .pos-btn {
            width: 100%;
            background: linear-gradient(180deg, #0891b2 0%, #0e7490 100%);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: inherit;
            letter-spacing: 0.02em;
            padding: 12px;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(14, 116, 144, 0.25);
            transition: filter 0.2s, transform 0.15s;
        }

        .pos-btn:hover { filter: brightness(1.05); }
        .pos-btn:active { transform: scale(0.99); }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--auth-muted);
            font-size: 0.6875rem;
        }
    </style>
</head>
<body>
    <div class="auth-card">

        <div class="auth-brand">
            <div class="auth-logo">
                <img src="/images/logo.png" alt="Bongo Bay" style="width:42px;height:42px;object-fit:contain;">
            </div>
            <h1 class="auth-company">Bongo <span>Bay</span></h1>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:6px;margin-bottom:10px;">
            <a href="{{ route('language.switch', 'en') }}" style="text-decoration:none;font-size:11px;padding:4px 10px;border-radius:6px;{{ app()->getLocale()=='en' ? 'background:#0e7490;color:#fff;font-weight:600;' : 'background:#f1f5f9;color:#64748b;font-weight:500;' }}">EN</a>
            <a href="{{ route('language.switch', 'bn') }}" style="text-decoration:none;font-size:11px;padding:4px 10px;border-radius:6px;{{ app()->getLocale()=='bn' ? 'background:#0e7490;color:#fff;font-weight:600;' : 'background:#f1f5f9;color:#64748b;font-weight:500;' }}">বাংলা</a>
        </div>

        <hr class="auth-divider">

        @yield('auth_content')

        <p class="auth-footer">
            &copy; {{ date('Y') }} {{ config('app.name') }} &mdash; {{ __('Secure Access Only') }}
        </p>
    </div>
</body>
</html>
