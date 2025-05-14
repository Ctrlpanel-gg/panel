@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1>{{ __('Servers') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                href="{{ route('servers.index') }}">{{ __('Servers') }}</a>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <!-- CUSTOM CONTENT -->
            <div class="mb-3 d-flex justify-content-md-start justify-content-center ">
                <a @if (Auth::user()->Servers->count() >= Auth::user()->server_limit) disabled="disabled" title="Server limit reached!" @endif
                   @cannot("user.server.create") disabled="disabled" title="No Permission!" @endcannot
                    href="{{ route('servers.create') }}" class="btn
                    @if (Auth::user()->Servers->count() >= Auth::user()->server_limit) disabled @endif
                    @cannot("user.server.create") disabled @endcannot
                    btn-primary">
                    <i class="mr-2 fa fa-plus"></i>
                    {{ __('Create Server') }}
                </a>
                @if (Auth::user()->Servers->count() > 0 && !empty($phpmyadmin_url))
                    <a
                        href="{{ $phpmyadmin_url }}" target="_blank"
                        class="ml-2 btn btn-secondary"><i title="manage"
                        class="mr-2 fas fa-database"></i><span>{{ __('Database') }}</span>
                    </a>
                @endif
            </div>

            <div class="flex-row row d-flex justify-content-center justify-content-md-start">
                @foreach ($servers as $server)
                 @if($server->location && $server->node && $server->nest && $server->egg)
                    <div class="pl-0 pr-0 col-xl-3 col-lg-5 col-md-6 col-sm-6 col-xs-12 card ml-sm-2 mr-sm-3"
                        style="max-width: 350px">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mt-1 card-title">{{ $server->name }}</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="container mt-1">
                                <div class="mb-3 row">
                                    <div class="my-auto col">{{ __('Status') }}:</div>
                                    <div class="my-auto col-7">
                                        @if($server->suspended)
                                            <span class="badge badge-danger">{{ __('Suspended') }}</span>
                                        @elseif($server->canceled)
                                            <span class="badge badge-warning">{{ __('Canceled') }}</span>
                                        @else
                                            <span class="badge badge-success">{{ __('Active') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <div class="col-5">
                                        {{ __('Location') }}:
                                    </div>
                                    <div class="col-7 d-flex justify-content-between align-items-center">
                                        <span class="">{{ $server->location }}</span>
                                        <i data-toggle="popover" data-trigger="hover"
                                            data-content="{{ __('Node') }}: {{ $server->node }}"
                                            class="fas fa-info-circle"></i>
                                    </div>

                                </div>
                                <div class="mb-2 row">
                                    <div class="col-5 ">
                                        {{ __('Software') }}:
                                    </div>
                                    <div class="col-7 text-wrap">
                                        <span>{{ $server->nest }}</span>
                                    </div>

                                </div>
                                <div class="mb-2 row">
                                    <div class="col-5 ">
                                        {{ __('Specification') }}:
                                    </div>
                                    <div class="col-7 text-wrap">
                                        <span>{{ $server->egg }}</span>
                                    </div>
                                </div>
                                <div class="mb-2 row">
                                    <div class="col-5 ">
                                        {{ __('Resource plan') }}:
                                    </div>
                                    <div class="col-7 text-wrap d-flex justify-content-between align-items-center">
                                        <span>{{ $server->product->name }}
                                        </span>
                                        <i data-toggle="popover" data-trigger="hover" data-html="true"
                                            data-content="{{ __('CPU') }}: {{ $server->product->cpu / 100 }} {{ __('vCores') }} <br/>{{ __('RAM') }}: {{ $server->product->memory }} MB <br/>{{ __('Disk') }}: {{ $server->product->disk }} MB <br/>{{ __('Backups') }}: {{ $server->product->backups }} <br/> {{ __('MySQL Databases') }}: {{ $server->product->databases }} <br/> {{ __('Allocations') }}: {{ $server->product->allocations }} <br/>{{ __('OOM Killer') }}: {{ $server->product->oom_killer ? __("enabled") : __("disabled") }} <br/> {{ __('Billing Period') }}: {{$server->product->billing_period}}"
                                            class="fas fa-info-circle"></i>
                                    </div>
                                </div>

                                <div class="mb-4 row ">
                                    <div class="col-5 word-break" style="hyphens: auto">
                                        {{ __('Next Billing Cycle') }}:
                                    </div>
                                    <div class="col-7 d-flex text-wrap align-items-center">
                                        <span>
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
                                </div>

                                <div class="mb-2 row">
                                    <div class="col-4">
                                        {{ __('Price') }}:
                                        <span class="text-muted">
                                            ({{ $credits_display_name }})
                                        </span>
                                    </div>
                                    <div class="text-center col-8">
                                        <div class="text-muted">
                                        @if($server->product->billing_period == 'monthly')
                                            {{ __('per Month') }}
                                        @elseif($server->product->billing_period == 'half-annually')
                                            {{ __('per 6 Months') }}
                                        @elseif($server->product->billing_period == 'quarterly')
                                            {{ __('per 3 Months') }}
                                        @elseif($server->product->billing_period == 'annually')
                                            {{ __('per Year') }}
                                        @elseif($server->product->billing_period == 'weekly')
                                            {{ __('per Week') }}
                                        @elseif($server->product->billing_period == 'daily')
                                            {{ __('per Day') }}
                                        @elseif($server->product->billing_period == 'hourly')
                                            {{ __('per Hour') }}
                                        @endif
                                            <i data-toggle="popover" data-trigger="hover"
                                               data-content="{{ __('Your') ." " . $credits_display_name . " ". __('are reduced') ." ". $server->product->billing_period . ". " . __("This however calculates to ") . Currency::formatForDisplay($server->product->getMonthlyPrice()) . " ". $credits_display_name . " ". __('per Month')}}"
                                               class="fas fa-info-circle"></i>
                                            </div>
                                        <span>
                                            {{ $server->product->display_price }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center card-footer">
                            <a href="{{ $pterodactyl_url }}/server/{{ $server->identifier }}"
                                target="__blank"
                                class="float-left ml-2 text-center btn btn-info"
                                data-toggle="tooltip" data-placement="bottom" title="{{ __('Manage Server') }}">
                                <i class="mx-2 fas fa-tools"></i>
                            </a>
                            <a href="{{ route('servers.show', ['server' => $server->id])}}"
                            	class="mr-3 text-center btn btn-info"
                            	data-toggle="tooltip" data-placement="bottom" title="{{ __('Server Settings') }}">
                                <i class="mx-2 fas fa-cog"></i>
                            </a>
                            <button onclick="handleServerCancel('{{ $server->id }}');" target="__blank"
                                class="text-center btn btn-warning"
                                {{ $server->suspended || $server->canceled ? "disabled" : "" }}
                                data-toggle="tooltip" data-placement="bottom" title="{{ __('Cancel Server') }}">
                                <i class="mx-2 fas fa-ban"></i>
                            </button>
                            <button onclick="handleServerDelete('{{ $server->id }}');" target="__blank"
                                class="float-right mr-2 text-center btn btn-danger"
                                data-toggle="tooltip" data-placement="bottom" title="{{ __('Delete Server') }}">
                                <i class="mx-2 fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                 @endif
                @endforeach
            </div>
            <!-- END CUSTOM CONTENT -->
        </div>
    </section>
    <!-- END CONTENT -->

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
