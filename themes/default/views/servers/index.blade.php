@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <header class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <h1 class="text-3xl font-light text-white">{{ __('Servers') }}</h1>
            <div class="flex items-center gap-4 mt-4">
                <a href="{{ route('servers.create') }}"
                   class="btn btn-primary"
                   @if (Auth::user()->Servers->count() >= Auth::user()->server_limit || !Auth::user()->can("user.server.create"))
                   disabled
                   @endif>
                    <i class="fas fa-plus mr-2"></i>
                    {{ __('Create Server') }}
                </a>

                @if (Auth::user()->Servers->count() > 0 && !empty($phpmyadmin_url))
                    <a href="{{ $phpmyadmin_url }}" target="_blank"
                       class="btn bg-zinc-800/50 text-zinc-300 hover:bg-zinc-800">
                        <i class="fas fa-database mr-2"></i>
                        {{ __('Database') }}
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Servers Grid -->
    <div class="max-w-screen-xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-6">
            @foreach ($servers as $server)
                @if($server->location && $server->node && $server->nest && $server->egg)
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-lg font-medium text-white">{{ $server->name }}</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Status -->
                            <div class="flex justify-between items-center">
                                <span class="text-zinc-400">{{ __('Status') }}</span>
                                <div>
                                    @if($server->suspended)
                                        <span class="px-2 py-1 text-xs font-medium bg-red-500/10 text-red-400 rounded-full">
                                            {{ __('Suspended') }}
                                        </span>
                                    @elseif($server->canceled)
                                        <span class="px-2 py-1 text-xs font-medium bg-amber-500/10 text-amber-400 rounded-full">
                                            {{ __('Canceled') }}
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium bg-emerald-500/10 text-emerald-400 rounded-full">
                                            {{ __('Active') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Server Info -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-400">{{ __('Location') }}</span>
                                    <span class="text-white">{{ $server->location }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-400">{{ __('Software') }}</span>
                                    <span class="text-white">{{ $server->nest }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-400">{{ __('Specification') }}</span>
                                    <span class="text-white">{{ $server->egg }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-400">{{ __('Resource plan') }}</span>
                                    <span class="text-white">{{ $server->product->name }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-400">{{ __('Next Billing Cycle') }}</span>
                                    <span class="text-white">
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
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-zinc-400">{{ __('Price') }}</span>
                                    <span class="text-white">
                                        {{ $server->product->price == round($server->product->price) ? round($server->product->price) : $server->product->price }}
                                    </span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <a href="{{ $pterodactyl_url }}/server/{{ $server->identifier }}"
                                   target="_blank"
                                   class="btn flex-1 bg-zinc-800/50 text-zinc-300 hover:bg-zinc-800">
                                    <i class="fas fa-tools"></i>
                                </a>
                                
                                <a href="{{ route('servers.show', ['server' => $server->id])}}"
                                   class="btn flex-1 bg-zinc-800/50 text-zinc-300 hover:bg-zinc-800">
                                    <i class="fas fa-cog"></i>
                                </a>

                                <button onclick="handleServerCancel('{{ $server->id }}');"
                                        class="btn flex-1 bg-amber-500/10 text-amber-400 hover:bg-amber-500/20"
                                        {{ $server->suspended || $server->canceled ? "disabled" : "" }}>
                                    <i class="fas fa-ban"></i>
                                </button>

                                <button onclick="handleServerDelete('{{ $server->id }}');"
                                        class="btn flex-1 bg-red-500/10 text-red-400 hover:bg-red-500/20">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>

<!-- Keep existing JavaScript -->
<script>
    const handleServerCancel = (serverId) => {
        // Handle server cancel with sweetalert
        Swal.fire({
            title: "{{ __('Cancel Server?') }}",
            text: "{{ __('This will cancel your current server to the next billing period. It will get suspended when the current period runs out.') }}",
            icon: 'warning',
            confirmButtonColor: '#d9534f',
            showCancelButton: true,
            confirmButtonText: "{{ __('Yes, cancel it!') }}",
            cancelButtonText: "{{ __('No, abort!') }}",
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                // Delete server
                fetch("{{ route('servers.cancel', '') }}" + '/' + serverId, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(() => {
                    window.location.reload();
                }).catch((error) => {
                    Swal.fire({
                        title: "{{ __('Error') }}",
                        text: "{{ __('Something went wrong, please try again later.') }}",
                        icon: 'error',
                        confirmButtonColor: '#d9534f',
                    })
                })
                return
            }
        })
    }

    const handleServerDelete = (serverId) => {
        Swal.fire({
            title: "{{ __('Delete Server?') }}",
            html: "{!! __('This is an irreversible action, all files of this server will be removed. <strong>No funds will get refunded</strong>. We recommend deleting the server when server is suspended.') !!}",
            icon: 'warning',
            confirmButtonColor: '#d9534f',
            showCancelButton: true,
            confirmButtonText: "{{ __('Yes, delete it!') }}",
            cancelButtonText: "{{ __('No, abort!') }}",
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                // Delete server
                fetch("{{ route('servers.destroy', '') }}" + '/' + serverId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(() => {
                    window.location.reload();
                }).catch((error) => {
                    Swal.fire({
                        title: "{{ __('Error') }}",
                        text: "{{ __('Something went wrong, please try again later.') }}",
                        icon: 'error',
                        confirmButtonColor: '#d9534f',
                    })
                })
                return
            }
        });

    }

    document.addEventListener('DOMContentLoaded', () => {
        $('[data-toggle="popover"]').popover();
    });

    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
@endsection
