@extends('layouts.auth')

@section('title', __('Login'))

@section('auth_content')

    {{-- Session status --}}
    @if (session('status'))
        <div class="alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @if (session('success'))
        <div style="background:#dcfce7;color:#15803d;border:1px solid #86efac;border-radius:8px;padding:10px 14px;font-size:13px;font-weight:600;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-check-circle" style="font-size:15px;"></i> {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Email --}}
        <div class="pos-field">
            <label for="email" class="pos-label">{{ __('Email Address') }}</label>
            <div class="pos-input-wrap">
                <svg class="pos-icon" viewBox="0 0 24 24">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                <input id="email"
                       type="email"
                       name="email"
                       class="pos-input @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       placeholder="you@company.com"
                       required
                       autocomplete="email"
                       autofocus>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        {{-- Password --}}
        <div class="pos-field">
            <label for="password" class="pos-label">{{ __('Password') }}</label>
            <div class="pos-input-wrap">
                <svg class="pos-icon" viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input id="password"
                       type="password"
                       name="password"
                       class="pos-input @error('password') is-invalid @enderror"
                       placeholder="••••••••"
                       required
                       autocomplete="current-password">
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </div>

        {{-- Remember + Forgot --}}
        <div class="pos-meta">
            <label class="pos-check-label">
                <input type="checkbox"
                       name="remember"
                       id="remember"
                       {{ old('remember') ? 'checked' : '' }}>
                {{ __('Remember Me') }}
            </label>

        </div>

        {{-- Submit --}}
        <button type="submit" class="pos-btn">
            {{ __('Sign In') }} &rarr;
        </button>
    </form>

@endsection
