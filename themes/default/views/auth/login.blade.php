@extends('layouts.app')

@section('content')
    @php($website_settings = app(App\Settings\WebsiteSettings::class))
    <body class="hold-transition dark-mode login-page">
        <div class="login-box">
            <!-- /.login-logo -->
            <div class="card card-outline card-primary">
                <div class="card-header text-center">
                    <a href="{{ route('welcome') }}" class="h1 mb-2"><b
                            class="mr-1">{{ config('app.name', 'Laravel') }}</b></a>
                    @if ($website_settings->enable_login_logo) 
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->exists('logo.png') ? asset('storage/logo.png') : asset('images/controlpanel_logo.png') }}"
                            alt="{{ config('app.name', 'Controlpanel.gg') }} Logo" style="opacity: .8; max-width:100%; height: 150px; margin-top: 10px;">
                    @endif
                </div>
                <div class="card-body pt-0">
                    <p class="login-box-msg">{{ __('Sign in to start your session') }}</p>

                    @if (session('message'))
                        <div class="alert alert-danger">{{ session('message') }}</div>
                    @endif

                    <form action="{{ route('login') }}" method="post">
                        @csrf
                        @if (Session::has('error'))
                            <span class="text-danger" role="alert">
                                <small><strong>{{ Session::get('error') }}</strong></small>
                            </span>
                        @endif

                        <div class="form-group">
                            <div class="input-group mb-3">
                                <input type="text" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    placeholder="{{ __('Email or Username') }}">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-envelope"></span>
                                    </div>
                                </div>

                            </div>
                            @error('email')
                                <span class="text-danger" role="alert">
                                    <small><strong>{{ $message }}</strong></small>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="input-group mb-3">
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    placeholder="{{ __('Password') }}">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-lock"></span>
                                    </div>
                                </div>

                            </div>
                            @error('password')
                                <span class="text-danger" role="alert">
                                    <small><strong>{{ $message }}</strong></small>
                                </span>
                            @enderror
                        </div>
                        @if (app(App\Settings\GeneralSettings::class)->recaptcha_enabled)
                            <div class="input-group mb-3">
                                {!! htmlFormSnippet() !!}
                                @error('g-recaptcha-response')
                                    <span class="text-danger" role="alert">
                                        <small><strong>{{ $message }}</strong></small>
                                    </span>
                                @enderror
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-8">
                                <div class="icheck-primary">
                                    <input type="checkbox" name="remember" id="remember"
                                        {{ old('remember') ? 'checked' : '' }}>
                                    <label for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                            <!-- /.col -->
                            <div class="col-4">
                                <button type="submit" class="btn btn-primary btn-block">{{ __('Sign In') }}</button>
                            </div>
                            <!-- /.col -->
                        </div>
                    </form>
                    <p class="mb-1">
                        @if (Route::has('password.request'))
                            <a class="" href="{{ route('password.request') }}">
                                {{ __('Forgot Your Password?') }}
                            </a>
                        @endif
                    </p>
                    <p class="mb-0">
                        <a href="{{ route('register') }}" class="text-center">{{ __('Register a new membership') }}</a>
                    </p>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.login-box -->

        {{-- imprint and privacy policy --}}
        <div class="fixed-bottom ">
            <div class="container text-center">
                @if ($website_settings->show_imprint)
                    <a href="{{ route('imprint') }}"><strong>{{ __('Imprint') }}</strong></a> |
                @endif
                @if ($website_settings->show_privacy)
                    <a href="{{ route('privacy') }}"><strong>{{ __('Privacy') }}</strong></a>
                @endif
                @if ($website_settings->show_tos)
                    | <a target="_blank" href="{{ route('tos') }}"><strong>{{ __('Terms of Service') }}</strong></a>
                @endif
            </div>
        </div>
    </body>
@endsection
