@extends('layouts.app')

@section('content')
  @php($website_settings = app(App\Settings\WebsiteSettings::class))
  @php($suppressSweetAlert2 = true)

  <body class="hold-transition dark-mode login-page">
  <div class="login-box">
    <div class="card card-outline card-primary">
      <div class="text-center card-header">
        <a href="{{ route('welcome') }}" class="mb-2 h1"><b
            class="mr-1">{{ config('app.name', 'CtrlPanel.gg') }}</b></a>
        @if ($website_settings->enable_login_logo)
          <img
            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->exists('logo.png') ? asset('storage/logo.png') : asset('images/ctrlpanel_logo.png') }}"
            alt="{{ config('app.name', 'CtrlPanel.gg') }} Logo"
            style="opacity: .8; max-width:100%; height: 150px; margin-top: 10px;">
        @endif
      </div>
      <div class="pt-0 card-body">
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
                     placeholder="{{ __('Authentication Code') }}"
                     autofocus
                     autocomplete="one-time-code"
                     inputmode="numeric">
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

          <input type="hidden" name="_token" value="{{ csrf_token() }}">
        </form>

        <p class="mt-3 mb-1 text-center">
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                {{ __('Log out') }}
            </a>
        </p>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
      </div>
    </div>
  </div>

  <div class="fixed-bottom ">
    <div class="container text-center">
      @if ($website_settings->show_imprint)
        <a target="_blank" href="{{ route('terms', 'imprint') }}"><strong>{{ __('Imprint') }}</strong></a> |
      @endif
      @if ($website_settings->show_privacy)
        <a target="_blank" href="{{ route('terms', 'privacy') }}"><strong>{{ __('Privacy') }}</strong></a>
      @endif
      @if ($website_settings->show_tos)
        | <a target="_blank"
             href="{{ route('terms', 'tos') }}"><strong>{{ __('Terms of Service') }}</strong></a>
      @endif
    </div>
  </div>
  </body>
@endsection
