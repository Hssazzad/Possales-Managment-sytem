<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PosSales') }} — @yield('title', 'Login')</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            font-family: 'Nunito', 'Segoe UI', system-ui, sans-serif;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(201,168,76,0.07) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(26,90,160,0.10) 0%, transparent 55%),
                #0f1c2e;
        }

        /* ── Card ── */
        .auth-card {
            width: 100%;
            max-width: 460px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 18px;
            padding: 2.5rem 2.25rem 2rem;
            box-shadow: 0 32px 80px rgba(0,0,0,0.5);
            animation: fadeUp 0.45s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Brand ── */
        .auth-brand {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .auth-logo {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background: linear-gradient(135deg, #c9a84c, #e0c06a);
            margin: 0 auto 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 0 7px rgba(201,168,76,0.13);
        }

        .auth-logo svg {
            width: 30px;
            height: 30px;
            stroke: #1a2a4a;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .auth-company {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #f0e6c8;
            margin-bottom: 4px;
        }

        .auth-company span { color: #c9a84c; }

        .auth-tagline {
            font-size: 11px;
            color: #5a7a9a;
            letter-spacing: 1.8px;
            text-transform: uppercase;
        }

        .auth-divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.07);
            margin: 1.5rem 0;
        }

        /* ── Alerts ── */
        .alert-success {
            background: rgba(40,167,69,0.15);
            border: 1px solid rgba(40,167,69,0.3);
            border-radius: 8px;
            color: #6fcf97;
            font-size: 13px;
            padding: 10px 14px;
            margin-bottom: 1.25rem;
        }

        .alert-danger {
            background: rgba(220,53,69,0.12);
            border: 1px solid rgba(220,53,69,0.3);
            border-radius: 8px;
            color: #e07070;
            font-size: 13px;
            padding: 10px 14px;
            margin-bottom: 1.25rem;
        }

        /* ── Labels ── */
        .pos-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #6a8aaa;
            margin-bottom: 7px;
        }

        /* ── Fields ── */
        .pos-field { margin-bottom: 1.1rem; }

        .pos-input-wrap { position: relative; }

        .pos-input-wrap .pos-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            stroke: #c9a84c;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            opacity: 0.75;
            pointer-events: none;
        }

        .pos-input {
            width: 100%;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            color: #e8f0f8;
            font-size: 14px;
            font-family: inherit;
            padding: 11px 13px 11px 40px;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }

        .pos-input::placeholder { color: #2e4a62; }

        .pos-input:focus {
            border-color: rgba(201,168,76,0.5);
            background: rgba(255,255,255,0.09);
        }

        .pos-input.is-invalid {
            border-color: rgba(220,53,69,0.55);
        }

        .invalid-feedback {
            display: block;
            color: #e07070;
            font-size: 12px;
            margin-top: 5px;
        }

        /* ── Remember / Forgot row ── */
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
            font-size: 13px;
            color: #6a8aaa;
            cursor: pointer;
            user-select: none;
        }

        .pos-check-label input[type="checkbox"] {
            accent-color: #c9a84c;
            width: 15px;
            height: 15px;
            cursor: pointer;
        }

        .pos-link {
            font-size: 12px;
            color: #c9a84c;
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .pos-link:hover { opacity: 0.7; color: #c9a84c; }

        /* ── Submit button ── */
        .pos-btn {
            width: 100%;
            background: linear-gradient(135deg, #c9a84c, #dbb85a);
            border: none;
            border-radius: 10px;
            color: #1a2a4a;
            font-size: 14px;
            font-weight: 800;
            font-family: inherit;
            letter-spacing: 0.8px;
            padding: 12px;
            cursor: pointer;
            text-transform: uppercase;
            transition: opacity 0.2s, transform 0.15s;
        }

        .pos-btn:hover  { opacity: 0.88; }
        .pos-btn:active { transform: scale(0.98); }

        /* ── Footer ── */
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #2e4a62;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="auth-card">

        {{-- Brand --}}
        <div class="auth-brand">
            <div class="auth-logo">
                <svg viewBox="0 0 24 24">
                    <rect x="2" y="3" width="20" height="14" rx="2"/>
                    <path d="M8 21h8M12 17v4"/>
                    <circle cx="7"  cy="8"  r="1" fill="#1a2a4a"/>
                    <circle cx="12" cy="8"  r="1" fill="#1a2a4a"/>
                    <circle cx="17" cy="8"  r="1" fill="#1a2a4a"/>
                    <circle cx="7"  cy="12" r="1" fill="#1a2a4a"/>
                    <circle cx="12" cy="12" r="1" fill="#1a2a4a"/>
                    <circle cx="17" cy="12" r="1" fill="#1a2a4a"/>
                </svg>
            </div>
            <h1 class="auth-company">Pos<span>Sales</span></h1>
            <p class="auth-tagline">Sales Management Portal</p>
        </div>

        <hr class="auth-divider">

        {{-- Page content (login form, register form, etc.) --}}
        @yield('auth_content')

        <p class="auth-footer">
            &copy; {{ date('Y') }} PosSales &mdash; Secure Access Only
        </p>
    </div>
</body>
</html>
