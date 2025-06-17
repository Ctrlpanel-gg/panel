@extends('layouts.main')

@section('content')
<div class="relative min-h-screen bg-gradient-to-br from-zinc-950 via-primary-950 to-zinc-900 overflow-hidden">
    <!-- Optimized Background Effects -->
    <div aria-hidden="true" class="fixed inset-0">
        <div class="absolute inset-0 bg-grid-pattern opacity-[0.02] pointer-events-none"></div>
        <div class="absolute -top-40 -left-40 w-80 h-80 bg-primary-500/5 rounded-full blur-3xl pointer-events-none transform-gpu"></div>
        <div class="absolute -bottom-40 -right-40 w-80 h-80 bg-accent-blue/5 rounded-full blur-3xl pointer-events-none transform-gpu"></div>
    </div>

    <!-- Main Content Container -->
    <div class="relative z-10 p-4 sm:p-8">
        <!-- Hero Header -->
        <header class="mb-8 sm:mb-12">
            <div class="max-w-7xl mx-auto">
                <div class="glass-panel p-6 sm:p-8 lg:p-10 overflow-hidden relative transform-gpu">
                    <!-- Optimized Background Gradient -->
                    <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-r from-primary-600/10 via-transparent to-accent-blue/10 pointer-events-none"></div>
                    
                    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 rounded-xl bg-primary-500/20 flex items-center justify-center">
                                    <i class="fas fa-tachometer-alt text-primary-400 text-xl"></i>
                                </div>
                                <div>
                                    <h1 class="text-2xl font-semibold text-white mb-1">{{ __('Dashboard') }}</h1>
                                    <p class="text-sm text-zinc-400">
                                        {{ __('Welcome back') }}, <span class="text-primary-400 font-medium">{{ Auth::user()->name }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="flex items-center gap-3 mt-4 lg:mt-0">
                            <button class="px-3 py-2 bg-zinc-800/50 text-zinc-300 rounded-lg hover:bg-zinc-700/50 transition-colors text-sm border border-zinc-700/50">
                                <i class="fas fa-bell mr-2 text-xs"></i>
                                {{ __('Notifications') }}
                            </button>
                            <button class="px-3 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-500 transition-colors text-sm">
                                <i class="fas fa-plus mr-2 text-xs"></i>
                                {{ __('New Server') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Admin Warning -->
        @if (!file_exists(base_path() . '/install.lock') && Auth::user()->hasRole("Admin"))
            <div class="max-w-7xl mx-auto mb-8">
                <div class="glass-panel p-6 bg-red-500/5 border-red-500/20 relative overflow-hidden transform-gpu">
                    <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-r from-red-500/10 to-transparent pointer-events-none"></div>
                    <div class="relative z-10">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-red-500/20 flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-red-400 text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-red-300 mb-2">{{ __('Security Warning') }}</h4>
                                <p class="text-sm text-red-200/90 mb-4">
                                    {{ __('The installer is not locked! Please create a file called "install.lock" in your dashboard root directory. Otherwise, no settings will be loaded!') }}
                                </p>
                                <a href="/install?step=7" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-500 transition-colors text-sm inline-flex items-center">
                                    <i class="fas fa-shield-alt mr-2 text-xs"></i>
                                    {{ __('Secure Installation') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Alert Message -->
        @if ($general_settings->alert_enabled && !empty($general_settings->alert_message))
            <div class="max-w-7xl mx-auto mb-8">
                <div class="glass-panel p-6 bg-amber-500/5 border-amber-500/20 relative overflow-hidden transform-gpu">
                    <div aria-hidden="true" class="absolute inset-0 bg-gradient-to-r from-amber-500/10 to-transparent pointer-events-none"></div>
                    <div class="relative z-10">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center">
                                <i class="fas fa-bullhorn text-amber-400 text-lg"></i>
                            </div>
                            <div class="prose prose-invert max-w-none">
                                {!! $general_settings->alert_message !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Enhanced Stats Grid -->
        <div class="max-w-7xl mx-auto mb-12">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Optimized Stats Cards -->
                <div class="group">
                    <div class="glass-panel p-6 h-full transition-transform duration-300 hover:scale-[1.02] hover:shadow-lg relative overflow-hidden transform-gpu">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-server text-blue-400 text-xl"></i>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-white group-hover:text-blue-400 transition-colors">
                                        {{ Auth::user()->servers()->count() }}
                                    </div>
                                    <div class="text-sm text-zinc-400 group-hover:text-zinc-300 transition-colors">
                                        {{ __('Servers') }}
                                    </div>
                                </div>
                            </div>
                            <div class="h-1 bg-zinc-800 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-blue-500 to-blue-400 rounded-full transform translate-x-0 group-hover:translate-x-1 transition-transform duration-500" style="width: 75%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Credits Card -->
                <div class="group">
                    <div class="glass-panel p-6 h-full transition-transform duration-300 hover:scale-[1.02] hover:shadow-lg relative overflow-hidden transform-gpu">
                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-coins text-emerald-400 text-xl"></i>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-white group-hover:text-emerald-400 transition-colors">
                                        {{ Auth::user()->Credits() }}
                                    </div>
                                    <div class="text-sm text-zinc-400 group-hover:text-zinc-300 transition-colors">
                                        {{ $general_settings->credits_display_name }}
                                    </div>
                                </div>
                            </div>
                            <div class="h-1 bg-zinc-800 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-400 rounded-full transform translate-x-0 group-hover:translate-x-1 transition-transform duration-500" style="width: 60%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usage Card -->
                <div class="group">
                    <div class="glass-panel p-6 h-full transition-transform duration-300 hover:scale-[1.02] hover:shadow-lg relative overflow-hidden transform-gpu">
                        <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-chart-line text-amber-400 text-xl"></i>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-white group-hover:text-amber-400 transition-colors">
                                        {{ number_format($usage, 2, '.', '') }}
                                    </div>
                                    <div class="text-sm text-zinc-400 group-hover:text-zinc-300 transition-colors">
                                        {{ __('Usage') }} <span class="text-xs opacity-75">{{ __('per month') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="h-1 bg-zinc-800 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-amber-500 to-amber-400 rounded-full transform translate-x-0 group-hover:translate-x-1 transition-transform duration-500" style="width: 45%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Credits Remaining Card -->
                @if ($credits > 0.01 && $usage > 0)
                <div class="group">
                    <div class="glass-panel p-6 h-full transition-transform duration-300 hover:scale-[1.02] hover:shadow-lg relative overflow-hidden transform-gpu">
                        <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 rounded-xl bg-red-500/20 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                    <i class="fas fa-hourglass-half text-red-400 text-xl"></i>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-white group-hover:text-red-400 transition-colors">
                                        {{ $boxText }}
                                    </div>
                                    <div class="text-sm text-zinc-400 group-hover:text-zinc-300 transition-colors">
                                        {{ __('Credits Remaining') }} <span class="text-xs opacity-75">{{ $unit }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="h-1 bg-zinc-800 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-red-500 to-red-400 rounded-full transform translate-x-0 group-hover:translate-x-1 transition-transform duration-500" style="width: 30%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto">
            <!-- Toast notification for URL copy -->
            <div id="url-copy-toast" class="fixed top-5 right-5 z-[9999] hidden transform-gpu">
                <div class="flex items-center w-full max-w-xs p-4 text-zinc-300 bg-zinc-900/95 rounded-xl shadow-lg border border-zinc-800/50 backdrop-blur-sm" role="alert">
                    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-emerald-500 bg-emerald-500/10 rounded-lg">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="ml-3 text-sm font-normal">{{ __('URL copied to clipboard') }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <!-- Left Column (2/3 width) -->
                <div class="xl:col-span-2 space-y-8">
                    <!-- MOTD -->
                    @if ($website_settings->motd_enabled)
                        <div class="card group hover:scale-[1.01] transition-all duration-300">
                            <div class="card-header bg-gradient-to-r from-primary-500/10 to-transparent">
                                <h3 class="text-lg font-semibold text-white flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-primary-500/20 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-bullhorn text-primary-400"></i>
                                    </div>
                                    {{ __('Announcement') }}
                                </h3>
                            </div>
                            <div class="card-body prose prose-invert max-w-none">
                                {!! $website_settings->motd_message !!}
                            </div>
                        </div>
                    @endif

                    <!-- Useful Links -->
                    @if ($website_settings->useful_links_enabled)
                        <div class="card group hover:scale-[1.01] transition-all duration-300">
                            <div class="card-header bg-gradient-to-r from-accent-blue/10 to-transparent">
                                <h3 class="text-lg font-semibold text-white flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-accent-blue/20 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                        <i class="fas fa-link text-accent-blue"></i>
                                    </div>
                                    {{ __('Useful Links') }}
                                </h3>
                            </div>
                            <div class="card-body">
                                @if($useful_links_dashboard->count())
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach ($useful_links_dashboard as $useful_link)
                                            <a href="{{ $useful_link->link }}" target="_blank" 
                                               class="group/link p-4 bg-zinc-800/30 rounded-xl hover:bg-zinc-800/50 transition-all duration-300 hover:scale-105 border border-transparent hover:border-zinc-700/50">
                                                <div class="flex items-start gap-3">
                                                    <div class="w-8 h-8 rounded-lg bg-zinc-700/50 flex items-center justify-center flex-shrink-0 group-hover/link:scale-110 transition-transform duration-300">
                                                        <i class="{{ $useful_link->icon }} text-zinc-400 group-hover/link:text-accent-blue transition-colors"></i>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <h4 class="text-sm font-medium text-white mb-2 group-hover/link:text-accent-blue transition-colors truncate">
                                                            {{ $useful_link->title }}
                                                        </h4>
                                                        <div class="text-xs text-zinc-400 line-clamp-2">
                                                            {!! $useful_link->description !!}
                                                        </div>
                                                    </div>
                                                    <i class="fas fa-external-link-alt text-zinc-600 group-hover/link:text-accent-blue transition-colors"></i>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <div class="w-16 h-16 rounded-full bg-zinc-800/50 flex items-center justify-center mx-auto mb-4">
                                            <i class="fas fa-link text-zinc-500 text-xl"></i>
                                        </div>
                                        <p class="text-sm text-zinc-500">{{ __('No useful links available') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column (1/3 width) -->
                <div class="space-y-8">
                <!-- Partner Program -->
                @if ($referral_settings->enabled)
                    <div class="card ">
                        <div class="p-6 border-b border-zinc-800/50">
                            <h3 class="text-white font-medium flex items-center gap-2 text-base">
                                <i class="fas fa-handshake text-zinc-400"></i>
                                {{ __('Partner program') }}
                            </h3>
                        </div>
                        <div class="p-6">
                            @if (Auth::user()->can("user.referral"))
                                <!-- Referral URL Card -->
                                <div class="glass-panel bg-zinc-800/30 p-4 mb-6">
                                    <div class="flex flex-col sm:flex-row gap-4 items-center">
                                        <div class="flex-1 w-full">
                                            <div class="relative">
                                                <div class="flex items-center gap-3 px-4 py-3 bg-zinc-900/50 rounded-lg cursor-pointer hover:bg-zinc-900/70 transition-colors group"
                                                     onmouseover="hoverIn()" onmouseout="hoverOut()" onclick="onClickCopy()">
                                                    <i class="fa fa-link text-zinc-500 group-hover:text-zinc-400 transition-colors"></i>
                                                    <span id="RefLink" class="text-sm text-zinc-400 group-hover:text-zinc-300 transition-colors">
                                                        {{ __('Click to copy referral URL') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="shrink-0">
                                            <div class="flex items-center gap-2 px-4 py-3 bg-zinc-900/50 rounded-lg">
                                                <i class="fas fa-users text-zinc-500"></i>
                                                <span class="text-sm text-zinc-400">
                                                    {{ $numberOfReferrals }} {{ __('referred users') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if ($partnerDiscount)
                                    <!-- Partner Stats -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <!-- Your Discount -->
                                        <div class="glass-panel bg-zinc-800/30 p-4">
                                            <div class="flex items-center gap-3 mb-2">
                                                <div class="rounded-lg p-2 bg-blue-500/10">
                                                    <i class="fas fa-percentage text-blue-400"></i>
                                                </div>
                                                <span class="text-xs text-zinc-400">{{ __('Your discount') }}</span>
                                            </div>
                                            <div class="text-lg font-medium text-white">
                                                {{ $partnerDiscount->partner_discount }}%
                                            </div>
                                        </div>

                                        <!-- New User Discount -->
                                        <div class="glass-panel bg-zinc-800/30 p-4">
                                            <div class="flex items-center gap-3 mb-2">
                                                <div class="rounded-lg p-2 bg-emerald-500/10">
                                                    <i class="fas fa-tag text-emerald-400"></i>
                                                </div>
                                                <span class="text-xs text-zinc-400">{{ __('New user discount') }}</span>
                                            </div>
                                            <div class="text-lg font-medium text-white">
                                                {{ $partnerDiscount->registered_user_discount }}%
                                            </div>
                                        </div>

                                        <!-- Reward per User -->
                                        <div class="glass-panel bg-zinc-800/30 p-4">
                                            <div class="flex items-center gap-3 mb-2">
                                                <div class="rounded-lg p-2 bg-amber-500/10">
                                                    <i class="fas fa-gift text-amber-400"></i>
                                                </div>
                                                <span class="text-xs text-zinc-400">{{ __('Reward per user') }}</span>
                                            </div>
                                            <div class="text-lg font-medium text-white">
                                                {{ $referral_settings->reward }}
                                                <span class="text-xs text-zinc-500">{{ $general_settings->credits_display_name }}</span>
                                            </div>
                                        </div>

                                        <!-- Commission Rate -->
                                        <div class="glass-panel bg-zinc-800/30 p-4">
                                            <div class="flex items-center gap-3 mb-2">
                                                <div class="rounded-lg p-2 bg-purple-500/10">
                                                    <i class="fas fa-chart-line text-purple-400"></i>
                                                </div>
                                                <span class="text-xs text-zinc-400">{{ __('Commission rate') }}</span>
                                            </div>
                                            <div class="text-lg font-medium text-white">
                                                {{ $partnerDiscount->referral_system_commission == -1 ? $referral_settings->percentage : $partnerDiscount->referral_system_commission }}%
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <!-- Regular User Stats -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        @if(in_array($referral_settings->mode, ["sign-up","both"]))
                                            <div class="glass-panel bg-zinc-800/30 p-4">
                                                <div class="flex items-center gap-3 mb-2">
                                                    <div class="rounded-lg p-2 bg-amber-500/10">
                                                        <i class="fas fa-gift text-amber-400"></i>
                                                    </div>
                                                    <span class="text-xs text-zinc-400">{{ __('Reward per user') }}</span>
                                                </div>
                                                <div class="text-lg font-medium text-white">
                                                    {{ $referral_settings->reward }}
                                                    <span class="text-xs text-zinc-500">{{ $general_settings->credits_display_name }}</span>
                                                </div>
                                            </div>
                                        @endif
                                        @if(in_array($referral_settings->mode, ["commission","both"]))
                                            <div class="glass-panel bg-zinc-800/30 p-4">
                                                <div class="flex items-center gap-3 mb-2">
                                                    <div class="rounded-lg p-2 bg-purple-500/10">
                                                        <i class="fas fa-chart-line text-purple-400"></i>
                                                    </div>
                                                    <span class="text-xs text-zinc-400">{{ __('Commission rate') }}</span>
                                                </div>
                                                <div class="text-lg font-medium text-white">
                                                    {{ $referral_settings->percentage }}%
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @else
                                <div class="flex items-center gap-3 px-4 py-3 bg-amber-500/10 rounded-lg">
                                    <div class="rounded-lg p-2 bg-amber-500/20">
                                        <i class="fas fa-lock text-amber-400"></i>
                                    </div>
                                    <span class="text-sm text-amber-400">
                                        {{ __('Make a purchase to reveal your referral URL') }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Activity Logs -->
                <div class="card ">
                    <div class="p-6 border-b border-zinc-800/50">
                        <h3 class="text-white font-medium flex items-center gap-2 font-oxanium">
                            <i class="fas fa-history text-zinc-400"></i>
                            {{ __('Activity Logs') }}
                        </h3>
                    </div>
                    <div class="p-6 text-zinc-300">
                        <ul class="list-group list-group-flush">
                            @if(Auth::user()->actions()->count())
                                @foreach (Auth::user()->actions()->take(8)->orderBy('created_at', 'desc')->get() as $log)
                                    <li class="flex flex-col py-2 text-zinc-400 border-b border-zinc-800/10 last:border-0">
                                        <div class="flex justify-between cursor-pointer" onclick="toggleDetails('details-home-{{$log->id}}')">
                                            <div class="flex items-center gap-3">
                                                <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg 
                                                    @if (str_starts_with($log->description, 'created')) bg-emerald-500/10
                                                    @elseif(str_starts_with($log->description, 'redeemed')) bg-emerald-500/10
                                                    @elseif(str_starts_with($log->description, 'deleted')) bg-red-500/10
                                                    @elseif(str_starts_with($log->description, 'gained')) bg-emerald-500/10
                                                    @elseif(str_starts_with($log->description, 'updated')) bg-blue-500/10
                                                    @endif">
                                                    @if (str_starts_with($log->description, 'created'))
                                                        <i class="fas fa-plus text-emerald-500"></i>
                                                    @elseif(str_starts_with($log->description, 'redeemed'))
                                                        <i class="fas fa-money-check-alt text-emerald-500"></i>
                                                    @elseif(str_starts_with($log->description, 'deleted'))
                                                        <i class="fas fa-times text-red-500"></i>
                                                    @elseif(str_starts_with($log->description, 'gained'))
                                                        <i class="fas fa-money-bill text-emerald-500"></i>
                                                    @elseif(str_starts_with($log->description, 'updated'))
                                                        <i class="fas fa-pen text-blue-500"></i>
                                                    @endif
                                                </span>
                                                <div class="flex-1">
                                                    <div class="text-zinc-100 font-medium">
                                                        {{ explode('\\', $log->subject_type)[2] }}
                                                    </div>
                                                    <div class="text-sm text-zinc-500">
                                                        {{ ucfirst($log->description) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <small class="text-zinc-600">{{ $log->created_at->diffForHumans() }}</small>
                                                <i class="fas fa-chevron-down text-zinc-500 ml-2 transition-transform" id="icon-{{$log->id}}"></i>
                                            </div>
                                        </div>

                                        @php
                                            $properties = json_decode($log->properties, true);
                                        @endphp

                                        <div id="details-home-{{$log->id}}" class="hidden pl-11 space-y-2 mt-2" onclick="event.stopPropagation()">
                                            <div class="border-l-2 border-zinc-800 pl-3 py-1">
                                                @if ($log->description === 'created' && isset($properties['attributes']))
                                                    @foreach($properties['attributes'] as $key => $value)
                                                        @if(!is_null($value) && !is_array($value))
                                                            <div class="flex items-center gap-2 text-sm">
                                                                <span class="text-zinc-500 min-w-[120px]">{{ $key }}</span>
                                                                <span class="text-zinc-300">{{ $value }}</span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @elseif($log->description === 'updated' && isset($properties['attributes'], $properties['old']))
                                                    @foreach($properties['attributes'] as $key => $value)
                                                        @if(array_key_exists($key, $properties['old']) && !is_null($value) && !is_array($value))
                                                            <div class="flex items-center gap-2 text-sm">
                                                                <span class="text-zinc-500 min-w-[120px]">{{ $key }}</span>
                                                                <div class="flex items-center gap-2">
                                                                    <span class="text-red-400">{{ $properties['old'][$key] }}</span>
                                                                    <i class="fas fa-arrow-right text-zinc-600 text-xs"></i>
                                                                    <span class="text-emerald-400">{{ $value }}</span>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @elseif($log->description === 'deleted' && isset($properties['old']))
                                                    @foreach($properties['old'] as $key => $value)
                                                        @if(!is_null($value) && !is_array($value))
                                                            <div class="flex items-center gap-2 text-sm">
                                                                <span class="text-zinc-500 min-w-[120px]">{{ $key }}</span>
                                                                <span class="text-zinc-300">{{ $value }}</span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            @else
                                <li class="py-2 text-zinc-400">{{ __('No activity logs available') }}</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Optimized JavaScript with debouncing
    const originalText = document.getElementById('RefLink')?.innerText;
    const link = "{{ route('register') . '?ref=' . Auth::user()->referral_code }}";
    let timeoutID;

    function debounce(func, wait) {
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeoutID);
                func(...args);
            };
            clearTimeout(timeoutID);
            timeoutID = setTimeout(later, wait);
        };
    }

    const hoverIn = debounce(() => {
        const refLink = document.getElementById('RefLink');
        if (refLink) {
            refLink.innerText = link;
            timeoutID = setTimeout(() => {
                refLink.innerText = originalText;
            }, 2000);
        }
    }, 100);

    const hoverOut = () => {
        const refLink = document.getElementById('RefLink');
        if (refLink) {
            refLink.innerText = originalText;
            clearTimeout(timeoutID);
        }
    };

    // Optimized copy function with better UX
    async function onClickCopy() {
        try {
            await navigator.clipboard.writeText(link);
            Toast.fire({
                icon: 'success',
                title: 'Link copied to clipboard'
            });
        } catch (err) {
            console.error('Failed to copy:', err);
            Toast.fire({
                icon: 'error',
                title: 'Failed to copy link'
            });
        }
    }
    
    // Optimized toggle function with animation handling
    function toggleDetails(detailsId) {
        const details = document.getElementById(detailsId);
        const iconId = detailsId.replace('details-home-', 'icon-');
        const icon = document.getElementById(iconId);
        
        if (!details) return;

        const isHidden = details.classList.contains('hidden');
        
        // Use requestAnimationFrame for smooth animations
        requestAnimationFrame(() => {
            if (isHidden) {
                details.classList.remove('hidden');
                details.classList.add('animate-in', 'fade-in');
                icon?.classList.add('rotate-180');
            } else {
                details.classList.add('hidden');
                details.classList.remove('animate-in', 'fade-in');
                icon?.classList.remove('rotate-180');
            }
        });
    }
</script>

<style>
    /* Optimized animations using transform instead of opacity */
    .fade-in {
        @apply transition-all duration-200 ease-out;
    }

    .animate-in {
        animation: slideIn 200ms ease-out forwards;
    }

    .rotate-180 {
        transform: rotate(180deg);
        transition: transform 200ms ease-out;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(4px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Hardware accelerated animations */
    .glass-panel {
        @apply backdrop-blur-sm bg-zinc-900/20 border border-zinc-800/50;
        transform: translateZ(0);
        backface-visibility: hidden;
    }

    .stats-card {
        @apply transition-colors duration-200;
        transform: translateZ(0);
    }

    .stats-card:hover {
        @apply bg-zinc-800/30;
    }
</style>
@endsection
