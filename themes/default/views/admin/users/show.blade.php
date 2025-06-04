@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-4 sm:p-8">
    <!-- Header with Avatar -->
    <div class="w-full mb-8">
        <div class="glass-panel p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <img src="{{ $user->getAvatar() }}" alt="{{ $user->name }}'s avatar" class="w-20 h-20 rounded-xl">
                        @if($user->discordUser)
                            <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-primary-950 rounded-full flex items-center justify-center border-2 border-zinc-800">
                                <i class="fab fa-discord text-primary-400"></i>
                            </div>
                        @endif
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl sm:text-3xl font-light text-white">{{ $user->name }}</h1>
                            @if($user->email_verified_at)
                                <span class="verified-status success">
                                    <i class="fas fa-check-circle"></i>
                                    {{ __('Verified') }}
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-4 mt-2">
                            <div class="text-zinc-400 text-sm flex items-center gap-2">
                                <i class="fas fa-envelope"></i>
                                {{ $user->email }}
                            </div>
                            <div class="text-zinc-400 text-sm flex items-center gap-2">
                                <i class="fas fa-clock"></i>
                                {{ __('Joined') }} {{ $user->created_at->isoFormat('LL') }}
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            @foreach ($user->roles as $role)
                                <span style="background-color: {{$role->color}}20; color: {{$role->color}}" class="px-2.5 py-1 rounded text-xs font-medium">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
                        <i class="fas fa-pen mr-2"></i>{{ __('Edit User') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Credits Card -->
        <div class="glass-panel">
            <div class="p-4 flex items-start gap-3">
                <div class="shrink-0">
                    <div class="w-10 h-10 bg-primary-500/10 flex items-center justify-center rounded">
                        <i class="fas fa-coins text-lg text-primary-400"></i>
                    </div>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-zinc-400">{{ $credits_display_name }}</p>
                    <p class="text-xl font-semibold text-white mt-0.5 truncate">{{ $user->Credits() }}</p>
                    <p class="text-xs text-zinc-500 mt-1 truncate">{{ __('Usage') }}: {{ $user->CreditUsage() }}</p>
                </div>
            </div>
        </div>
        
        <!-- Servers Card -->
        <div class="glass-panel">
            <div class="p-4 flex items-start gap-3">
                <div class="shrink-0">
                    <div class="w-10 h-10 bg-emerald-500/10 flex items-center justify-center rounded">
                        <i class="fas fa-server text-lg text-emerald-400"></i>
                    </div>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-zinc-400">{{ __('Servers') }}</p>
                    <p class="text-xl font-semibold text-white mt-0.5 truncate">{{ $user->Servers()->count() }} / {{ $user->server_limit }}</p>
                    <p class="text-xs text-zinc-500 mt-1 truncate">{{ __('Pterodactyl ID') }}: {{ $user->pterodactyl_id }}</p>
                </div>
            </div>
        </div>
        
        <!-- Referrals Card -->
        <div class="glass-panel">
            <div class="p-4 flex items-start gap-3">
                <div class="shrink-0">
                    <div class="w-10 h-10 bg-amber-500/10 flex items-center justify-center rounded">
                        <i class="fas fa-user-friends text-lg text-amber-400"></i>
                    </div>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-zinc-400">{{ __('Referrals') }}</p>
                    <p class="text-xl font-semibold text-white mt-0.5 truncate">{{ count($referrals) }}</p>
                    <p class="text-xs text-zinc-500 mt-1 truncate">{{ __('Code') }}: {{ $user->referral_code }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Account Information -->
        <div class="glass-panel">
            <div class="p-6 border-b border-zinc-800/50">
                <h3 class="text-lg font-medium text-white flex items-center gap-2">
                    <i class="fas fa-user-shield text-zinc-400"></i>
                    {{ __('Account Information') }}
                </h3>
            </div>
            <div class="p-6">
                <dl class="space-y-4">
                    <div class="flex justify-between py-3 border-b border-zinc-800/50">
                        <dt class="text-zinc-400">{{ __('User ID') }}</dt>
                        <dd class="text-white font-medium">{{ $user->id }}</dd>
                    </div>
                    <div class="flex justify-between py-3 border-b border-zinc-800/50">
                        <dt class="text-zinc-400">{{ __('IP Address') }}</dt>
                        <dd class="text-white font-medium">{{ $user->ip }}</dd>
                    </div>
                    <div class="flex justify-between py-3 border-b border-zinc-800/50">
                        <dt class="text-zinc-400">{{ __('Last Seen') }}</dt>
                        <dd class="text-white font-medium">
                            @if($user->last_seen)
                                {{ $user->last_seen->diffForHumans() }}
                            @else
                                {{ __('Never') }}
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between py-3 border-b border-zinc-800/50">
                        <dt class="text-zinc-400">{{ __('Referred By') }}</dt>
                        <dd class="text-white font-medium">
                            @if($user->referredBy())
                                <a href="{{ route('admin.users.show', $user->referredBy()->id) }}" class="text-primary-400 hover:text-primary-300">
                                    {{ $user->referredBy()->name }}
                                </a>
                            @else
                                {{ __('None') }}
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Verification Status -->
        <div class="glass-panel">
            <div class="p-6 border-b border-zinc-800/50">
                <h3 class="text-lg font-medium text-white flex items-center gap-2">
                    <i class="fas fa-shield-alt text-zinc-400"></i>
                    {{ __('Verification Status') }}
                </h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- Email Verification -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="rounded-xl p-2 bg-emerald-500/10">
                            <i class="fas fa-envelope text-emerald-400"></i>
                        </div>
                        <div>
                            <div class="font-medium text-white">{{ __('Email Verification') }}</div>
                            <div class="text-sm text-zinc-400">{{ $user->email }}</div>
                        </div>
                    </div>
                    @if($user->email_verified_at)
                        <span class="verified-status success">
                            <i class="fas fa-check-circle"></i>
                            {{ __('Verified') }}
                        </span>
                    @else
                        <span class="verified-status danger">
                            <i class="fas fa-times-circle"></i>
                            {{ __('Unverified') }}
                        </span>
                    @endif
                </div>

                <!-- Discord Verification -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="rounded-xl p-2 bg-indigo-500/10">
                            <i class="fab fa-discord text-indigo-400"></i>
                        </div>
                        <div>
                            <div class="font-medium text-white">{{ __('Discord Integration') }}</div>
                            <div class="text-sm text-zinc-400">
                                @if($user->discordUser)
                                    {{ $user->discordUser->username }}
                                @else
                                    {{ __('Not Connected') }}
                                @endif
                            </div>
                        </div>
                    </div>
                    @if($user->discordUser)
                        <span class="verified-status success">
                            <i class="fas fa-check-circle"></i>
                            {{ __('Connected') }}
                        </span>
                    @else
                        <span class="verified-status danger">
                            <i class="fas fa-times-circle"></i>
                            {{ __('Not Connected') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Servers Section -->
    <div class="glass-panel mb-8">
        <div class="p-6 border-b border-zinc-800/50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-white flex items-center gap-2">
                    <i class="fas fa-server text-zinc-400"></i>
                    {{ __('Servers') }}
                </h3>
                <span class="text-sm text-zinc-400">{{ __('Total Servers') }}: {{ $user->Servers()->count() }}</span>
            </div>
        </div>
        <div class="p-6">
            <div class="relative overflow-x-auto">
                <div id="servers-loader" style="display: none;">
                    <div class="loader-container">
                        <div class="loader"></div>
                    </div>
                </div>
                <table id="servers-table" class="w-full text-left">
                    <thead>
                        <tr>
                            <th width="20"></th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Server id') }}</th>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Suspended at') }}</th>
                            <th>{{ __('Created at') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/10">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Referrals Section -->
    @if(count($referrals) > 0)
    <div class="glass-panel">
        <div class="p-6 border-b border-zinc-800/50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas fa-user-friends text-zinc-400"></i>
                    <h3 class="text-lg font-medium text-white">{{ __('Referrals') }}</h3>
                </div>
                <div class="flex items-center gap-4">
                    <div class="bg-zinc-800/30 rounded-lg px-4 py-2">
                        <span class="text-sm text-zinc-400">{{ __('Code') }}:</span>
                        <span class="ml-2 text-white font-mono">{{ $user->referral_code }}</span>
                    </div>
                    <div class="bg-zinc-800/30 rounded-lg px-4 py-2">
                        <span class="text-sm text-zinc-400">{{ __('Total') }}:</span>
                        <span class="ml-2 text-white">{{ count($referrals) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach ($referrals as $referral)
                <div class="glass-panel bg-zinc-800/30 hover:bg-zinc-800/40 transition-all duration-300">
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img class="w-10 h-10 rounded-lg" src="{{ $referral->getAvatar() }}" alt="{{ $referral->name }}'s avatar">
                                <div>
                                    <a href="{{ route('admin.users.show', $referral->id) }}" class="text-lg font-medium text-white hover:text-primary-400 transition-colors">
                                        {{ $referral->name }}
                                    </a>
                                    <div class="flex items-center gap-4 mt-1">
                                        <div class="flex items-center gap-1.5 text-sm text-zinc-400">
                                            <i class="fas fa-envelope"></i>
                                            {{ $referral->email }}
                                        </div>
                                        <div class="flex items-center gap-1.5 text-sm text-zinc-400">
                                            <i class="fas fa-server"></i>
                                            {{ $referral->servers()->count() }} {{ __('servers') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <div class="text-sm text-zinc-400">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $referral->created_at->diffForHumans() }}
                                </div>
                                <div class="flex items-center gap-1.5 text-sm">
                                    <i class="fas fa-coins text-amber-400"></i>
                                    <span class="text-white">{{ $referral->credits() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-zinc-700/50 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                @foreach($referral->roles as $role)
                                    <span style="background-color: {{$role->color}}20; color: {{$role->color}}" class="px-2 py-0.5 rounded text-xs">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </div>
                            <div class="flex items-center gap-2">
                                @if($referral->email_verified_at)
                                    <span class="verified-status success">
                                        <i class="fas fa-check-circle"></i>
                                        {{ __('Verified') }}
                                    </span>
                                @else
                                    <span class="verified-status danger">
                                        <i class="fas fa-times-circle"></i>
                                        {{ __('Unverified') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @else
    <div class="glass-panel">
        <div class="p-6 border-b border-zinc-800/50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas fa-user-friends text-zinc-400"></i>
                    <h3 class="text-lg font-medium text-white">{{ __('Referrals') }}</h3>
                </div>
                <div class="bg-zinc-800/30 rounded-lg px-4 py-2">
                    <span class="text-sm text-zinc-400">{{ __('Code') }}:</span>
                    <span class="ml-2 text-white font-mono">{{ $user->referral_code }}</span>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div class="text-center py-12">
                <div class="mx-auto w-16 h-16 flex items-center justify-center rounded-full bg-zinc-800/30 mb-4">
                    <i class="fas fa-user-friends text-2xl text-zinc-400"></i>
                </div>
                <h3 class="text-lg font-medium text-white/90 mb-2">{{ __('No Referrals Yet') }}</h3>
                <p class="text-zinc-400 max-w-md mx-auto">
                    {{ __('This user has not referred anyone yet.') }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const serversLoader = document.getElementById('servers-loader');
            const serversTable = $('#servers-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{ $locale_datatables }}.json',
                    paginate: {
                        first: '<i class="fas fa-angle-double-left flex items-center justify-center w-full h-full"></i>',
                        previous: '<i class="fas fa-angle-left flex items-center justify-center w-full h-full"></i>',
                        next: '<i class="fas fa-angle-right flex items-center justify-center w-full h-full"></i>',
                        last: '<i class="fas fa-angle-double-right flex items-center justify-center w-full h-full"></i>'
                    }
                },
                processing: false,
                serverSide: true,
                stateSave: true,
                ajax: {
                    url: "{{ route('admin.servers.datatable') }}?user={{ $user->id }}",
                    beforeSend: function() {
                        serversLoader.style.display = 'flex';
                    },
                    complete: function() {
                        serversLoader.style.display = 'none';
                    }
                },
                order: [[5, "desc"]],
                columns: [
                    { data: 'status', name: 'servers.suspended', sortable: false },
                    { data: 'name' },
                    { data: 'identifier' },
                    { data: 'resources', name: 'product.name', sortable: false },
                    { data: 'suspended' },
                    { data: 'created_at' },
                    { data: 'actions', sortable: false }
                ],
                dom: 'rtp',
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                pagingType: "full_numbers",
                drawCallback: function() {
                    $('.dataTables_processing').hide();
                    $('[data-toggle="popover"]').popover({
                        trigger: 'hover',
                        placement: 'top',
                        html: true,
                        template: '<div class="popover custom-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
                    });
                }
            });
        });
    </script>
</div>
@endsection
