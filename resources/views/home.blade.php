@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a class="text-muted" href="">Dashboard</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-server"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Servers</span>
                            <span class="info-box-number">{{Auth::user()->servers()->count()}}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-coins"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Credits</span>
                            <span class="info-box-number">{{Auth::user()->Credits()}}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->

                <!-- fix for small devices only -->
                <div class="clearfix hidden-md-up"></div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box mb-3">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-chart-line"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">Usage</span>
                            <span class="info-box-number">{{number_format($useage, 2, '.', '')}} <sup>p/m</sup></span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
                <!-- /.col -->

            </div>


            <div class="row">
                <div class="col-md-6">
                    <div class="card card-default">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-link mr-2"></i>
                                Useful Links
                            </h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div class="alert alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h5><a class="alert-link text-decoration-none" href="{{env('PTERODACTYL_URL' , 'http://localhost')}}"><i
                                            class="fas fa-egg mr-2"></i>Pterodactyl Panel</a></h5>
                                Use your servers on our pterodactyl panel <small>(You can use the same login details)</small>
                            </div>

                            <div class="alert alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h5><a class="alert-link text-decoration-none" href="{{env('PHPMYADMIN_URL' , 'http://localhost')}}"><i
                                            class="fas fa-database mr-2"></i>phpMyAdmin</a></h5>
                                View your database online using phpMyAdmin
                            </div>

                            <div class="alert alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h5><a class="alert-link text-decoration-none" href="{{env('DISCORD_INVITE_URL')}}"><i
                                            class="fab fa-discord mr-2"></i>Discord</a></h5>
                                Need a helping hand? want to chat? got any questions? Join our discord!
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->

                <div class="col-md-6">
                    <div class="card card-default">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history mr-2"></i>
                                Activity Log
                            </h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body py-0 pb-2">
                            <ul class="list-group list-group-flush">
                                @foreach(Auth::user()->actions()->take(8)->orderBy('created_at' , 'desc')->get() as $log)
                                    <li class="list-group-item d-flex justify-content-between text-muted">
                                        <span>
                                            @switch($log->description)
                                                @case('created')
                                                    <small><i class="fas text-success fa-plus mr-2"></i></small>
                                                @break
                                                @case('deleted')
                                                    <small><i class="fas text-danger fa-times mr-2"></i></small>
                                                @break
                                                @case('updated')
                                                    <small><i class="fas text-info fa-pen mr-2"></i></small>
                                                @break
                                            @endswitch
                                            {{ucfirst($log->description)}}
                                            {{ explode("\\" , $log->subject_type)[2]}}
                                        </span>
                                        <small>
                                            {{$log->created_at->diffForHumans()}}
                                        </small>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->

            </div>
            <!-- END CUSTOM CONTENT -->
        </div>
    </section>
    <!-- END CONTENT -->

@endsection
