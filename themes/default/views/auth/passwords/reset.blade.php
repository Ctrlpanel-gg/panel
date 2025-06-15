@extends('layouts.app')

@section('content')

    <body class="min-h-screen bg-gradient-to-br from-zinc-900 via-primary-950 to-zinc-900 flex items-center justify-center px-4">
        <div class="w-full max-w-md space-y-8">
            <!-- Logo -->
            <div class="text-center">
                <a href="{{ route('welcome') }}" class="inline-block">
                    <h1 class="h1 text-primary-400">{{ config('app.name', 'Laravel') }}</h1>
                </a>
            </div>

            <!-- Card -->
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-8">
                        <h2 class="h2 mb-2">{{ __('Reset Password') }}</h2>
                        <p class="body-2 text-zinc-400">
                            {{ __('You are only one step away from your new password, recover your password now.') }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <!-- Email Field -->
                        <div class="space-y-2">
                            <label for="email" class="label">{{ __('Email') }}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-zinc-500"></i>
                                </div>
                                <input 
                                    type="email" 
                                    name="email" 
                                    id="email"
                                    class="input pl-10 @error('email') border-red-500 @enderror"
                                    placeholder="{{ __('Email') }}"
                                    value="{{ old('email') }}"
                                    required
                                >
                            </div>
                            @error('email')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Field -->
                        <div class="space-y-2">
                            <label for="password" class="label">{{ __('Password') }}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-zinc-500"></i>
                                </div>
                                <input 
                                    type="password" 
                                    name="password" 
                                    id="password"
                                    class="input pl-10 @error('password') border-red-500 @enderror"
                                    placeholder="{{ __('Password') }}"
                                    required 
                                    autocomplete="new-password"
                                >
                            </div>
                            @error('password')
                                <p class="text-red-500 text-sm">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="space-y-2">
                            <label for="password_confirmation" class="label">{{ __('Confirm Password') }}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-zinc-500"></i>
                                </div>
                                <input 
                                    type="password" 
                                    name="password_confirmation" 
                                    id="password_confirmation"
                                    class="input pl-10"
                                    placeholder="{{ __('Retype password') }}"
                                    required 
                                    autocomplete="new-password"
                                >
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4">
                            <button type="submit" class="btn btn-primary w-full">
                                {{ __('Change password') }}
                            </button>
                        </div>

                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>

                    <!-- Login Link -->
                    <div class="text-center mt-6 pt-6 border-t border-zinc-800">
                        <p class="body-2 text-zinc-400">
                            {{ __('Remember your password?') }}
                            <a href="{{ route('login') }}" class="text-primary-400 hover:text-primary-300 transition-colors">
                                {{ __('Sign in') }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- imprint and privacy policy --}}
        <footer class="fixed bottom-0 left-0 right-0 p-4">
            <div class="container text-center">
                @php($website_settings = app(App\Settings\WebsiteSettings::class))
                <div class="flex flex-wrap justify-center items-center space-x-1 text-sm text-zinc-500">
                    @if ($website_settings->show_imprint)
                        <a href="{{ route('terms', 'imprint') }}" 
                           target="_blank" 
                           class="hover:text-primary-400 transition-colors font-medium">
                            {{ __('Imprint') }}
                        </a>
                        @if ($website_settings->show_privacy || $website_settings->show_tos)
                            <span class="text-zinc-600">|</span>
                        @endif
                    @endif
                    @if ($website_settings->show_privacy)
                        <a href="{{ route('terms', 'privacy') }}" 
                           target="_blank" 
                           class="hover:text-primary-400 transition-colors font-medium">
                            {{ __('Privacy') }}
                        </a>
                        @if ($website_settings->show_tos)
                            <span class="text-zinc-600">|</span>
                        @endif
                    @endif
                    @if ($website_settings->show_tos)
                        <a href="{{ route('terms', 'tos') }}" 
                           target="_blank" 
                           class="hover:text-primary-400 transition-colors font-medium">
                            {{ __('Terms of Service') }}
                        </a>
                    @endif
                </div>
            </div>
        </footer>
    </body>
@endsection
