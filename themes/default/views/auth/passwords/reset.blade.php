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
          <p class="text-center text-zinc-400 text-sm mb-6">
            {{ __('You are only one step a way from your new password, recover your password now.') }}
          </p>

          <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

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

            <div class="flex items-center justify-between pt-2">
              <a href="{{ route('login') }}" class="text-zinc-400 hover:text-zinc-300 transition-colors text-sm">
                {{ __('Back to login') }}
              </a>
              <button type="submit" class="px-5 py-2 bg-zinc-800 text-zinc-200 text-sm font-medium rounded-lg hover:bg-zinc-700 active:bg-zinc-600 transition-colors duration-200">
                {{ __('Change password') }}
              </button>
            </div>

            <input type="hidden" name="_token" value="{{ csrf_token() }}">
          </form>
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
