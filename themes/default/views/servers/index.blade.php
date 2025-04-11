@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-4 sm:p-8">
    <!-- Header -->
    <header class="w-full mb-4 sm:mb-8">
        <div class="glass-panel p-4 sm:p-6">
            <h1 class="text-xl sm:text-3xl font-light text-white">{{ __('Servers') }}</h1>
            <div class="flex flex-wrap items-center gap-2 sm:gap-4 mt-4">
                <a href="{{ route('servers.create') }}"
                   class="btn btn-primary text-xs sm:text-sm"
                   @if (Auth::user()->Servers->count() >= Auth::user()->server_limit || !Auth::user()->can("user.server.create"))
                   disabled
                   @endif>
                    <i class="fas fa-plus mr-2"></i>
                    {{ __('Create Server') }}
                </a>

                @if (Auth::user()->Servers->count() > 0 && !empty($phpmyadmin_url))
                    <a href="{{ $phpmyadmin_url }}" target="_blank"
                       class="btn bg-zinc-800/50 text-zinc-300 hover:bg-zinc-800 text-xs sm:text-sm">
                        <i class="fas fa-database mr-2"></i>
                        {{ __('Database') }}
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Servers Grid -->
    <div class="w-full">
        <div class="space-y-4">
            @foreach ($servers as $server)
                @if($server->location && $server->node && $server->nest && $server->egg)
                    <a href="{{ route('servers.show', ['server' => $server->id]) }}">
                        <div class="bg-background-secondary hover:bg-background-secondary/80 border border-neutral p-4 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="bg-secondary/10 p-2 rounded-lg">
                                        <i class="fas fa-server size-5 text-secondary"></i>
                                    </div>
                                    <span class="font-medium">{{ $server->name }}</span>
                                    <span class="text-base/50 font-semibold">
                                        <i class="fas fa-circle size-1 text-base/20"></i>
                                    </span>
                                    <span class="text-base text-sm">{{ $server->product->name }}</span>
                                </div>
                                <div class="size-5 rounded-md p-0.5
                                    @if ($server->suspended) text-red-500 bg-red-500/20
                                    @elseif($server->canceled) text-amber-500 bg-amber-500/20
                                    @else text-emerald-500 bg-emerald-500/20
                                    @endif">
                                    @if ($server->suspended)
                                        <i class="fas fa-ban"></i>
                                    @elseif($server->canceled)
                                        <i class="fas fa-times-circle"></i>
                                    @else
                                        <i class="fas fa-check-circle"></i>
                                    @endif
                                </div>
                            </div>
                            <p class="text-base text-sm">
                                {{ __('Location') }}: {{ $server->location }} | 
                                {{ __('Software') }}: {{ $server->nest }} | 
                                {{ __('Specification') }}: {{ $server->egg }}
                            </p>
                            <p class="text-base text-sm">
                                {{ __('Next Billing Cycle') }}: 
                                @if ($server->suspended)
                                    -
                                @else
                                    @switch($server->product->billing_period)
                                        @case('monthly')
                                            {{ \Carbon\Carbon::parse($server->last_billed)->addMonth()->toDayDateTimeString(); }}
                                            @break
                                        @case('weekly')
                                            {{ \Carbon\Carbon::parse($server->last_billed)->addWeek()->toDayDateTimeString(); }}
                                            @break
                                        @case('daily')
                                            {{ \Carbon\Carbon::parse($server->last_billed)->addDay()->toDayDateTimeString(); }}
                                            @break
                                        @case('hourly')
                                            {{ \Carbon\Carbon::parse($server->last_billed)->addHour()->toDayDateTimeString(); }}
                                            @break
                                        @case('quarterly')
                                            {{ \Carbon\Carbon::parse($server->last_billed)->addMonths(3)->toDayDateTimeString(); }}
                                            @break
                                        @case('half-annually')
                                            {{ \Carbon\Carbon::parse($server->last_billed)->addMonths(6)->toDayDateTimeString(); }}
                                            @break
                                        @case('annually')
                                            {{ \Carbon\Carbon::parse($server->last_billed)->addYear()->toDayDateTimeString(); }}
                                            @break
                                        @default
                                            {{ __('Unknown') }}
                                    @endswitch
                                @endif
                            </p>
                        </div>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endsection
