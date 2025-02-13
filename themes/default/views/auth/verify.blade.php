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
          <h2 class="text-center text-zinc-300 text-lg mb-6">{{ __('Verify Your Email Address') }}</h2>

          @if (session('resent'))
            <div class="bg-emerald-500/10 text-emerald-400 px-4 py-3 rounded-lg text-sm mb-6 border border-emerald-500/20">
              {{ __('A fresh verification link has been sent to your email address.') }}
            </div>
          @endif

          <p class="text-zinc-400 text-sm mb-4 text-center">
            {{ __('Before proceeding, please check your email for a verification link.') }}
          </p>
          
          <p class="text-zinc-400 text-sm mb-4 text-center">
            {{ __('If you did not receive the email') }},
          </p>

          <form class="text-center" method="POST" action="{{ route('verification.resend') }}">
            @csrf
            <button type="submit" 
              class="px-5 py-2 bg-zinc-800 text-zinc-200 text-sm font-medium rounded-lg hover:bg-zinc-700 active:bg-zinc-600 transition-colors duration-200">
              {{ __('Request another verification email') }}
            </button>
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
