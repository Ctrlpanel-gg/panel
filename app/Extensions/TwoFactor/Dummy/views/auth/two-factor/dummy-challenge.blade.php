@extends('layouts.app')

@section('content')
  @php($website_settings = app(App\Settings\WebsiteSettings::class))
  @php($suppressSweetAlert2 = true)

  <body class="hold-transition dark-mode login-page">
  <div class="login-box">
    <div class="card card-outline card-primary">
      <div class="text-center card-header">
        <a href="{{ route('welcome') }}" class="mb-2 h1"><b class="mr-1">{{ config('app.name', 'CtrlPanel.gg') }}</b></a>
      </div>
      <div class="pt-0 card-body">
        <p class="login-box-msg">{{ __('Dummy 2FA Challenge') }}</p>
        <p class="text-center small text-muted">Enter 123456 to pass.</p>

        <form action="{{ route('login.2fa.verify', ['method' => 'dummy']) }}" method="post">
          @csrf
          <div class="form-group">
            <input type="text" name="code" class="form-control" placeholder="123456" autofocus>
          </div>
          <button type="submit" class="btn btn-primary btn-block">{{ __('Verify') }}</button>
        </form>
      </div>
    </div>
  </div>
  </body>
@endsection
