@extends('layouts.app')

@section('content')
  @php($website_settings = app(App\Settings\WebsiteSettings::class))
  <body class="min-h-screen bg-zinc-950 flex items-center justify-center py-12">
    <div class="w-full max-w-md px-4">
      <div class="bg-zinc-900/50 backdrop-blur-sm rounded-xl shadow-2xl text-zinc-300 border border-zinc-800/50">
        <div class="text-center p-6">
          <a href="{{ route('welcome') }}">
            <span class="text-2xl font-light text-white">{{ config('app.name', 'Laravel') }}</span>
          </a>
        </div>

        <div class="px-6 pb-6">
          @if (!app(App\Settings\UserSettings::class)->creation_enabled)
            <div class="bg-amber-500/10 text-amber-400 px-4 py-3 rounded-lg text-sm mb-6 border border-amber-500/20">
              <h5 class="font-medium mb-1"><i class="icon fas fa-exclamation-circle"></i> {{ __('Warning!') }}</h5>
              {{ __('The system administrator has blocked the registration of new users') }}
            </div>
            <div class="text-center">
              <a class="px-5 py-2 bg-zinc-800 text-zinc-200 text-sm font-medium rounded-lg hover:bg-zinc-700 transition-colors inline-block" 
                 href="{{ route('login') }}">{{ __('Back') }}</a>
            </div>
          @else
            <p class="text-center text-zinc-400 text-sm mb-6">{{ __('Register a new membership') }}</p>

            <form method="POST" action="{{ route('register') }}">
              @csrf
              
              @if ($errors->has('ip') || $errors->has('registered') || $errors->has('ptero_registration_error'))
                <div class="bg-red-500/10 text-red-400 px-4 py-3 rounded-lg text-sm mb-6 border border-red-500/20">
                  @error('ip')<div class="mb-1">{{ $message }}</div>@enderror
                  @error('registered')<div class="mb-1">{{ $message }}</div>@enderror
                  @if ($errors->has('ptero_registration_error'))
                    @foreach ($errors->get('ptero_registration_error') as $err)
                      <div class="mb-1">{{ $err }}</div>
                    @endforeach
                  @endif
                </div>
              @endif

              <!-- Input fields -->
              <div class="space-y-4">
                <div>
                  <input type="text" name="name" value="{{ old('name') }}"
                    class="w-full px-4 py-2.5 bg-zinc-950 border border-zinc-800 rounded-lg text-sm transition-colors duration-200 ease-in-out focus:ring-2 focus:ring-zinc-700 focus:border-transparent @error('name') border-red-900 @enderror"
                    placeholder="{{ __('Username') }}" required>
                  @error('name')
                    <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                  @enderror
                </div>

                <div>
                  <input type="email" name="email" value="{{ old('email') }}"
                    class="w-full px-4 py-2.5 bg-zinc-950 border border-zinc-800 rounded-lg text-sm transition-colors duration-200 ease-in-out focus:ring-2 focus:ring-zinc-700 focus:border-transparent @error('email') border-red-900 @enderror"
                    placeholder="{{ __('Email') }}" required>
                  @error('email')
                    <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                  @enderror
                </div>

                <div>
                  <input type="password" name="password"
                    class="w-full px-4 py-2.5 bg-zinc-950 border border-zinc-800 rounded-lg text-sm transition-colors duration-200 ease-in-out focus:ring-2 focus:ring-zinc-700 focus:border-transparent @error('password') border-red-900 @enderror"
                    placeholder="{{ __('Password') }}" required>
                  @error('password')
                    <p class="text-red-400 text-xs mt-2">{{ $message }}</p>
                  @enderror
                </div>

                <div>
                  <input type="password" name="password_confirmation"
                    class="w-full px-4 py-2.5 bg-zinc-950 border border-zinc-800 rounded-lg text-sm transition-colors duration-200 ease-in-out focus:ring-2 focus:ring-zinc-700 focus:border-transparent"
                    placeholder="{{ __('Retype password') }}" required>
                </div>

                @php ($recaptchaVersion = app(App\Settings\GeneralSettings::class)->recaptcha_version)
                @if ($recaptchaVersion)
                  <div class="flex justify-center">
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

                @if ($website_settings->show_tos)
                  <div class="flex items-center space-x-2">
                    <input type="checkbox" id="agreeTerms" name="terms" value="agree"
                      class="form-checkbox bg-zinc-950 border-zinc-800 rounded text-zinc-600">
                    <label for="agreeTerms" class="text-zinc-400 text-sm">
                      {{__("I agree to the")}} <a target="_blank" href="{{ route('terms', 'tos') }}" 
                        class="text-zinc-300 hover:text-zinc-100 transition-colors">{{__("Terms of Service")}}</a>
                    </label>
                  </div>
                  @error('terms')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                  @enderror
                @endif

                <div class="flex items-center justify-between pt-2">
                  <a href="{{ route('login') }}" class="text-zinc-400 hover:text-zinc-300 transition-colors text-sm">
                    {{ __('I already have a membership') }}
                  </a>
                  <button type="submit" class="px-5 py-2 bg-zinc-800 text-zinc-200 text-sm font-medium rounded-lg hover:bg-zinc-700 active:bg-zinc-600 transition-colors duration-200">
                    {{ __('Register') }}
                  </button>
                </div>
              </div>

              <input type="hidden" name="_token" value="{{ csrf_token() }}">
            </form>
          @endif
        </div>
      </div>
    </div>

    <!-- Footer Links -->
    <div class="fixed bottom-0 left-0 right-0 p-4">
      <div class="container mx-auto text-center text-sm text-zinc-600 space-x-6">
        @if ($website_settings->show_imprint)
          <a href="{{ route('terms', 'imprint') }}" target="_blank" class="hover:text-zinc-500">{{ __('Imprint') }}</a>
        @endif
        @if ($website_settings->show_privacy)
          <a href="{{ route('terms', 'privacy') }}" target="_blank" class="hover:text-zinc-500">{{ __('Privacy') }}</a>
        @endif
        @if ($website_settings->show_tos)
          <a href="{{ route('terms', 'tos') }}" target="_blank" class="hover:text-zinc-500">{{ __('Terms of Service') }}</a>
        @endif
      </div>
    </div>
  </body>
@endsection
