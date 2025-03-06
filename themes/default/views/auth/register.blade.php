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
          @if (!app(App\Settings\UserSettings::class)->creation_enabled)
            <div class="p-4 mb-6 rounded-lg bg-amber-500/10 text-amber-400">
              <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-exclamation-circle"></i>
                <strong>{{ __('Warning!') }}</strong>
              </div>
              <p>{{ __('The system administrator has blocked the registration of new users') }}</p>
            </div>
            <div class="text-center">
              <a class="btn btn-primary" href="{{ route('login') }}">{{ __('Back') }}</a>
            </div>
          @else
            <p class="text-zinc-400 text-center mb-6">{{ __('Register a new membership') }}</p>

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
              @csrf
              
              @error('ip')
                <div class="text-sm text-red-400"><strong>{{ $message }}</strong></div>
              @enderror

              @error('registered')
                <div class="text-sm text-red-400"><strong>{{ $message }}</strong></div>
              @enderror

              @if ($errors->has('ptero_registration_error'))
                @foreach ($errors->get('ptero_registration_error') as $err)
                  <div class="text-sm text-red-400"><strong>{{ $err }}</strong></div>
                @endforeach
              @endif

              <!-- Username Input -->
              <div>
                <div class="relative">
                  <input type="text" name="name" value="{{ old('name') }}"
                    class="form-input pr-10 @error('name') border-red-500/50 focus:border-red-500 @enderror"
                    placeholder="{{ __('Username') }}" required autocomplete="name" autofocus>
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-500">
                    <i class="fas fa-user"></i>
                  </div>
                </div>
                @error('name')
                  <div class="mt-2 text-sm text-red-400"><strong>{{ $message }}</strong></div>
                @enderror
              </div>

              <!-- Email Input -->
              <div>
                <div class="relative">
                  <input type="email" name="email" value="{{ old('email') }}"
                    class="form-input pr-10 @error('email') border-red-500/50 focus:border-red-500 @enderror"
                    placeholder="{{ __('Email') }}" required autocomplete="email">
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-500">
                    <i class="fas fa-envelope"></i>
                  </div>
                </div>
                @error('email')
                  <div class="mt-2 text-sm text-red-400"><strong>{{ $message }}</strong></div>
                @enderror
              </div>

              <!-- Password Input -->
              <div>
                <div class="relative">
                  <input type="password" name="password"
                    class="form-input pr-10 @error('password') border-red-500/50 focus:border-red-500 @enderror"
                    placeholder="{{ __('Password') }}" required autocomplete="new-password">
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-500">
                    <i class="fas fa-lock"></i>
                  </div>
                </div>
                @error('password')
                  <div class="mt-2 text-sm text-red-400"><strong>{{ $message }}</strong></div>
                @enderror
              </div>

              <!-- Confirm Password Input -->
              <div>
                <div class="relative">
                  <input type="password" name="password_confirmation"
                    class="form-input pr-10"
                    placeholder="{{ __('Retype password') }}" required autocomplete="new-password">
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-500">
                    <i class="fas fa-lock"></i>
                  </div>
                </div>
              </div>

              <!-- Referral Code -->
              @if (app(App\Settings\ReferralSettings::class)->enabled)
                <div>
                  <div class="relative">
                    <input type="text" name="referral_code" value="{{ Request::get('ref') }}"
                      class="form-input pr-10"
                      placeholder="{{ __('Referral code') }} ({{ __('optional') }})">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-zinc-500">
                      <i class="fas fa-user-check"></i>
                    </div>
                  </div>
                </div>
              @endif

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
                    <div class="mt-2 text-sm text-red-400"><strong>{{ $message }}</strong></div>
                  @enderror
                </div>
              @endif

              <!-- Terms of Service -->
              @if ($website_settings->show_tos)
                <div class="flex items-center gap-2">
                  <input type="checkbox" id="agreeTerms" name="terms" value="agree" class="form-checkbox">
                  <label for="agreeTerms" class="text-sm text-zinc-400">
                    {{__("I agree to the")}} <a target="_blank" href="{{ route('terms', 'tos') }}" class="text-primary-400 hover:text-primary-300">{{__("Terms of Service")}}</a>
                  </label>
                </div>
                @error('terms')
                  <div class="text-sm text-red-400"><strong>{{ $message }}</strong></div>
                @enderror
              @endif

              <!-- Submit Button -->
              <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">{{ __('Register') }}</button>
              </div>

              <input type="hidden" name="_token" value="{{ csrf_token() }}">
            </form>

            <!-- Login Link -->
            <div class="mt-6 text-center">
              <a href="{{ route('login') }}" class="text-sm text-primary-400 hover:text-primary-300">
                {{ __('I already have a membership') }}
              </a>
            </div>
          @endif
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
