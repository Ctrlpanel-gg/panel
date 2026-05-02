@extends('layouts.app')

@section('content')
  @php($website_settings = app(App\Settings\WebsiteSettings::class))
  <body class="hold-transition dark-mode login-page">
  <div class="login-box">
    <div class="card card-outline card-primary">
      <div class="text-center card-header">
        <a href="{{ route('welcome') }}" class="mb-2 h1"><b
            class="mr-1">{{ config('app.name', 'CtrlPanel.gg') }}</b></a>
      </div>
      <div class="pt-0 card-body">
        <p class="login-box-msg">{{ __('Two-Factor Authentication') }}</p>

        @if (session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('2fa.verify') }}" method="post">
          @csrf
          <div class="form-group">
            <p>{{ __('Please enter the code from your authenticator app to continue.') }}</p>
            <div class="mb-3 input-group">
              <input type="text" name="one_time_password" class="form-control" placeholder="{{ __('Authentication Code') }}" required autofocus>
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-key"></span>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-12">
              <button type="submit" class="btn btn-primary btn-block">{{ __('Verify') }}</button>
            </div>
          </div>
        </form>

        <p class="mt-3 mb-1 text-center">
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                {{ __('Cancel and Logout') }}
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
