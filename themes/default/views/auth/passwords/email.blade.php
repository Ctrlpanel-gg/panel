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
        </div>

        <!-- Form -->
        <div class="p-6">
          @if (session('status'))
            <div class="p-4 mb-6 rounded-lg bg-emerald-500/10 text-emerald-400 text-sm">
              {{ session('status') }}
            </div>
          @endif

          <p class="text-zinc-400 text-center mb-6">
            {{ __('You forgot your password? Here you can easily retrieve a new password.') }}
          </p>

          <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <!-- Email Input -->
            <div>
              <div class="relative">
                <input type="email" name="email" value="{{ old('email') }}"
                  class="form-input pr-10 @error('email') border-red-500/50 focus:border-red-500 @enderror"
                  placeholder="{{ __('Email') }}" required autocomplete="email" autofocus>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-500">
                  <i class="fas fa-envelope"></i>
                </div>
              </div>
              @error('email')
                <div class="mt-2 text-sm text-red-400">
                  <strong>{{ $message }}</strong>
                </div>
              @enderror
            </div>

            <!-- Recaptcha -->
            @php ($recaptchaVersion = app(App\Settings\GeneralSettings::class)->recaptcha_version)
            @if ($recaptchaVersion)
              <div>
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

            <!-- Submit Button -->
            <div>
              <button type="submit" class="w-full btn btn-primary">
                {{ __('Request new password') }}
              </button>
            </div>

            <input type="hidden" name="_token" value="{{ csrf_token() }}">
          </form>

          <!-- Login Link -->
          <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm text-primary-400 hover:text-primary-300">
              {{ __('Login') }}
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
