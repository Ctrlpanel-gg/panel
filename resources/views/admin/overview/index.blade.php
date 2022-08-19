@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('Admin Overview')}}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">{{__('Dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                                       href="{{route('admin.overview.index')}}">{{__('Admin Overview')}}</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <div class="row mb-3">
                <div class="col-md-3">
                    <a href="https://discord.gg/4Y6HjD2uyU" class="btn btn-dark btn-block px-3"><i
                            class="fab fa-discord mr-2"></i> {{__('Support server')}}</a>
                </div>
                <div class="col-md-3">
                    <a href="https://controlpanel.gg/docs/intro" class="btn btn-dark btn-block px-3"><i
                            class="fas fa-link mr-2"></i> {{__('Documentation')}}</a>
                </div>
                <div class="col-md-3">
                    <a href="https://github.com/ControlPanel-gg/dashboard" class="btn btn-dark btn-block px-3"><i
                            class="fab fa-github mr-2"></i> {{__('Github')}}</a>
                </div>
                <div class="col-md-3">
                    <a href="https://controlpanel.gg/docs/Contributing/donating" class="btn btn-dark btn-block px-3"><i
                            class="fas fa-money-bill mr-2"></i> {{__('Support ControlPanel')}}</a>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-server"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">{{__('Servers')}}</span>
                            <span class="info-box-number">{{$counters['servers']->total}}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-users"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">{{__('Users')}}</span>
                            <span class="info-box-number">{{$counters['users']}}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning elevation-1"><i
                                class="fas fa-coins text-white"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">{{__('Total')}} {{CREDITS_DISPLAY_NAME}}</span>
                            <span class="info-box-number">{{$counters['credits']}}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-money-bill"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">{{__('Payments')}}</span>
                            <span class="info-box-number">{{$counters['payments']}}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <div class="card-title ">
                                    <span><i class="fas fa-kiwi-bird mr-2"></i>{{__('Pterodactyl')}}</span>
                                </div>
                                <a href="{{route('admin.overview.sync')}}" class="btn btn-primary btn-sm"><i
                                        class="fas fa-sync mr-2"></i>{{__('Sync')}}</a>
                            </div>
                        </div>
                        <div class="card-body py-1">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>{{__('Resources')}}</th>
                                    <th>{{__('Count')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>{{__('Locations')}}</td>
                                    <td>{{$counters['locations']}}</td>
                                </tr>
                                <tr>
                                    <td>{{__('Nodes')}}</td>
                                    <td>{{$nodes->count()}}</td>
                                </tr>
                                <tr>
                                    <td>{{__('Nests')}}</td>
                                    <td>{{$counters['nests']}}</td>
                                </tr>
                                <tr>
                                    <td>{{__('Eggs')}}</td>
                                    <td>{{$counters['eggs']}}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <span><i class="fas fa-sync mr-2"></i>{{__('Last updated :date', ['date' => $syncLastUpdate])}}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <div class="card-title ">
                                    <span><i class="fas fa-file-invoice-dollar mr-2"></i>{{__('Individual nodes')}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body py-1">
                            @if ($perPageLimit)
                                <div class="alert alert-danger m-2">
                                    <h5><i class="icon fas fa-exclamation-circle"></i>{{ __('Error!') }}</h5>
                                    <p class="">
                                        {{ __('You reached the Pterodactyl perPage limit. Please make sure to set it higher than your server count.') }}<br>
                                        {{ __('You can do that in settings.') }}
                                    </p>
                                </div>
                            @endif
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>{{__('ID')}}</th>
                                    <th>{{__('Node')}}</th>
                                    <th>{{__('Server count')}}</th>
                                    <th>{{__('Resource usage')}}</th>
                                    <th>{{CREDITS_DISPLAY_NAME . ' ' . __('Usage')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($nodes as $nodeID => $node)
                                        <tr>
                                            <td>{{$nodeID}}</td>
                                            <td>{{$node->name}}</td>
                                            <td>{{$node->activeServers}}/{{$node->totalServers}}</td>
                                            <td>{{$node->usagePercent}}%</td>
                                            <td>{{$node->activeEarnings}}/{{$node->totalEarnings}}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2"><span style="float: right; font-weight: 700">{{__('Total')}} ({{__('active')}}/{{__('total')}}):</span></td>
                                        <td>{{$counters['servers']->active}}/{{$counters['servers']->total}}</td>
                                        <td>{{$counters['totalUsagePercent']}}%</td>
                                        <td>{{$counters['earnings']->active}}/{{$counters['earnings']->total}}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- END CUSTOM CONTENT -->

    </section>
    <!-- END CONTENT -->
@endsection
