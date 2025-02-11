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
                                href="{{ route('admin.servers.index') }}">{{ __('Servers') }}</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <div class="card-title ">
                            <span><i class="mr-2 fas fa-server"></i>{{ __('Servers') }}</span>
                        </div>
                        <a href="{{ route('admin.servers.sync') }}" class="btn btn-primary btn-sm"><i
                                class="mr-2 fas fa-sync"></i>{{ __('Sync') }}</a>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    @include('admin.servers.table')
                </div>
            </div>
        </div>
        <!-- END CUSTOM CONTENT -->
    </section>
    <!-- END CONTENT -->
@endsection
