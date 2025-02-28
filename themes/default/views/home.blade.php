@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-4 sm:p-8">
    <!-- Header -->
    <header class="max-w-screen-2xl mx-auto mb-6 sm:mb-8">
        <div class="glass-panel p-4 sm:p-6">
            <h1 class="text-2xl sm:text-3xl font-light text-white">{{ __('Dashboard') }}</h1>
            <div class="text-zinc-400 text-sm mt-2">
                {{ __('Welcome back') }}, {{ Auth::user()->name }}
            </div>
        </div>
    </header>

    <!-- Admin Warning -->
    @if (!file_exists(base_path() . '/install.lock') && Auth::user()->hasRole("Admin"))
        <div class="max-w-screen-2xl mx-auto mb-6 sm:mb-8">
            <div class="glass-panel p-4 sm:p-6 bg-red-500/5 text-red-400">
                <div class="flex items-center gap-3 mb-2">
                    <i class="fas fa-exclamation-circle text-lg"></i>
                    <h4 class="font-medium">{{ __('The installer is not locked!') }}</h4>
                </div>
                <p class="text-sm opacity-90 mb-3">{{ __('Please create a file called "install.lock" in your dashboard root directory. Otherwise, no settings will be loaded!') }}</p>
                <a href="/install?step=7" class="inline-flex items-center px-4 py-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 text-sm rounded-lg border border-red-500/20 transition-colors">
                    {{ __('or click here') }}
                </a>
            </div>
        </div>
    @endif

    <!-- Alert Message -->
    @if ($general_settings->alert_enabled && !empty($general_settings->alert_message))
        <div class="max-w-screen-2xl mx-auto mb-6 sm:mb-8">
            <div class="glass-panel p-4 sm:p-6 text-zinc-300">
                {!! $general_settings->alert_message !!}
            </div>
        </div>
    @endif

    <!-- Stats Grid -->
    <div class="max-w-screen-2xl mx-auto mb-6 sm:mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <!-- Servers -->
            <div class="stats-card glass-morphism p-4 sm:p-6">
                <div class="stats-icon blue">
                    <i class="fas fa-server text-lg sm:text-xl"></i>
                </div>
                <div>
                    <div class="stats-text-label">{{ __('Servers') }}</div>
                    <div class="stats-text-value">{{ Auth::user()->servers()->count() }}</div>
                </div>
            </div>

            <!-- Credits -->
            <div class="stats-card glass-morphism p-4 sm:p-6">
                <div class="stats-icon emerald">
                    <i class="fas fa-coins text-lg sm:text-xl"></i>
                </div>
                <div>
                    <div class="stats-text-label">{{ $general_settings->credits_display_name }}</div>
                    <div class="stats-text-value">{{ Auth::user()->Credits() }}</div>
                </div>
            </div>

            <!-- Usage -->
            <div class="stats-card glass-morphism p-4 sm:p-6">
                <div class="stats-icon amber">
                    <i class="fas fa-chart-line text-lg sm:text-xl"></i>
                </div>
                <div>
                    <div class="stats-text-label">{{ __('Usage') }}</div>
                    <div class="stats-text-value">
                        {{ number_format($usage, 2, '.', '') }}
                        <span class="stats-text-subtitle">{{ __('per month') }}</span>
                    </div>
                </div>
            </div>

            <!-- Credits Remaining -->
            @if ($credits > 0.01 && $usage > 0)
            <div class="stats-card glass-morphism p-4 sm:p-6">
                <div class="stats-icon red">
                    <i class="fas fa-hourglass-half text-lg sm:text-xl"></i>
                </div>
                <div>
                    <div class="stats-text-label">{{ __('Credits Remaining') }}</div>
                    <div class="stats-text-value">
                        {{ $boxText }}<span class="stats-text-subtitle">{{ $unit }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto">
        <!-- Toast notification for URL copy -->
        <div id="url-copy-toast" class="fixed top-5 right-5 z-[9999] hidden">
            <div class="flex items-center w-full max-w-xs p-4 text-zinc-300 bg-zinc-900/95 rounded-lg shadow border border-zinc-800/50 backdrop-blur-sm" role="alert">
                <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-emerald-500 bg-emerald-500/10 rounded-lg">
                    <i class="fas fa-check"></i>
                </div>
                <div class="ml-3 text-sm font-normal">{{ __('URL copied to clipboard') }}</div>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
            <!-- Left Column -->
            <div class="space-y-6 sm:space-y-8">
                <!-- MOTD -->
                @if ($website_settings->motd_enabled)
                    <div class="card glass-morphism">
                        <div class="p-4 sm:p-6 border-b border-zinc-800/50">
                            <h3 class="text-white font-medium flex items-center gap-2">
                                <i class="fas fa-bullhorn text-zinc-400"></i>
                                {{ __('Announcement') }}
                            </h3>
                        </div>
                        <div class="p-4 sm:p-6">
                            <div class="prose prose-invert max-w-none w-full prose-sm sm:prose-base">
                                {!! $website_settings->motd_message !!}
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Useful Links -->
                @if ($website_settings->useful_links_enabled)
                    <div class="card glass-morphism">
                        <div class="p-4 sm:p-6 border-b border-zinc-800/50">
                            <h3 class="text-white font-medium flex items-center gap-2">
                                <i class="fas fa-link text-zinc-400"></i>
                                {{ __('Useful Links') }}
                            </h3>
                        </div>
                        <div class="p-4 sm:p-6">
                            @if($useful_links_dashboard->count())
                                <div class="space-y-3 sm:space-y-4">
                                    @foreach ($useful_links_dashboard as $useful_link)
                                        <a href="{{ $useful_link->link }}" target="_blank" 
                                           class="block p-3 sm:p-4 bg-zinc-800/50 rounded-lg hover:bg-zinc-800 transition-colors">
                                            <h4 class="text-white font-medium flex items-center gap-2 mb-2 text-sm sm:text-base">
                                                <i class="{{ $useful_link->icon }} text-zinc-400"></i>
                                                {{ $useful_link->title }}
                                            </h4>
                                            <div class="text-xs sm:text-sm text-zinc-400">
                                                {!! $useful_link->description !!}
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-zinc-500 text-sm">{{ __('No useful links available') }}</div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column -->
            <div class="space-y-6 sm:space-y-8">
                <!-- Partner Program -->
                @if ($referral_settings->enabled)
                    <div class="card glass-morphism">
                        <div class="p-6 border-b border-zinc-800/50">
                            <h3 class="text-white font-medium flex items-center gap-2">
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
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <!-- Your Discount -->
                                        <div class="glass-panel bg-zinc-800/30 p-4">
                                            <div class="flex items-center gap-3 mb-2">
                                                <div class="rounded-lg p-2 bg-blue-500/10">
                                                    <i class="fas fa-percentage text-blue-400"></i>
                                                </div>
                                                <span class="text-sm text-zinc-400">{{ __('Your discount') }}</span>
                                            </div>
                                            <div class="text-xl font-medium text-white">
                                                {{ $partnerDiscount->partner_discount }}%
                                            </div>
                                        </div>

                                        <!-- New User Discount -->
                                        <div class="glass-panel bg-zinc-800/30 p-4">
                                            <div class="flex items-center gap-3 mb-2">
                                                <div class="rounded-lg p-2 bg-emerald-500/10">
                                                    <i class="fas fa-tag text-emerald-400"></i>
                                                </div>
                                                <span class="text-sm text-zinc-400">{{ __('New user discount') }}</span>
                                            </div>
                                            <div class="text-xl font-medium text-white">
                                                {{ $partnerDiscount->registered_user_discount }}%
                                            </div>
                                        </div>

                                        <!-- Reward per User -->
                                        <div class="glass-panel bg-zinc-800/30 p-4">
                                            <div class="flex items-center gap-3 mb-2">
                                                <div class="rounded-lg p-2 bg-amber-500/10">
                                                    <i class="fas fa-gift text-amber-400"></i>
                                                </div>
                                                <span class="text-sm text-zinc-400">{{ __('Reward per user') }}</span>
                                            </div>
                                            <div class="text-xl font-medium text-white">
                                                {{ $referral_settings->reward }}
                                                <span class="text-sm text-zinc-500">{{ $general_settings->credits_display_name }}</span>
                                            </div>
                                        </div>

                                        <!-- Commission Rate -->
                                        <div class="glass-panel bg-zinc-800/30 p-4">
                                            <div class="flex items-center gap-3 mb-2">
                                                <div class="rounded-lg p-2 bg-purple-500/10">
                                                    <i class="fas fa-chart-line text-purple-400"></i>
                                                </div>
                                                <span class="text-sm text-zinc-400">{{ __('Commission rate') }}</span>
                                            </div>
                                            <div class="text-xl font-medium text-white">
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
                                                    <span class="text-sm text-zinc-400">{{ __('Reward per user') }}</span>
                                                </div>
                                                <div class="text-xl font-medium text-white">
                                                    {{ $referral_settings->reward }}
                                                    <span class="text-sm text-zinc-500">{{ $general_settings->credits_display_name }}</span>
                                                </div>
                                            </div>
                                        @endif
                                        @if(in_array($referral_settings->mode, ["commission","both"]))
                                            <div class="glass-panel bg-zinc-800/30 p-4">
                                                <div class="flex items-center gap-3 mb-2">
                                                    <div class="rounded-lg p-2 bg-purple-500/10">
                                                        <i class="fas fa-chart-line text-purple-400"></i>
                                                    </div>
                                                    <span class="text-sm text-zinc-400">{{ __('Commission rate') }}</span>
                                                </div>
                                                <div class="text-xl font-medium text-white">
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
                <div class="card glass-morphism">
                    <div class="p-6 border-b border-zinc-800/50">
                        <h3 class="text-white font-medium flex items-center gap-2">
                            <i class="fas fa-history text-zinc-400"></i>
                            {{ __('Activity Logs') }}
                        </h3>
                    </div>
                    <div class="p-6 text-zinc-300">
                        <ul class="list-group list-group-flush">
                            @if(Auth::user()->actions()->count())
                                @foreach (Auth::user()->actions()->take(8)->orderBy('created_at', 'desc')->get() as $log)
                                    <li class="flex justify-between py-2 text-zinc-400">
                                        <span>
                                            @if (str_starts_with($log->description, 'created'))
                                                <small><i class="mr-2 fas text-emerald-500 fa-plus"></i></small>
                                            @elseif(str_starts_with($log->description, 'redeemed'))
                                                <small><i class="mr-2 fas text-emerald-500 fa-money-check-alt"></i></small>
                                            @elseif(str_starts_with($log->description, 'deleted'))
                                                <small><i class="mr-2 fas text-red-500 fa-times"></i></small>
                                            @elseif(str_starts_with($log->description, 'gained'))
                                                <small><i class="mr-2 fas text-emerald-500 fa-money-bill"></i></small>
                                            @elseif(str_starts_with($log->description, 'updated'))
                                                <small><i class="mr-2 fas text-blue-500 fa-pen"></i></small>
                                            @endif
                                            {{ explode('\\', $log->subject_type)[2] }}
                                            {{ ucfirst($log->description) }}

                                            @php
                                                $properties = json_decode($log->properties, true);
                                            @endphp

                                            {{-- Handle Created Entries --}}
                                            @if ($log->description === 'created' && isset($properties['attributes']))
                                                <ul class="ml-3 mt-1 space-y-1 text-zinc-500">
                                                    @foreach ($properties['attributes'] as $attribute => $value)
                                                        @if (!is_null($value))
                                                            <li>
                                                                <strong class="text-zinc-400">{{ ucfirst($attribute) }}:</strong>
                                                                {{ $attribute === 'created_at' || $attribute === 'updated_at' ? \Carbon\Carbon::parse($value)->toDayDateTimeString() : $value }}
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            @endif

                                            {{-- Handle Updated Entries --}}
                                            @if ($log->description === 'updated' && isset($properties['attributes'], $properties['old']))
                                                <ul class="ml-3 mt-1 space-y-1 text-zinc-500">
                                                    @foreach ($properties['attributes'] as $attribute => $newValue)
                                                        @if (array_key_exists($attribute, $properties['old']) && !is_null($newValue))
                                                            <li>
                                                                <strong class="text-zinc-400">{{ ucfirst($attribute) }}:</strong>
                                                                {{ $attribute === 'created_at' || $attribute === 'updated_at' ? \Carbon\Carbon::parse($properties['old'][$attribute])->toDayDateTimeString() . ' → ' . \Carbon\Carbon::parse($newValue)->toDayDateTimeString() : $properties['old'][$attribute] . ' → ' . $newValue }}
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            @endif

                                            {{-- Handle Deleted Entries --}}
                                            @if ($log->description === 'deleted' && isset($properties['old']))
                                                <ul class="ml-3 mt-1 space-y-1 text-zinc-500">
                                                    @foreach ($properties['old'] as $attribute => $value)
                                                        @if (!is_null($value))
                                                            <li>
                                                                <strong class="text-zinc-400">{{ ucfirst($attribute) }}:</strong>
                                                                {{ $attribute === 'created_at' || $attribute === 'updated_at' ? \Carbon\Carbon::parse($value)->toDayDateTimeString() : $value }}
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </span>
                                        <small class="text-zinc-600">{{ $log->created_at->diffForHumans() }}</small>
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
    var originalText = document.getElementById('RefLink').innerText;
    var link = "{{ route('register') . '?ref=' . Auth::user()->referral_code }}";
    var timeoutID;

    function hoverIn() {
        document.getElementById('RefLink').innerText = link;
        timeoutID = setTimeout(function() {
            document.getElementById('RefLink').innerText = originalText;
        }, 2000);
    }

    function hoverOut() {
        document.getElementById('RefLink').innerText = originalText;
        clearTimeout(timeoutID);
    }

    function onClickCopy() {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(link).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: '{{ __('URL copied to clipboard') }}',
                    position: 'top-middle',
                    showConfirmButton: false,
                    background: '#343a40',
                    toast: false,
                    timer: 10000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                })
            })
        } else {
            console.log('Browser Not compatible')
        }
    }
</script>
@endsection
