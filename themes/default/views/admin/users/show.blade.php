@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="max-w-screen-2xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('User Details') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li><a href="{{ route('admin.users.index') }}" class="hover:text-white transition-colors">{{ __('Users') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ $user->name }}</li>
                        </ol>
                    </nav>
                </div>
                
                <div class="relative" data-dropdown>

                    <div data-dropdown-menu class="hidden dropdown-menu">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="dropdown-item">
                            <i class="fas fa-edit mr-2"></i>{{ __('Edit User') }}
                        </a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return submitResult()">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item text-red-400">
                                <i class="fas fa-trash mr-2"></i>{{ __('Delete User') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- User Details -->
            <div class="lg:col-span-8">
                <div class="glass-panel">
                    <div class="p-6 border-b border-zinc-800/50">
                        <h5 class="text-lg font-medium text-white flex items-center">
                            <i class="fas fa-user mr-2 text-zinc-400"></i>
                            {{__('User Information')}}
                        </h5>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            @php
                            $userInfo = [
                                ['label' => __('ID'), 'value' => $user->id],
                                ['label' => __('Role'), 'value' => $user->roles->map(function($role) {
                                    return "<span class='badge' style='background-color: {$role->color}'>{$role->name}</span>";
                                })->join(' ')],
                                ['label' => __('Pterodactyl ID'), 'value' => $user->pterodactyl_id],
                                ['label' => __('Email'), 'value' => $user->email],
                                ['label' => __('Server limit'), 'value' => $user->Servers()->count() . ' / ' . $user->server_limit],
                                ['label' => __('Name'), 'value' => $user->name],
                                ['label' => __('Verified Email'), 'value' => $user->email_verified_at ? 'True' : 'False'],
                                ['label' => $credits_display_name, 'value' => '<i class="fas fa-coins mr-2"></i>' . $user->Credits()],
                                ['label' => __('Verified Discord'), 'value' => $user->discordUser ? 'True' : 'False'],
                                ['label' => __('IP'), 'value' => $user->ip],
                                ['label' => __('Usage'), 'value' => '<i class="fas fa-coins mr-2"></i>' . $user->CreditUsage()],
                                ['label' => __('Referred by'), 'value' => $user->referredBy() ? $user->referredBy()->name : 'None'],
                                ['label' => __('Created at'), 'value' => $user->created_at->diffForHumans()],
                                ['label' => __('Last seen'), 'value' => $user->last_seen ? $user->last_seen->diffForHumans() : '<small class="text-zinc-500">Never</small>'],
                            ];
                            @endphp

                            @foreach($userInfo as $info)
                            <div class="flex items-start space-x-4">
                                <div class="w-1/3 text-zinc-400 text-sm">{{ $info['label'] }}</div>
                                <div class="w-2/3 text-zinc-200">{!! $info['value'] !!}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Discord Info -->
            @if ($user->discordUser)
            <div class="lg:col-span-4">
                <div class="glass-panel">
                    <div class="p-6 border-b border-zinc-800/50">
                        <h5 class="text-lg font-medium text-white flex items-center">
                            <i class="fab fa-discord mr-2 text-zinc-400"></i>
                            {{__('Discord Account')}}
                        </h5>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center space-x-4">
                            <img src="{{ $user->discordUser->getAvatar() }}" alt="Discord Avatar" 
                                 class="w-16 h-16 rounded-full ring-2 ring-zinc-700/50">
                            <div>
                                <h3 class="text-lg font-medium text-white">{{ $user->discordUser->username }}</h3>
                                <p class="text-zinc-400">{{ $user->discordUser->id }}</p>
                                <p class="text-sm text-zinc-500">{{ $user->discordUser->locale }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Servers Section -->
        <div class="mt-6">
            <div class="glass-panel">
                <div class="p-6 border-b border-zinc-800/50">
                    <h5 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-server mr-2 text-zinc-400"></i>
                        {{__('Servers')}}
                    </h5>
                </div>
                <div class="p-6">
                    @include('admin.servers.table', ['filter' => '?user=' . $user->id])
                </div>
            </div>
        </div>

        <!-- Referrals Section -->
        <div class="mt-6">
            <div class="glass-panel">
                <div class="p-6 border-b border-zinc-800/50">
                    <h5 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-user-plus mr-2 text-zinc-400"></i>
                        {{__('Referrals')}} 
                        <span class="ml-2 px-2 py-1 text-xs rounded-full bg-zinc-800 text-zinc-400">
                            {{__('Code')}}: {{ $user->referral_code }}
                        </span>
                    </h5>
                </div>
                <div class="p-6">
                    @if(!empty($referrals))
                        <div class="grid grid-cols-1 gap-4">
                            @foreach ($referrals as $referral)
                            <div class="flex items-center justify-between p-4 rounded-lg bg-zinc-800/30">
                                <div class="flex items-center space-x-4">
                                    <div class="text-zinc-400 text-sm">ID: {{ $referral->id }}</div>
                                    <a href="{{ route('admin.users.show', $referral->id) }}" 
                                       class="text-primary-400 hover:text-primary-300 transition-colors">
                                        {{ $referral->name }}
                                    </a>
                                </div>
                                <div class="text-zinc-500 text-sm">
                                    <i class="fas fa-clock mr-2"></i>
                                    {{ $referral->created_at->diffForHumans() }}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-zinc-500">
                            <i class="fas fa-user-friends text-4xl mb-4"></i>
                            <p>{{__('No referrals yet')}}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
