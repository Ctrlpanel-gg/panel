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

        <!-- Content -->
        <div class="p-6">
          <h2 class="text-xl font-medium text-white text-center mb-6">{{ __('Verify Your Email Address') }}</h2>
          
          @if (session('resent'))
            <div class="p-4 mb-6 rounded-lg bg-emerald-500/10 text-emerald-400 text-sm">
              {{ __('A fresh verification link has been sent to your email address.') }}
            </div>
          @endif

          <p class="text-zinc-300 mb-4">
            {{ __('Before proceeding, please check your email for a verification link.') }}
          </p>
          
          <p class="text-zinc-400 flex flex-wrap items-center gap-1 justify-center">
            {{ __('If you did not receive the email') }},
            <form class="inline" method="POST" action="{{ route('verification.resend') }}">
              @csrf
              <button type="submit" class="text-primary-400 hover:text-primary-300 transition-colors">
                {{ __('click here to request another') }}
              </button>
              <input type="hidden" name="_token" value="{{ csrf_token() }}">
            </form>
          </p>

          <!-- Return to Login -->
          <div class="mt-8 text-center">
            <a href="{{ route('login') }}" class="text-sm text-primary-400 hover:text-primary-300">
              {{ __('Return to login') }}
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
