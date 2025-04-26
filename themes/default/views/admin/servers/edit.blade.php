@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="w-full mb-8">
        <div class="glass-panel p-6">
            <h1 class="text-3xl font-light text-white">{{ __('Edit Server') }}</h1>
            <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-zinc-400">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li><a href="{{ route('admin.servers.index') }}" class="hover:text-white transition-colors">{{ __('Servers') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li class="text-zinc-500">{{ __('Edit') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Warning Notice -->
    <div class="w-full mb-8">
        <div class="glass-panel bg-red-500/5 text-red-400">
            <div class="p-6 flex items-start gap-4">
                <i class="fas fa-exclamation-circle mt-1"></i>
                <div>
                    <h4 class="font-medium text-lg mb-1">{{ __('ATTENTION!') }}</h4>
                    <p>{{ __('Only edit these settings if you know exactly what you are doing') }}</p>
                    <p>{{ __('You usually do not need to change anything here') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-full">
        <div class="glass-panel">
            <div class="p-6 border-b border-zinc-800/50">
                <h3 class="text-lg font-medium text-white">{{ __('Server Details') }}</h3>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.servers.update', $server->id) }}" method="POST" class="max-w-xl">
                    @csrf
                    @method('PATCH')
                    
                    <div class="space-y-6">
                        <div>
                            <label for="identifier" class="block text-sm font-medium text-zinc-400 mb-2">
                                {{ __('Server identifier') }}
                                <i data-toggle="popover" data-trigger="hover"
                                   data-content="{{ __('Change the server identifier on CtrlPanel to match a pterodactyl server.') }}"
                                   class="fas fa-info-circle text-zinc-500 ml-1"></i>
                            </label>
                            <input value="{{ $server->identifier }}" id="identifier" name="identifier"
                                   type="text" class="input @error('identifier') border-red-500 @enderror"
                                   required>
                            @error('identifier')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="user_id" class="block text-sm font-medium text-zinc-400 mb-2">
                                {{ __('Server owner') }}
                                <i data-toggle="popover" data-trigger="hover"
                                   data-content="{{ __('Change the current server owner on CtrlPanel and pterodactyl.') }}"
                                   class="fas fa-info-circle text-zinc-500 ml-1"></i>
                            </label>
                            <select name="user_id" id="user_id" class="input">
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        @if ($user->id == $server->user_id) selected @endif>{{ $user->name }}
                                        ({{ $user->email }})
                                        ({{ $user->id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                        </div>
                    </div>

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
