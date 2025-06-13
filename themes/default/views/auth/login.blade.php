@extends('layouts.app')

@section('content')
  @php($website_settings = app(App\Settings\WebsiteSettings::class))
  <div class="min-h-screen bg-primary-950 flex items-center justify-center p-4 sm:p-8">
    <div class="w-full max-w-md">
      <!-- Card -->
      <div class="card glass-morphism">
        <!-- Header -->
        <div class="p-6 text-center border-b border-zinc-800/50">
          <a href="{{ route('welcome') }}" class="inline-block mb-4">
            <span class="text-2xl font-semibold text-white">{{ config('app.name', 'Laravel') }}</span>
          </a>
          @if ($website_settings->enable_login_logo)
            <img
              src="{{ \Illuminate\Support\Facades\Storage::disk('public')->exists('logo.png') ? asset('storage/logo.png') : asset('images/ctrlpanel_logo.png') }}"
              alt="{{ config('app.name', 'CtrlPanel.gg') }} Logo"
              class="mx-auto h-32 object-contain opacity-80">
          @endif
        </div>

        <!-- Form -->
        <div class="p-6">
          <p class="text-zinc-400 text-center mb-6">{{ __('Sign in to start your session') }}</p>

          @if (session('message'))
            <div class="p-4 mb-6 rounded-lg bg-red-500/10 text-red-400 text-sm">
              {{ session('message') }}
            </div>
          @endif

          <form action="{{ route('login') }}" method="post">
            @csrf
            
            @if (Session::has('error'))
              <div class="mb-4 text-sm text-red-400">
                <strong>{{ Session::get('error') }}</strong>
              </div>
            @endif

            <!-- Email/Username Input -->
            <div class="mb-4">
              <div class="relative">
                <input type="text" name="email"
                  class="form-input pr-10 @error('email') border-red-500/50 focus:border-red-500 @enderror @error('name') border-red-500/50 focus:border-red-500 @enderror"
                  placeholder="{{ __('Email or Username') }}">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-500">
                  <i class="fas fa-envelope"></i>
                </div>
              </div>
              @if ($errors->get("email") || $errors->get("name"))
                <div class="mt-2 text-sm text-red-400">
                  <strong>{{ $errors->first('email') ? $errors->first('email') : $errors->first('name') }}</strong>
                </div>
              @endif
            </div>

            <!-- Password Input -->
            <div class="mb-4">
              <div class="relative">
                <input type="password" name="password"
                  class="form-input pr-10 @error('password') border-red-500/50 focus:border-red-500 @enderror"
                  placeholder="{{ __('Password') }}">
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-500">
                  <i class="fas fa-lock"></i>
                </div>
              </div>
              @error('password')
                <div class="mt-2 text-sm text-red-400">
                  <strong>{{ $message }}</strong>
                </div>
              @enderror
            </div>

            <!-- Recaptcha -->
            @php ($recaptchaVersion = app(App\Settings\GeneralSettings::class)->recaptcha_version)
            @if ($recaptchaVersion)
              <div class="mb-4">
                @switch($recaptchaVersion)
                  @case("v2")
                    {!! htmlFormSnippet() !!}
                    @break
                  @case("v3")
                    {!! RecaptchaV3::field('recaptchathree') !!}
                    @break
                @endswitch

                @error('g-recaptcha-response')
                  <div class="mt-2 text-sm text-red-400">
                    <strong>{{ $message }}</strong>
                  </div>
                @enderror
              </div>
            @endif

            <!-- Remember Me & Submit -->
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember"
                  class="form-checkbox" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember" class="ml-2 text-sm text-zinc-400">
                  {{ __('Remember Me') }}
                </label>
              </div>
              <button type="submit" class="btn btn-primary">{{ __('Sign In') }}</button>
            </div>

            <input type="hidden" name="_token" value="{{ csrf_token() }}">
          </form>

          <!-- Links -->
          <div class="space-y-2 text-center">
            @if (Route::has('password.request'))
              <a href="{{ route('password.request') }}" class="block text-sm text-primary-400 hover:text-primary-300">
                {{ __('Forgot Your Password?') }}
              </a>
            @endif
            <a href="{{ route('register') }}" class="block text-sm text-primary-400 hover:text-primary-300">
              {{ __('Register a new membership') }}
            </a>
          </div>
        </div>
      </div>

      <!-- Footer Links -->
      <div class="mt-8 text-center text-sm space-x-3">
        @if ($website_settings->show_imprint)
          <a href="{{ route('terms', 'imprint') }}" target="_blank" class="text-zinc-400 hover:text-zinc-300">
            {{ __('Imprint') }}
          </a>
        @endif
        @if ($website_settings->show_privacy)
          <a href="{{ route('terms', 'privacy') }}" target="_blank" class="text-zinc-400 hover:text-zinc-300">
            {{ __('Privacy') }}
          </a>
        @endif
        @if ($website_settings->show_tos)
          <a href="{{ route('terms', 'tos') }}" target="_blank" class="text-zinc-400 hover:text-zinc-300">
            {{ __('Terms of Service') }}
          </a>
        @endif
      </div>
    </div>
  </div>
@endsection
