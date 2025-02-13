@extends('layouts.app')

@section('content')
  @php($website_settings = app(App\Settings\WebsiteSettings::class))
  <body class="min-h-screen bg-primary-950 flex items-center justify-center">
    <div class="w-full max-w-sm px-4 py-6">
      <div class="card">
        <div class="text-center p-6">
          <a href="{{ route('welcome') }}">
            <span class="text-2xl font-light text-white">{{ config('app.name', 'Laravel') }}</span>
          </a>
          @if ($website_settings->enable_login_logo)
            <img
              src="{{ \Illuminate\Support\Facades\Storage::disk('public')->exists('logo.png') ? asset('storage/logo.png') : asset('images/ctrlpanel_logo.png') }}"
              alt="{{ config('app.name', 'CtrlPanel.gg') }} Logo"
              class="mx-auto mt-3 h-[100px] object-contain opacity-70">
          @endif
        </div>

        <div class="px-6 pb-6">
          <p class="text-center text-zinc-400 text-sm mb-6">{{ __('Sign in to start your session') }}</p>

          @if (session('message'))
            <div class="bg-red-500/10 text-red-400 px-4 py-3 rounded-lg text-sm mb-6 border border-red-500/20">
              {{ session('message') }}
            </div>
          @endif

          <form action="{{ route('login') }}" method="post">
            @csrf
            @if (Session::has('error'))
              <p class="text-red-400 text-xs mb-3">{{ Session::get('error') }}</p>
            @endif

            <!-- Email/Username Input -->
            <div class="mb-4">
              <input type="text" name="email"
                class="w-full px-4 py-2.5 bg-zinc-950 border border-zinc-800 rounded-lg text-sm transition-colors duration-200 ease-in-out focus:ring-2 focus:ring-zinc-700 focus:border-transparent @error('email') border-red-900 @enderror @error('name') border-red-900 @enderror"
                placeholder="{{ __('Email or Username') }}">
              @if ($errors->get("email") || $errors->get("name"))
                <p class="text-red-400 text-xs mt-2">
                  {{ $errors->first('email') ? $errors->first('email') : $errors->first('name') }}
                </p>
              @endif
            </div>

            <!-- Password Input -->
            <div class="mb-4">
              <input type="password" name="password"
                class="w-full px-4 py-2.5 bg-zinc-950 border border-zinc-800 rounded-lg text-sm transition-colors duration-200 ease-in-out focus:ring-2 focus:ring-zinc-700 focus:border-transparent @error('password') border-red-900 @enderror"
                placeholder="{{ __('Password') }}">
              @error('password')
                <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
              @enderror
            </div>

            <!-- Recaptcha -->
            @php ($recaptchaVersion = app(App\Settings\GeneralSettings::class)->recaptcha_version)
            @if ($recaptchaVersion)
              <div class="mb-3">
                @switch($recaptchaVersion)
                  @case("v2")
                    {!! htmlFormSnippet() !!}
                    @break
                  @case("v3")
                    {!! RecaptchaV3::field('recaptchathree') !!}
                    @break
                @endswitch
                @error('g-recaptcha-response')
                  <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
              </div>
            @endif

            <!-- Remember Me & Submit -->
            <div class="flex items-center justify-between mb-6">
              <label class="flex items-center">
                <input type="checkbox" name="remember" class="form-checkbox bg-zinc-950 border-zinc-800 rounded text-zinc-600"
                  {{ old('remember') ? 'checked' : '' }}>
                <span class="ml-2 text-zinc-400 text-sm">{{ __('Remember Me') }}</span>
              </label>
              <button type="submit" class="px-5 py-2 bg-zinc-800 text-zinc-200 text-sm font-medium rounded-lg hover:bg-zinc-700 active:bg-zinc-600 transition-colors duration-200">
                {{ __('Sign In') }}
              </button>
            </div>

            <input type="hidden" name="_token" value="{{ csrf_token() }}">
          </form>

          <!-- Links -->
          <div class="text-center text-sm space-y-3 mt-6">
            @if (Route::has('password.request'))
              <a href="{{ route('password.request') }}" class="text-zinc-400 hover:text-zinc-300 transition-colors">
                {{ __('Forgot Your Password?') }}
              </a>
            @endif
            <p>
              <a href="{{ route('register') }}" class="text-zinc-400 hover:text-zinc-300 transition-colors">
                {{ __('Register a new membership') }}
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer Links -->
    <div class="fixed bottom-0 left-0 right-0 p-4">
      <div class="container mx-auto text-center text-sm text-primary-500 space-x-6">
        @if ($website_settings->show_imprint)
          <a href="{{ route('terms', 'imprint') }}" target="_blank" class="hover:text-primary-400">{{ __('Imprint') }}</a>
        @endif
        @if ($website_settings->show_privacy)
          <a href="{{ route('terms', 'privacy') }}" target="_blank" class="hover:text-primary-400">{{ __('Privacy') }}</a>
        @endif
        @if ($website_settings->show_tos)
          <a href="{{ route('terms', 'tos') }}" target="_blank" class="hover:text-primary-400">{{ __('Terms of Service') }}</a>
        @endif
      </div>
    </div>
  </body>
@endsection
