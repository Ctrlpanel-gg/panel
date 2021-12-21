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
            <div class="d-flex justify-content-end mb-3">
                <a @if (Auth::user()->Servers->count() >= Auth::user()->server_limit) disabled="disabled" title="Server limit reached!" @endif href="{{ route('servers.create') }}" class="btn @if (Auth::user()->Servers->count() >= Auth::user()->server_limit) disabled @endif btn-primary"><i class="fa fa-plus mr-2"></i>Create
                    Server</a>
            </div>

            <div class="row ml-1">
                @foreach ($servers as $server)

                    <div class="col-xl-3 col-lg-5 col-md-6 col-sm-6 col-xs-12 card pr-0 pl-0">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mt-1">{{ $server->name }}
                                </h5>
                                <div class="card-tools mt-1">
                                    <div class="dropdown no-arrow">
                                        <a href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-white-50"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            @if (!empty(env('PHPMYADMIN_URL')))
                                                <a href="{{ env('PHPMYADMIN_URL', 'http://localhost') }}"
                                                    class="dropdown-item text-info" target="__blank"><i title="manage"
                                                        class="fas fa-database mr-2"></i><span>Database</span></a>
                                            @endif
                                            <div class="dropdown-divider"></div>
                                            <span class="dropdown-item"><i title="Created at"
                                                    class="fas fa-sync-alt mr-2"></i><span>{{ $server->created_at->isoFormat('LL') }}</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="container mt-1">
                                <div class="row mb-3">
                                    <div class="col my-auto">Status:</div>
                                    <div class="col-8 my-auto">
                                        <i
                                            class="fas {{ $server->isSuspended() ? 'text-danger' : 'text-success' }} fa-circle mr-2"></i>
                                        {{ $server->isSuspended() ? 'Suspended' : 'Active' }}
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col my-auto">
                                        Location:
                                    </div>
                                    <div class="col-8">
                                        <div class="">{{ $server->location }}</div>
                                    </div>

                                </div>
                                <div class="row mb-2">
                                    <div class="col my-auto">
                                        Software:
                                    </div>
                                    <div class="col-8">
                                        <div class="">{{ $server->nest }}</div>
                                    </div>

                                </div>
                                <div class="row mb-2">
                                    <div class="col my-auto">
                                        Specification:
                                    </div>
                                    <div class="col-8">
                                        <div class="">{{ $server->egg }}</div>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col my-auto">
                                        Resourceplan:
                                    </div>
                                    <div class="col-8">
                                        <div class="">{{ $server->resourceplanName }}</div>
                                    </div>

                                </div>
                                <div class="row mb-2">
                                    <div class="col my-auto">
                                        Price:
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted">per Hour</div>
                                        <div>
                                            {{ number_format($server->product->getHourlyPrice(), 2, '.', '') }}
                                            {{ CREDITS_DISPLAY_NAME }}
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-muted">per Month
                                        </div>
                                        <div> {{ $server->product->getHourlyPrice() * 24 * 30 }}
                                            {{ CREDITS_DISPLAY_NAME }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="card-footer">
                            <div class="footer btn-group d-flex justify-content-around">
                                <form
                                    action="{{ env('PTERODACTYL_URL', 'http://localhost') }}/server/{{ $server->identifier }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-info w-100">
                                        <i title="delete" class="fas fa-tools mr-2"></i>
                                        Manage
                                    </button>
                                </form>
                                <form method="post" onsubmit="return submitResult();"
                                    action="{{ route('servers.destroy', $server->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger w-100">
                                        <i title="delete" class="fas fa-trash mr-2"></i>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>

                @endforeach
            </div>
            <!-- END CUSTOM CONTENT -->


        </div>
    </section>
    <!-- END CONTENT -->

    <script>
        function submitResult() {
            return confirm("{{ __('Are you sure you wish to delete?') }}") !== false;
        }
    </script>
@endsection
