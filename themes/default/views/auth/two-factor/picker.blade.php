@extends('layouts.app')

@section('content')
  @php($website_settings = app(App\Settings\WebsiteSettings::class))
  @php($suppressSweetAlert2 = true)

  <body class="hold-transition dark-mode login-page">
  <div class="login-box">
    <div class="card card-outline card-primary">
      <div class="text-center card-header">
        <a href="{{ route('welcome') }}" class="h1"><b>{{ config('app.name', 'CtrlPanel.gg') }}</b></a>
      </div>
      <div class="card-body login-card-body">
        <p class="login-box-msg">{{ __('Two-Factor Authentication') }}</p>
        <p class="text-center small text-muted">
            {{ __('Select a verification method') }}
        </p>

        <div class="list-group mt-3">
            @foreach($methods as $method)
                <a href="{{ route('login.2fa.method', ['method' => $method->getName()]) }}" 
                   class="list-group-item list-group-item-action d-flex align-items-center bg-dark border-secondary">
                    <div class="mr-3">
                        <i class="{{ $method->getIcon() }} fa-lg text-primary"></i>
                    </div>
                    <div>
                        <div class="text-sm font-weight-bold">{{ $method->getLabel() }}</div>
                        <div class="text-xs text-muted">{{ $method->getDescription() }}</div>
                    </div>
                </a>
            @endforeach
        </div>

        <p class="mt-4 mb-1 text-center">
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
