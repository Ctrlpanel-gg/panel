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
    <div class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Create Ticket') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li><a href="{{ route('ticket.index') }}" class="hover:text-white transition-colors">{{ __('Ticket') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Create') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-screen-xl mx-auto">
        <form action="{{route('ticket.new.store')}}" method="POST" class="ticket-form">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Ticket Details -->
                <div class="glass-panel">
                    <div class="p-6 border-b border-zinc-800/50">
                        <h5 class="text-lg font-medium text-white flex items-center">
                            <i class="fas fa-ticket-alt mr-2 text-zinc-400"></i>
                            {{__('Open a new ticket')}}
                        </h5>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="space-y-2">
                            <label for="title" class="block text-sm font-medium text-zinc-400">{{__('Title')}}</label>
                            <input id="title" type="text" class="input" name="title" value="{{ old('title') }}">
                            @if ($errors->has('title'))
                                <p class="text-sm text-red-500">{{ $errors->first('title') }}</p>
                            @endif
                        </div>

                        <div class="space-y-2">
                            <label for="server" class="block text-sm font-medium text-zinc-400">{{__('Server')}}</label>
                            <select id="server" name="server" class="input">
                                <option value="">{{ __("No Server") }}</option>
                                @foreach ($servers as $server)
                                    <option value="{{ $server->id }}">{{ $server->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="ticketcategory" class="block text-sm font-medium text-zinc-400">{{__('Category')}}</label>
                            <select id="ticketcategory" name="ticketcategory" class="input" required>
                                <option value="" disabled selected>{{__("Select Category")}}</option>
                                @foreach ($ticketcategories as $ticketcategory)
                                    <option value="{{ $ticketcategory->id }}">{{ $ticketcategory->name }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('ticketcategory'))
                                <p class="text-sm text-red-500">{{ $errors->first('ticketcategory') }}</p>
                            @endif
                        </div>

                        <div class="space-y-2">
                            <label for="priority" class="block text-sm font-medium text-zinc-400">{{__('Priority')}}</label>
                            <select id="priority" name="priority" class="input">
                                <option value="" disabled selected>{{__("Select Priority")}}</option>
                                <option value="Low">{{__("Low")}}</option>
                                <option value="Medium">{{__("Medium")}}</option>
                                <option value="High">{{__("High")}}</option>
                            </select>
                            @if ($errors->has('priority'))
                                <p class="text-sm text-red-500">{{ $errors->first('priority') }}</p>
                            @endif
                        </div>

                        @if ($recaptchaVersion)
                            <div class="space-y-2">
                                @switch($recaptchaVersion)
                                    @case("v2")
                                        {!! htmlFormSnippet() !!}
                                        @break
                                    @case("v3")
                                        {!! RecaptchaV3::field('recaptchathree') !!}
                                        @break
                                @endswitch
                                @error('g-recaptcha-response')
                                    <p class="text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>
                    <div class="p-6 border-t border-zinc-800/50">
                        <button type="submit" class="btn btn-primary ticket-once">
                            {{__('Open Ticket')}}
                        </button>
                    </div>
                </div>

                <!-- Message Content -->
                <div class="glass-panel">
                    <div class="p-6 border-b border-zinc-800/50">
                        <h5 class="text-lg font-medium text-white flex items-center">
                            <i class="fas fa-message mr-2 text-zinc-400"></i>
                            {{__('Ticket details')}}
                        </h5>
                    </div>
                    <div class="p-6">
                        <div class="space-y-2">
                            <label for="message" class="block text-sm font-medium text-zinc-400">{{__('Message')}}</label>
                            <textarea rows="12" id="message" class="input" name="message">{{old("message")}}</textarea>
                            @if ($errors->has('message'))
                                <p class="text-sm text-red-500">{{ $errors->first('message') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(".ticket-form").submit(function (e) {
        $(".ticket-once").attr("disabled", true);
        return true;
    });
</script>
@endsection
