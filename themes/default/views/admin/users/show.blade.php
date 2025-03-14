@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-4 sm:p-8">
    <!-- Header -->
    <header class="max-w-screen-2xl mx-auto mb-6 sm:mb-8">
        <div class="glass-panel p-4 sm:p-6">
            <h1 class="text-2xl sm:text-3xl font-light text-white">{{ __('Users') }}</h1>
            <div class="text-zinc-400 text-sm mt-2">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2">
                        <li><a href="{{ route('home') }}" class="text-primary-400 hover:text-primary-300">{{ __('Dashboard') }}</a></li>
                        <li><span class="text-zinc-600 mx-1">/</span></li>
                        <li><a href="{{ route('admin.users.index') }}" class="text-primary-400 hover:text-primary-300">{{ __('Users') }}</a></li>
                        <li><span class="text-zinc-600 mx-1">/</span></li>
                        <li class="text-zinc-400">{{ $user->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto space-y-6">
        <!-- Discord Info -->
        @if ($user->discordUser)
        <div class="card glass-morphism">
            <div class="p-6">
                <div class="flex items-center justify-between bg-zinc-800/30 rounded-lg p-4">
                    <div class="flex items-center gap-4">
                        <img class="w-16 h-16 rounded-full" src="{{ $user->discordUser->getAvatar() }}" alt="Discord Avatar">
                        <div>
                            <h3 class="text-xl font-medium text-white">{{ $user->discordUser->username }}</h3>
                            <p class="text-zinc-400">{{ $user->discordUser->id }}</p>
                            <span class="bg-blue-500/10 text-blue-400 px-2 py-1 rounded-full text-xs">{{ $user->discordUser->locale }}</span>
                        </div>
                    </div>
                    <div class="text-zinc-400">
                        <i class="fab fa-discord text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- User Details -->
        <div class="card glass-morphism">
            <div class="p-6 border-b border-zinc-800/50">
                <h3 class="text-white font-medium flex items-center gap-2">
                    <i class="fas fa-user text-zinc-400"></i>
                    {{ __('User Details') }}
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- User Info Cards -->
                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-blue-500/10">
                                <i class="fas fa-id-card text-blue-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('ID') }}</span>
                        </div>
                        <div class="text-lg font-medium text-white">{{ $user->id }}</div>
                    </div>

                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-purple-500/10">
                                <i class="fas fa-user-tag text-purple-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('Roles') }}</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($user->roles as $role)
                                <span style="background-color: {{$role->color}}" class="px-2 py-1 rounded-full text-xs text-white">{{$role->name}}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-emerald-500/10">
                                <i class="fas fa-envelope text-emerald-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('Email') }}</span>
                        </div>
                        <div class="text-lg font-medium text-white">{{ $user->email }}</div>
                    </div>

                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-amber-500/10">
                                <i class="fas fa-server text-amber-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('Server Limit') }}</span>
                        </div>
                        <div class="text-lg font-medium text-white">{{ $user->Servers()->count() }} / {{ $user->server_limit }}</div>
                    </div>

                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-red-500/10">
                                <i class="fas fa-coins text-red-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ $credits_display_name }}</span>
                        </div>
                        <div class="text-lg font-medium text-white">{{ $user->Credits() }}</div>
                    </div>

                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-green-500/10">
                                <i class="fas fa-server text-green-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('Pterodactyl ID') }}</span>
                        </div>
                        <div class="text-lg font-medium text-white">{{ $user->pterodactyl_id }}</div>
                    </div>

                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-yellow-500/10">
                                <i class="fas fa-user text-yellow-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('Name') }}</span>
                        </div>
                        <div class="text-lg font-medium text-white">{{ $user->name }}</div>
                    </div>

                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-teal-500/10">
                                <i class="fas fa-check text-teal-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('Verified') }} {{ __('Email') }}</span>
                        </div>
                        <div class="text-lg font-medium text-white">{{ $user->email_verified_at ? 'True' : 'False' }}</div>
                    </div>

                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-indigo-500/10">
                                <i class="fas fa-check text-indigo-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('Verified') }} {{ __('Discord') }}</span>
                        </div>
                        <div class="text-lg font-medium text-white">{{ $user->discordUser ? 'True' : 'False' }}</div>
                    </div>

                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-pink-500/10">
                                <i class="fas fa-network-wired text-pink-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('IP') }}</span>
                        </div>
                        <div class="text-lg font-medium text-white">{{ $user->ip }}</div>
                    </div>

                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-orange-500/10">
                                <i class="fas fa-coins text-orange-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('Usage') }}</span>
                        </div>
                        <div class="text-lg font-medium text-white">{{ $user->CreditUsage() }}</div>
                    </div>

                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-cyan-500/10">
                                <i class="fas fa-user-friends text-cyan-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('Referred by') }}</span>
                        </div>
                        <div class="text-lg font-medium text-white">{{ $user->referredBy() != Null ? $user->referredBy()->name : "None" }}</div>
                    </div>

                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-lime-500/10">
                                <i class="fas fa-calendar-alt text-lime-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('Created at') }}</span>
                        </div>
                        <div class="text-lg font-medium text-white">{{ $user->created_at->diffForHumans() }}</div>
                    </div>

                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-gray-500/10">
                                <i class="fas fa-clock text-gray-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('Last seen') }}</span>
                        </div>
                        <div class="text-lg font-medium text-white">
                            @if ($user->last_seen)
                                {{ $user->last_seen->diffForHumans() }}
                            @else
                                <small class="text-muted">Null</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Servers -->
        <div class="card glass-morphism">
            <div class="p-6 border-b border-zinc-800/50">
                <h3 class="text-white font-medium flex items-center gap-2">
                    <i class="fas fa-server text-zinc-400"></i>
                    {{ __('Servers') }}
                </h3>
            </div>
            <div class="p-6">
                @include('admin.servers.table', ['filter' => '?user=' . $user->id])
            </div>
        </div>

        <!-- Referrals -->
        <div class="card glass-morphism">
            <div class="p-6 border-b border-zinc-800/50">
                <h3 class="text-white font-medium flex items-center gap-2">
                    <i class="fas fa-user-check text-zinc-400"></i>
                    {{ __('Referrals') }} ({{ __('referral-code') }}: {{ $user->referral_code }})
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach ($referrals as $referral)
                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg p-2 bg-blue-500/10">
                                    <i class="fas fa-user text-blue-400"></i>
                                </div>
                                <div>
                                    <div class="text-white">{{ $referral->name }}</div>
                                    <div class="text-sm text-zinc-400">ID: {{ $referral->id }}</div>
                                </div>
                            </div>
                            <div class="text-sm text-zinc-400">
                                <i class="fas fa-clock mr-1"></i>
                                {{ $referral->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
