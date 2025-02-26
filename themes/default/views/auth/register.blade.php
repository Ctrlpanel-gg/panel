@extends('layouts.app')

@section('content')

  <body class="hold-transition dark-mode register-page">
  <div class="register-box">
    <div class="card card-outline card-primary">
      <div class="text-center card-header">
        <a href="{{ route('welcome') }}" class="h1"><b
            class="mr-1">{{ config('app.name', 'Laravel') }}</b></a>
      </div>
      <div class="card-body">
        @if (!app(App\Settings\UserSettings::class)->creation_enabled)
          <div class="p-2 m-2 alert alert-warning">
            <h5><i class="icon fas fa-exclamation-circle"></i> {{ __('Warning!') }}</h5>
            {{ __('The system administrator has blocked the registration of new users') }}
          </div>
          <div class="text-center">
            <a class="btn btn-primary" href="{{ route('login') }}">{{ __('Back') }}</a>
          </div>
        @else
          <p class="login-box-msg">{{ __('Register a new membership') }}</p>

          <form method="POST" action="{{ route('register') }}">

            @error('ip')
            <span class="text-danger" role="alert">
                                    <small><strong>{{ $message }}</strong></small>
                                </span>
            @enderror

            @error('registered')
            <span class="text-danger" role="alert">
                                    <small><strong>{{ $message }}</strong></small>
                                </span>
            @enderror
            @if ($errors->has('ptero_registration_error'))
              @foreach ($errors->get('ptero_registration_error') as $err)
                <span class="text-danger" role="alert">
                                        <small><strong>{{ $err }}</strong></small>
                                    </span>
              @endforeach
            @endif

            @csrf
            <div class="form-group">
              <div class="input-group">
                <input type="text" class="form-control @error('name') is-invalid @enderror"
                       name="name" value="{{ old('name') }}" placeholder="{{ __('Username') }}"
                       required autocomplete="name" autofocus>
                <div class="input-group-append">
                  <div class="input-group-text">
                    <span class="fas fa-user"></span>
                  </div>
                </div>
              </div>
              @error('name')
              <span class="text-danger" role="alert">
                                        <small><strong>{{ $message }}</strong></small>
                                    </span>
              @enderror
            </div>


            <div class="form-group">
              <div class="mb-3 input-group">
                <input type="email" name="email"
                       class="form-control  @error('email') is-invalid @enderror"
                       placeholder="{{ __('Email') }}" value="{{ old('email') }}" required
                       autocomplete="email">
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
              <div class="mb-3 input-group">
                <input type="password" class="form-control @error('password') is-invalid @enderror"
                       placeholder="{{ __('Password') }}" name="password" required
                       autocomplete="new-password">
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

            <div class="mb-3 input-group">
              <input type="password" class="form-control" name="password_confirmation"
                     placeholder="{{ __('Retype password') }}" required autocomplete="new-password">
              <div class="input-group-append">
                <div class="input-group-text">
                  <span class="fas fa-lock"></span>
                </div>
              </div>
            </div>
            @if (app(App\Settings\ReferralSettings::class)->enabled)
              <div class="mb-3 input-group">
                <input type="text" value="{{ Request::get('ref') }}" class="form-control"
                       name="referral_code"
                       placeholder="{{ __('Referral code') }} ({{ __('optional') }})">
                <div class="input-group-append">
                  <div class="input-group-text">
                    <span class="fas fa-user-check"></span>
                  </div>
                </div>
              </div>
            @endif

            @php ($recaptchaVersion = app(App\Settings\GeneralSettings::class)->recaptcha_version)
            @if ($recaptchaVersion)
              <div class="mb-3 input-group">
                @switch($recaptchaVersion)
                  @case("v2")
                    {!! htmlFormSnippet() !!}
                    @break
                  @case("v3")
                    {!! RecaptchaV3::field('recaptchathree') !!}
                    @break
                @endswitch

                @error('g-recaptcha-response')
                <span class="text-danger" role="alert">
                          <small><strong>{{ $message }}</strong></small>
                        </span>
                @enderror
              </div>
            @endif


            <div class="row">
              <div class="col-12">
                @php($website_settings = app(App\Settings\WebsiteSettings::class))
                @if ($website_settings->show_tos)
                  <div class="icheck-primary">
                    <input type="checkbox" id="agreeTerms" name="terms" value="agree">
                    <label for="agreeTerms">
                      {{__("I agree to the")}} <a target="_blank"
                                                  href="{{ route('terms', 'tos') }}">{{__("Terms of Service")}}</a>
                    </label>
                  </div>
                  @error('terms')
                  <span class="text-danger" role="alert">
                                            <small><strong>{{ $message }}</strong></small>
                                       </span>
                  @enderror
                @endif
              </div>
            </div>
            <!-- /.col -->
            <div class="col-4">
              <button type="submit" class="btn btn-primary">{{ __('Register') }}</button>
            </div>
            <!-- /.col -->
      </div>

      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      </form>
      <a href="{{ route('login') }}" class="text-center">{{ __('I already have a membership') }}</a>
    </div>
    <!-- /.form-box -->
  </div><!-- /.card -->
  </div>
  <!-- /.register-box -->

  {{-- imprint and privacy policy --}}
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
  @endif
@endsection
