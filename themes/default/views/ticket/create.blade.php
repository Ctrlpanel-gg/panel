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
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <header class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <h1 class="text-3xl font-light text-white">{{ __('Create Ticket') }}</h1>
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
    <div class="max-w-screen-xl mx-auto">
        <form action="{{route('ticket.new.store')}}" method="POST" class="ticket-form">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Ticket Details -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="text-white font-medium flex items-center gap-2">
                            <i class="fas fa-ticket-alt text-zinc-400"></i>
                            {{__('Ticket Details')}}
                        </h5>
                    </div>
                    <div class="card-body space-y-6">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-zinc-400 mb-2">{{__('Title')}}</label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}" 
                                   class="input @error('title') border-red-500/50 @enderror">
                            @error('title')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Server Selection -->
                        <div>
                            <label for="server" class="block text-sm font-medium text-zinc-400 mb-2">{{__('Server')}}</label>
                            <select name="server" id="server" class="input">
                                <option value="">{{ __('No Server') }}</option>
                                @foreach($servers as $server)
                                    <option value="{{ $server->id }}">{{ $server->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="ticketcategory" class="block text-sm font-medium text-zinc-400 mb-2">{{__('Category')}}</label>
                            <select name="ticketcategory" id="ticketcategory" required 
                                    class="input @error('ticketcategory') border-red-500/50 @enderror">
                                <option value="" disabled selected>{{__('Select Category')}}</option>
                                @foreach($ticketcategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('ticketcategory')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Priority -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-zinc-400 mb-2">{{__('Priority')}}</label>
                            <select name="priority" id="priority" 
                                    class="input @error('priority') border-red-500/50 @enderror">
                                <option value="" disabled selected>{{__('Select Priority')}}</option>
                                <option value="Low">{{__('Low')}}</option>
                                <option value="Medium">{{__('Medium')}}</option>
                                <option value="High">{{__('High')}}</option>
                            </select>
                            @error('priority')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Recaptcha -->
                        @if($recaptchaVersion)
                            <div>
                                @switch($recaptchaVersion)
                                    @case('v2')
                                        {!! htmlFormSnippet() !!}
                                        @break
                                    @case('v3')
                                        {!! RecaptchaV3::field('recaptchathree') !!}
                                        @break
                                @endswitch
                                @error('g-recaptcha-response')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Message Content -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="text-white font-medium flex items-center gap-2">
                            <i class="fas fa-comment text-zinc-400"></i>
                            {{__('Message')}}
                        </h5>
                    </div>
                    <div class="card-body">
                        <textarea name="message" id="message" rows="12" 
                                  class="input @error('message') border-red-500/50 @enderror"
                                  placeholder="{{__('Describe your issue...')}}">{{old('message')}}</textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-8 flex justify-end">
                <button type="submit" class="btn btn-primary ticket-once">
                    <i class="fas fa-paper-plane mr-2"></i>
                    {{__('Create Ticket')}}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('.ticket-form');
        const submitBtn = document.querySelector('.ticket-once');
        
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
        });
    });
</script>
@endsection
