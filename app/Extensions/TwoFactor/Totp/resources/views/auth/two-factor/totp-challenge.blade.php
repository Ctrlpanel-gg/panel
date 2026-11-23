@extends('layouts.app')

@section('content')
@php($suppressSweetAlert2 = true)

<body class="hold-transition dark-mode login-page">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="text-center card-header">
                <a href="{{ route('welcome') }}" class="mb-2 h1"><b
                        class="mr-1">{{ config('app.name', 'CtrlPanel.gg') }}</b></a>
            </div>
            <div class="card-body">
                <p class="login-box-msg">{{ __('Two-Factor Authentication') }}</p>

                <p class="text-center small text-muted">
                    {{ __('Please enter your 6-digit TOTP code from your authenticator app or an 8-character recovery code.') }}
                </p>

                <form action="{{ route('login.2fa.verify', ['method' => 'totp']) }}" method="post">
                    @csrf

                    <div class="form-group">
                        <div class="mb-3 input-group">
                            <input type="text" name="code" id="code"
                                class="form-control @error('code') is-invalid @enderror"
                                placeholder="{{ __('Authentication Code') }}" autofocus autocomplete="one-time-code">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                        @error('code')
                            <span class="text-danger" role="alert">
                                <small><strong>{{ $message }}</strong></small>
                            </span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">{{ __('Verify') }}</button>
                        </div>
                    </div>
                </form>
                <p class="mt-3 mb-1 text-center">
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        {{ __('Logout') }}
                    </a>
                </p>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</body>
@endsection
