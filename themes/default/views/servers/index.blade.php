@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
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
            <div class="d-flex justify-content-md-start justify-content-center mb-3 ">
                <a @if (Auth::user()->Servers->count() >= Auth::user()->server_limit)
                    disabled="disabled" title="Server limit reached!"
                    @endif href="{{ route('servers.create') }}"
                    class="btn
                    @if (Auth::user()->Servers->count() >= Auth::user()->server_limit) disabled
                    @endif btn-primary"><i
                        class="fa fa-plus mr-2"></i>
                    {{ __('Create Server') }}
                </a>
                @if (Auth::user()->Servers->count() > 0&&!empty(config('SETTINGS::MISC:PHPMYADMIN:URL')))
                    <a 
                        href="{{ config('SETTINGS::MISC:PHPMYADMIN:URL') }}" target="_blank"
                        class="btn btn-secondary ml-2"><i title="manage"
                        class="fas fa-database mr-2"></i><span>{{ __('Database') }}</span>
                    </a>
                @endif
            </div>

            <div class="row d-flex flex-row justify-content-center justify-content-md-start">
                @foreach ($servers as $server)
                    @if($server->location&&$server->node&&$server->nest&&$server->egg)
                        <div class="col-xl-3 col-lg-5 col-md-6 col-sm-6 col-xs-12 card pr-0 pl-0 ml-sm-2 mr-sm-3"
                            style="max-width: 350px">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mt-1">{{ $server->name }}
                                    </h5>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="container mt-1">
                                    <div class="row mb-3">
                                        <div class="col my-auto">{{ __('Status') }}:</div>
                                        <div class="col-7 my-auto">
                                            <i
                                                class="fas {{ $server->isSuspended() ? 'text-danger' : 'text-success' }} fa-circle mr-2"></i>
                                            {{ $server->isSuspended() ? 'Suspended' : 'Active' }}
                                        </div>
                                    </div>
                                    <div class="row mb-2">
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
                                    <div class="row mb-2">
                                        <div class="col-5 ">
                                            {{ __('Software') }}:
                                        </div>
                                        <div class="col-7 text-wrap">
                                            <span>{{ $server->nest }}</span>
                                        </div>

                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-5 ">
                                            {{ __('Specification') }}:
                                        </div>
                                        <div class="col-7 text-wrap">
                                            <span>{{ $server->egg }}</span>
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-5 ">
                                            {{ __('Resource plan') }}:
                                        </div>
                                        <div class="col-7 text-wrap d-flex justify-content-between align-items-center">
                                            <span>{{ $server->product->name }}
                                            </span>
                                            <i data-toggle="popover" data-trigger="hover" data-html="true"
                                                data-content="{{ __('CPU') }}: {{ $server->product->cpu / 100 }} {{ __('vCores') }} <br/>{{ __('RAM') }}: {{ $server->product->memory }} MB <br/>{{ __('Disk') }}: {{ $server->product->disk }} MB <br/>{{ __('Backups') }}: {{ $server->product->backups }} <br/> {{ __('MySQL Databases') }}: {{ $server->product->databases }} <br/> {{ __('Allocations') }}: {{ $server->product->allocations }} <br/>"
                                                class="fas fa-info-circle"></i>
                                        </div>

                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-4">
                                            {{ __('Price') }}:
                                            <span class="text-muted">
                                                ({{ CREDITS_DISPLAY_NAME }})
                                            </span>
                                        </div>
                                        <div class="col-8">
                                            <div class="row">
                                                <div class="col-6  text-center">
                                                    <div class="text-muted">{{ __('per Hour') }}</div>
                                                    <span>
                                                        {{ number_format($server->product->getHourlyPrice(), 2, '.', '') }}
                                                    </span>
                                                </div>
                                                <div class="col-6  text-center">
                                                    <div class="text-muted">{{ __('per Month') }}
                                                    </div>
                                                    <span>
                                                        {{ $server->product->getHourlyPrice() * 24 * 30 }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a href="{{ config('SETTINGS::SYSTEM:PTERODACTYL:URL') }}/server/{{ $server->identifier }}"
                                    target="__blank"
                                    class="btn btn-info mx-3 w-100 align-items-center justify-content-center d-flex">
                                    <i class="fas fa-tools mr-2"></i>
                                    <span>{{ __('Manage') }}</span>
                                </a>
                                <a href="{{ route('servers.show', ['server' => $server->id])}}" class="btn btn-warning mx-3 w-100 align-items-center justify-content-center d-flex">
                                    <i class="fas fa-cog mr-2"></i>
                                    <span>{{ __('Settings') }}</span>
                                </a>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            <!-- END CUSTOM CONTENT -->
        </div>
    </section>
    <!-- END CONTENT -->
@endsection
