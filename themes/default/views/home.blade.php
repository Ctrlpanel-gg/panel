@extends('layouts.main')

@section('content')
    <!-- Admin Alerts -->
    @if (!file_exists(base_path() . '/install.lock') && Auth::User()->hasRole('Admin'))
        @if (!file_exists(base_path() . '/install.lock') && Auth::User()->hasRole('Admin'))
            <div class="mx-6 mt-6 rounded-xl p-6 backdrop-blur-sm border"
                style="background: linear-gradient(to right, rgb(var(--danger) / 0.5), rgb(var(--danger) / 0.4)); border-color: rgb(var(--danger) / 0.5);">
                <h4 class="text-xl font-bold mb-2 flex items-center" style="color: rgb(var(--danger));">
                    <i class="fas fa-exclamation-triangle mr-3"></i>
                    {{ __('Please remove the install folder from your directory') }}
                </h4>
                <p class="text-gray-300 mb-4">
                    {{ __('please create a file called "install.lock" in your dashboard Root directory. Otherwise no settings will beloaded!') }}
                </p>
                <a href="/install?step=7">
                    <button class="px-6 py-2 text-white font-semibold rounded-lg transition-all duration-200"
                        style="background-color: rgb(var(--danger)); &:hover { opacity: 0.9; }">
                        <i class="fas fa-wrench mr-2"></i> {{ __('Finish installation') }}
                    </button>
                </a>
            </div>
        @endif
    @endif

    @if ($general_settings->alert_enabled && !empty($general_settings->alert_message))
        <div class="mx-6 mt-6 bg-gradient-to-r from-{{ $general_settings->alert_type === 'danger' ? 'red' : ($general_settings->alert_type === 'warning' ? 'yellow' : ($general_settings->alert_type === 'success' ? 'green' : 'blue')) }}-900/50 to-{{ $general_settings->alert_type === 'danger' ? 'red' : ($general_settings->alert_type === 'warning' ? 'yellow' : ($general_settings->alert_type === 'success' ? 'green' : 'blue')) }}-800/50 border border-{{ $general_settings->alert_type === 'danger' ? 'red' : ($general_settings->alert_type === 'warning' ? 'yellow' : ($general_settings->alert_type === 'success' ? 'green' : 'blue')) }}-500/50 rounded-xl p-6 backdrop-blur-sm"
            role="alert">
            <div class="text-gray-200">{!! $general_settings->alert_message !!}</div>
        </div>
    @endif

    <!-- Welcome Section -->
    <div class="px-6 pt-6 pb-4">
        <div class="flex items-center gap-3">
            <div class="text-4xl animate-wave">👋</div>
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-white">
                    {{ __('Welcome back') }}, <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-accent-400 to-accent-800">{{ Auth::user()->name }}</span>!
                </h1>
                <p class="text-gray-400 text-sm">{{ __('Here\'s an overview of your account') }}</p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="px-4 pb-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            <!-- Servers Card -->
            <div
                class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-4 border border-gray-700/50 hover:border-accent-500/50 transition-all duration-300 group shadow-xl hover:shadow-2xl glow-accent">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-400 text-sm font-medium mb-1">{{ __('Servers') }}</p>
                        <p class="text-2xl font-bold text-white">{{ Auth::user()->servers()->count() }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300 shadow-xl"
                        style="background: linear-gradient(to bottom right, rgb(var(--info)), rgb(var(--info) / 0.8)); box-shadow: 0 10px 15px -3px rgb(var(--info) / 0.5);">
                        <i class="fas fa-server text-white text-lg"></i>
                    </div>
                </div>
            </div>

            <!-- Credits Card -->
            <div
                class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-4 border border-gray-700/50 hover:border-accent-500/50 transition-all duration-300 group shadow-xl hover:shadow-2xl glow-accent">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-gray-400 text-sm font-medium mb-1">{{ $general_settings->credits_display_name }}</p>
                        <p class="text-2xl font-bold text-white">{{ Currency::formatForDisplay(Auth::user()->credits) }}
                        </p>
                    </div>
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300 shadow-xl shadow-yellow-500/50">
                        <i class="fas fa-coins text-white text-lg"></i>
                    </div>
                </div>
            </div>

            <!-- Time Left Card (always shown; fallbacks if data missing) -->
            @php
                $timeMessage = $timeLeft['message'] ?? __('Time Left');
                $timeValue = isset($timeLeft['value']) ? $timeLeft['value'] : '—';
                $timeUnit = $timeLeft['unit'] ?? '';

            @endphp
            @if ($credits > 10 && $usage > 0)
                <div
                    class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl p-4 border border-gray-700/50 hover:border-accent-500/50 transition-all duration-300 group shadow-xl hover:shadow-2xl glow-accent">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-gray-400 text-sm font-medium mb-1">{{ $timeMessage }}</p>
                            <p class="text-2xl font-bold text-white">
                                {{ $timeValue }}<span class="text-sm text-gray-400">{{ $timeUnit }}</span>
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 bg-warning-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300 shadow-xl shadow-warning-500/50">
                            <i class="fas fa-hourglass-half text-white text-lg"></i>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- MOTD Full Width -->
    @if ($website_settings->motd_enabled)
        <div class="px-6 pb-6">
            <div class="bg-gray-800 rounded-lg border border-gray-700/50 overflow-hidden shadow-xl">
                <div class="px-4 py-3 border-b border-gray-700/50">
                    <h3 class="text-lg font-semibold text-white">
                        {{ config('app.name', 'MOTD') }} - MOTD
                    </h3>
                </div>
                <div class="px-4 py-4 text-gray-300 unreset">
                    {!! $website_settings->motd_message !!}
                </div>
            </div>
        </div>
    @endif

    <!-- Two Column Layout: Useful Links (Left) & Activity Logs (Right) -->
    <div class="px-6 pb-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column: Useful Links -->
            <div class="space-y-6">
                @if ($website_settings->useful_links_enabled)
                    <div
                        class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700/50 overflow-hidden shadow-xl">
                        <div class="px-6 py-4 border-b border-gray-700/50 bg-gray-800/50">
                            <h3 class="text-xl font-bold text-white flex items-center">
                                <i class="fas fa-link mr-3 text-accent-400"></i>
                                {{ __('Useful Links') }}
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            @if ($useful_links_dashboard->count())
                                @foreach ($useful_links_dashboard as $useful_link)
                                    <div class="relative bg-gray-800/50 border border-gray-700/50 rounded-lg p-4 hover:border-accent-500/50 transition-all duration-300"
                                        x-data="{ show: true }" x-show="show" x-transition>
                                        <button @click="show = false" type="button"
                                            class="absolute top-3 right-3 text-gray-500 hover:text-white transition-colors">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <h5 class="mb-2">
                                            <a class="text-accent-400 hover:text-accent-300 font-semibold text-lg transition-colors flex items-center"
                                                target="_blank" href="{{ $useful_link->link }}">
                                                <i class="{{ $useful_link->icon }} mr-2"></i>
                                                {{ $useful_link->title }}
                                            </a>
                                        </h5>
                                        <div class="text-gray-300 text-sm">
                                            {!! $useful_link->description !!}
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-gray-400 text-center py-4">{{ __('No useful links available') }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column: Partner Program & Activity Logs -->
            <div class="space-y-6">
                @if ($referral_settings->enabled)
                    <div
                        class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700/50 overflow-hidden shadow-xl">
                        <div class="px-6 py-4 border-b border-gray-700/50 bg-gray-800/50">
                            <h3 class="text-xl font-bold text-white flex items-center">
                                <i class="fas fa-handshake mr-3 text-accent-400"></i>
                                {{ __('Partner program') }}
                            </h3>
                        </div>
                        <div class="p-6">
                            @if (Auth::user()->can('user.referral'))
                                <div class="grid grid-cols-1 gap-4 mb-4">
                                    <div class="rounded-lg p-4 border"
                                        style="background: linear-gradient(to right, rgb(var(--success) / 0.3), rgb(var(--success) / 0.25)); border-color: rgb(var(--success) / 0.5);">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center" style="color: rgb(var(--success));">
                                                <i class="fa fa-user-check mr-2"></i>
                                                <span class="font-medium">{{ __('Your referral URL') }}:</span>
                                            </div>
                                            <span onmouseover="hoverIn()" onmouseout="hoverOut()" onclick="onClickCopy()"
                                                id="RefLink"
                                                class="text-white font-semibold cursor-pointer hover:opacity-80 transition-colors">
                                                {{ __('Click to copy') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="rounded-lg p-4 border"
                                        style="background: linear-gradient(to right, rgb(var(--info) / 0.3), rgb(var(--info) / 0.25)); border-color: rgb(var(--info) / 0.5);">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium"
                                                style="color: rgb(var(--info));">{{ __('Number of referred users:') }}</span>
                                            <span class="text-white font-bold text-lg">{{ $numberOfReferrals }}</span>
                                        </div>
                                    </div>
                                </div>

                                @if ($partnerDiscount)
                                    <div class="border-t border-gray-700/50 mt-4 pt-4">
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm">
                                                <thead>
                                                    <tr class="border-b border-gray-700/50">
                                                        <th class="text-left py-2 px-3 text-gray-400 font-medium">
                                                            {{ __('Your discount') }}</th>
                                                        <th class="text-left py-2 px-3 text-gray-400 font-medium">
                                                            {{ __('Discount for your new users') }}</th>
                                                        <th class="text-left py-2 px-3 text-gray-400 font-medium">
                                                            {{ __('Reward per registered user') }}</th>
                                                        <th class="text-left py-2 px-3 text-gray-400 font-medium">
                                                            {{ __('New user payment commision') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="py-2 px-3 text-white font-semibold">
                                                            {{ $partnerDiscount->partner_discount }}%</td>
                                                        <td class="py-2 px-3 text-white font-semibold">
                                                            {{ $partnerDiscount->registered_user_discount }}%</td>
                                                        <td class="py-2 px-3 text-white font-semibold">
                                                            {{ Currency::formatForDisplay($referral_settings->reward) }}
                                                            {{ $general_settings->credits_display_name }}
                                                        </td>
                                                        <td class="py-2 px-3 text-white font-semibold">
                                                            {{ $partnerDiscount->referral_system_commission == -1 ? $referral_settings->percentage : $partnerDiscount->referral_system_commission }}%
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @else
                                    <div class="border-t border-gray-700/50 mt-4 pt-4">
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm">
                                                <thead>
                                                    <tr class="border-b border-gray-700/50">
                                                        @if (in_array($referral_settings->mode, ['sign-up', 'both']))
                                                            <th class="text-left py-2 px-3 text-gray-400 font-medium">
                                                                {{ __('Reward per registered user') }}</th>
                                                        @endif
                                                        @if (in_array($referral_settings->mode, ['commission', 'both']))
                                                            <th class="text-left py-2 px-3 text-gray-400 font-medium">
                                                                {{ __('New user payment commision') }}</th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        @if (in_array($referral_settings->mode, ['sign-up', 'both']))
                                                            <td class="py-2 px-3 text-white font-semibold">
                                                                {{ Currency::formatForDisplay($referral_settings->reward) }}
                                                                {{ $general_settings->credits_display_name }}
                                                            </td>
                                                        @endif
                                                        @if (in_array($referral_settings->mode, ['commission', 'both']))
                                                            <td class="py-2 px-3 text-white font-semibold">
                                                                {{ $referral_settings->percentage }}%</td>
                                                        @endif
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <div
                                    class="bg-gradient-to-r from-yellow-900/30 to-yellow-800/30 border border-yellow-500/50 rounded-lg p-4 text-center">
                                    <i class="fa fa-user-check mr-2 text-yellow-400"></i>
                                    <span
                                        class="text-yellow-300 font-medium">{{ __('Make a purchase to reveal your referral-URL') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Activity Logs (list with togglable details) -->
                <div
                    class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-xl border border-gray-700/50 overflow-hidden shadow-xl">
                    <div class="px-6 py-4 border-b border-gray-700/50 bg-gray-800/50">
                        <h3 class="text-xl font-bold text-white flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-lg bg-accent-blue/20 flex items-center justify-center duration-300">
                                <i class="fas fa-history text-accent-blue"></i>
                            </div>
                            {{ __('Activity Logs') }}
                        </h3>
                    </div>
                    <div class="p-6 text-gray-300">
                        <ul class="list-group list-group-flush">
                            @if (Auth::user()->actions()->count())
                                @foreach (Auth::user()->actions()->take(8)->orderBy('created_at', 'desc')->get() as $log)
                                    @php
                                        $properties = json_decode($log->properties, true);
                                        $subjectParts = explode('\\', $log->subject_type);
                                        $subjectLabel = end($subjectParts) ?: '-';
                                        $bgClass = 'bg-gray-800/30';
                                        $iconClass = 'text-gray-400';
                                        $icon = 'fa-circle';
                                        if (str_starts_with($log->description, 'created')) {
                                            $bgClass = 'bg-emerald-500/10';
                                            $iconClass = 'text-emerald-400';
                                            $icon = 'fa-plus';
                                        } elseif (str_starts_with($log->description, 'redeemed')) {
                                            $bgClass = 'bg-emerald-500/10';
                                            $iconClass = 'text-emerald-400';
                                            $icon = 'fa-money-check-alt';
                                        } elseif (str_starts_with($log->description, 'deleted')) {
                                            $bgClass = 'bg-red-500/10';
                                            $iconClass = 'text-red-400';
                                            $icon = 'fa-times';
                                        } elseif (str_starts_with($log->description, 'gained')) {
                                            $bgClass = 'bg-emerald-500/10';
                                            $iconClass = 'text-emerald-400';
                                            $icon = 'fa-money-bill';
                                        } elseif (str_starts_with($log->description, 'updated')) {
                                            $bgClass = 'bg-blue-500/10';
                                            $iconClass = 'text-blue-400';
                                            $icon = 'fa-pen';
                                        }
                                    @endphp
                                    <li class="flex flex-col py-2 text-gray-400 border-b border-gray-700/10 last:border-0">
                                        <div class="flex justify-between cursor-pointer"
                                            onclick="toggleDetails('details-home-{{ $log->id }}')">
                                            <div class="flex items-center gap-3">
                                                <span
                                                    class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-lg {{ $bgClass }} border border-gray-700/30">
                                                    <i class="fas {{ $icon }} {{ $iconClass }}"></i>
                                                </span>
                                                <div class="flex-1">
                                                    <div class="text-white font-medium">
                                                        {{ explode('\\', $log->subject_type)[2] ?? '-' }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ ucfirst($log->description) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <small
                                                    class="text-gray-600">{{ $log->created_at->diffForHumans() }}</small>
                                                <i class="fas fa-chevron-down text-gray-500 ml-2 transition-transform"
                                                    id="icon-{{ $log->id }}"></i>
                                            </div>
                                        </div>

                                        <div id="details-home-{{ $log->id }}" class="hidden pl-11 space-y-2 mt-2"
                                            onclick="event.stopPropagation()">
                                            <div class="border-l-2 border-gray-700 pl-3 py-2 bg-gray-900/20 rounded-md">
                                                @if ($log->description === 'created' && isset($properties['attributes']))
                                                    @foreach ($properties['attributes'] as $key => $value)
                                                        @if (!is_null($value) && !is_array($value))
                                                            <div class="flex items-center gap-2 text-sm">
                                                                <span
                                                                    class="text-gray-500 min-w-[120px]">{{ $key }}</span>
                                                                <span class="text-gray-300">{{ $value }}</span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @elseif($log->description === 'updated' && isset($properties['attributes'], $properties['old']))
                                                    @foreach ($properties['attributes'] as $key => $value)
                                                        @if (array_key_exists($key, $properties['old']) && !is_null($value) && !is_array($value))
                                                            <div class="flex items-center gap-2 text-sm">
                                                                <span
                                                                    class="text-gray-500 min-w-[120px]">{{ $key }}</span>
                                                                <div class="flex items-center gap-2">
                                                                    <span
                                                                        class="text-red-400">{{ $properties['old'][$key] }}</span>
                                                                    <i
                                                                        class="fas fa-arrow-right text-gray-600 text-xs"></i>
                                                                    <span
                                                                        class="text-emerald-400">{{ $value }}</span>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @elseif($log->description === 'deleted' && isset($properties['old']))
                                                    @foreach ($properties['old'] as $key => $value)
                                                        @if (!is_null($value) && !is_array($value))
                                                            <div class="flex items-center gap-2 text-sm">
                                                                <span
                                                                    class="text-gray-500 min-w-[120px]">{{ $key }}</span>
                                                                <span class="text-gray-300">{{ $value }}</span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            @else
                                <li class="py-2 text-gray-400">No activity logs available</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <style>
        @keyframes wave {
            0% {
                transform: rotate(0deg);
            }

            10% {
                transform: rotate(14deg);
            }

            20% {
                transform: rotate(-8deg);
            }

            30% {
                transform: rotate(14deg);
            }

            40% {
                transform: rotate(-4deg);
            }

            50% {
                transform: rotate(10deg);
            }

            60% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(0deg);
            }
        }

        .animate-wave {
            animation: wave 2s ease-in-out infinite;
            transform-origin: 70% 70%;
            display: inline-block;
        }

        .unreset img {
            max-height: 300px;
        }

        /* Activity Log toggle animations */
        .fade-in {
            transition: all 200ms ease-out;
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
    </style>

    <script>
        var originalText = 'Click to copy';
        var link = "<?php echo route('register') . '?ref=' . Auth::user()->referral_code; ?>";
        var timeoutID;

        function hoverIn() {
            const refLink = document.getElementById('RefLink');
            if (refLink) {
                refLink.innerText = link;
                timeoutID = setTimeout(function() {
                    refLink.innerText = originalText;
                }, 2000);
            }
        }

        function hoverOut() {
            const refLink = document.getElementById('RefLink');
            if (refLink) {
                refLink.innerText = originalText;
                clearTimeout(timeoutID);
            }
        }

        function onClickCopy() {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(link).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __('URL copied to clipboard') }}',
                        position: 'top-end',
                        showConfirmButton: false,
                        background: 'linear-gradient(135deg, rgb(var(--gray-800)) 0%, rgb(var(--gray-900)) 100%)',
                        color: '#fff',
                        toast: true,
                        timer: 2000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'rounded-xl border shadow-2xl',
                            timerProgressBar: 'bg-gradient-to-r',
                        },
                        didOpen: (toast) => {
                            toast.style.borderColor = 'rgb(var(--accent-500) / 0.3)';
                            const progressBar = toast.querySelector('.swal2-timer-progress-bar');
                            if (progressBar) {
                                progressBar.style.background =
                                    'linear-gradient(to right, rgb(var(--accent-500)), rgb(var(--accent-600)))';
                            }
                            toast.addEventListener('mouseenter', Swal.stopTimer);
                            toast.addEventListener('mouseleave', Swal.resumeTimer);
                        }
                    });
                }).catch(err => {
                    console.error('Failed to copy:', err);
                });
            } else {
                console.log('Browser clipboard API not available');
            }
        }

        // Toggle activity detail panels (smooth animation + icon rotation)
        function toggleDetails(detailsId) {
            const details = document.getElementById(detailsId);
            const iconId = detailsId.replace('details-home-', 'icon-');
            const icon = document.getElementById(iconId);
            if (!details) return;
            const isHidden = details.classList.contains('hidden');
            requestAnimationFrame(() => {
                if (isHidden) {
                    details.classList.remove('hidden');
                    details.classList.add('animate-in', 'fade-in');
                    if (icon) icon.classList.add('rotate-180');
                } else {
                    details.classList.add('hidden');
                    details.classList.remove('animate-in', 'fade-in');
                    if (icon) icon.classList.remove('rotate-180');
                }
            });
        }
    </script>
@endsection
