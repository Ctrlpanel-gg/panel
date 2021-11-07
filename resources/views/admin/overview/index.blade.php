@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Admin Overview</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                                       href="{{route('admin.overview.index')}}">Admin Overview</a></li>
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
                    <a href="https://discord.gg/4Y6HjD2uyU" class="btn btn-dark btn-block px-3"><i class="fab fa-discord mr-2"></i> {{__('Support server')}}</a>
                </div>
                <div class="col-md-3">
                    <a href="https://controlpanel.gg/docs/intro" class="btn btn-dark btn-block px-3"><i class="fas fa-link mr-2"></i> {{__('Documentation')}}</a>
                </div>
                <div class="col-md-3">
                    <a href="https://github.com/ControlPanel-gg/dashboard" class="btn btn-dark btn-block px-3"><i class="fab fa-github mr-2"></i> {{__('Github')}}</a>
                </div>
                <div class="col-md-3">
                    <a href="https://controlpanel.gg/docs/Contributing/donating" class="btn btn-dark btn-block px-3"><i class="fas fa-money-bill mr-2"></i> {{__('Support ControlPanel')}}</a>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-server"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">{{__('Servers')}}</span>
                            <span class="info-box-number">{{$serverCount}}</span>
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
                            <span class="info-box-number">{{$userCount}}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-coins text-white"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">{{__('Total')}} {{CREDITS_DISPLAY_NAME}}</span>
                            <span class="info-box-number">{{$creditCount}}</span>
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
                            <span class="info-box-number">{{$paymentCount}}</span>
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
                                <button class="btn btn-primary"><i class="fas fa-sync mr-2"></i>{{__('Sync')}}</button>
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
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>{{__('Nodes')}}</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>{{__('Nests')}}</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>{{__('Eggs')}}</td>
                                    <td>1</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <span><i class="fas fa-sync mr-2"></i>{{__('Last updated :date', ['date' => now()])}}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- END CUSTOM CONTENT -->

    </section>
    <!-- END CONTENT -->
@endsection
