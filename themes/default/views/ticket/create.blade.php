@extends('layouts.main')
@php ($recaptchaVersion = app(App\Settings\GeneralSettings::class)->recaptcha_version)
@if ($recaptchaVersion)
  @switch($recaptchaVersion)
    @case("v2")
      {!! htmlScriptTagJsApi() !!}
      @break
    @case("v3")
      {!! RecaptchaV3::initJs() !!}
      @break
  @endswitch
@endif
@section('content')
<div class="min-h-screen bg-primary-950 p-4 sm:p-8">
    <!-- Header -->
    <header class="max-w-screen-2xl mx-auto mb-6">
        <div class="glass-panel p-4 sm:p-6">
            <h1 class="text-2xl sm:text-3xl font-light text-white">{{ __('Create Ticket') }}</h1>
            <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-zinc-400">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li><a href="{{ route('ticket.index') }}" class="hover:text-white transition-colors">{{ __('Tickets') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li class="text-zinc-500">{{ __('Create') }}</li>
                </ol>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto">
        <form action="{{route('ticket.new.store')}}" method="POST" class="ticket-form">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                <!-- Ticket Details -->
                <div class="glass-panel">
                    <div class="p-6 border-b border-zinc-800/50">
                        <h5 class="text-lg font-medium text-white flex items-center">
                            <i class="fas fa-ticket-alt mr-2 text-zinc-400"></i>
                            {{ __('Ticket Details') }}
                        </h5>
                    </div>
                    <div class="p-6">
                        <!-- Title -->
                        <div class="mb-5">
                            <label for="title" class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Title') }}</label>
                            <input id="title" type="text" name="title" value="{{ old('title') }}"
                                class="form-input @error('title') border-red-500/50 focus:border-red-500 @enderror">
                            @error('title')
                                <div class="mt-1 text-sm text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Server -->
                        <div class="mb-5">
                            <label for="server" class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Server') }}</label>
                            <select id="server" name="server" class="form-select">
                                <option value="">{{ __("No Server") }}</option>
                                @if ($servers->count() >= 1)
                                    @foreach ($servers as $server)
                                        <option value="{{ $server->id }}">{{ $server->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('server')
                                <div class="mt-1 text-sm text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div class="mb-5">
                            <label for="ticketcategory" class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Category') }}</label>
                            <select id="ticketcategory" name="ticketcategory" required class="form-select @error('ticketcategory') border-red-500/50 focus:border-red-500 @enderror">
                                <option value="" disabled selected>{{ __("Select Category") }}</option>
                                @foreach ($ticketcategories as $ticketcategory)
                                    <option value="{{ $ticketcategory->id }}">{{ $ticketcategory->name }}</option>
                                @endforeach
                            </select>
                            @error('ticketcategory')
                                <div class="mt-1 text-sm text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Priority -->
                        <div class="mb-5">
                            <label for="priority" class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Priority') }}</label>
                            <select id="priority" name="priority" class="form-select @error('priority') border-red-500/50 focus:border-red-500 @enderror">
                                <option value="" disabled selected>{{ __("Select Priority") }}</option>
                                <option value="Low">{{ __("Low") }}</option>
                                <option value="Medium">{{ __("Medium") }}</option>
                                <option value="High">{{ __("High") }}</option>
                            </select>
                            @error('priority')
                                <div class="mt-1 text-sm text-red-400">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Recaptcha -->
                        @if ($recaptchaVersion)
                            <div class="mb-5">
                                @switch($recaptchaVersion)
                                    @case("v2")
                                        {!! htmlFormSnippet() !!}
                                        @break
                                    @case("v3")
                                        {!! RecaptchaV3::field('recaptchathree') !!}
                                        @break
                                @endswitch

                                @error('g-recaptcha-response')
                                    <div class="mt-1 text-sm text-red-400">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Message -->
                <div class="glass-panel">
                    <div class="p-6 border-b border-zinc-800/50">
                        <h5 class="text-lg font-medium text-white flex items-center">
                            <i class="fas fa-comment-alt mr-2 text-zinc-400"></i>
                            {{ __('Message') }}
                        </h5>
                    </div>
                    <div class="p-6">
                        <div>
                            <label for="message" class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Message Details') }}</label>
                            <textarea rows="10" id="message" name="message" class="form-textarea @error('message') border-red-500/50 focus:border-red-500 @enderror">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="mt-1 text-sm text-red-400">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="btn btn-primary ticket-once">
                                {{ __('Open Ticket') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ticketForm = document.querySelector(".ticket-form");
        if (ticketForm) {
            ticketForm.addEventListener("submit", function() {
                const submitButton = document.querySelector(".ticket-once");
                if (submitButton) {
                    submitButton.disabled = true;
                }
            });
        }
    });
</script>
@endsection
