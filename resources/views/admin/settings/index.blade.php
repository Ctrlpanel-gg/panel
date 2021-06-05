@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Settings</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                                       href="{{route('admin.settings.index')}}">Settings</a></li>
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
                        <h5 class="card-title"><i class="fas fa-tools mr-2"></i>Settings</h5>
                    </div>
                </div>

                <div class="card-body ">

                    <!-- Nav pills -->
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="pill" href="#dashboard-icons">Dashboard icons</a>
                        </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div class="tab-pane mt-3 active" id="dashboard-icons">

                            <form method="POST" class="mb-3" action="{{route('admin.settings.update.icons')}}">
                                @csrf
                                @method('PATCH')

                                <div class="d-flex">
                                    <div class="form-group">
                                        <div class="text-center mb-2">Dashboard Icon</div>
                                        <div class="avatar">
                                            <div class="slim rounded-circle"
                                                 data-size="128,128"
                                                 data-ratio="1:1"
                                                 style="width: 140px;height:140px; cursor: pointer"
                                                 data-save-initial-image="true">
                                                <input type="file" name="icon"/>
                                                <img
                                                    src="{{\Illuminate\Support\Facades\Storage::disk('public')->exists('icon.png') ? asset('storage/icon.png') : asset('images/bitsec.png')}}"
                                                    alt="icon">
                                            </div>
                                        </div>
                                    </div>


                                    <div class="form-group ml-5">
                                        <div class="text-center mb-2">Favicon</div>
                                        <div class="avatar">
                                            <div class="slim rounded-circle"
                                                 data-size="64,64"
                                                 data-ratio="1:1"
                                                 style="width: 140px;height:140px; cursor: pointer"
                                                 data-save-initial-image="true">
                                                <input accept="image/x-icon" type="file" name="favicon"/>
                                                <img
                                                    src="{{\Illuminate\Support\Facades\Storage::disk('public')->exists('favicon.ico') ? asset('storage/favicon.ico') : asset('favicon.ico')}}"
                                                    alt="favicon">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button class="btn btn-primary">Submit</button>
                            </form>

                            <p class="text-muted">Images and Icons may be cached, use <code>CNTRL + F5</code><sup>(google chrome hotkey)</sup> to reload without cache to see your changes appear :)</p>

                        </div>
                    </div>


                </div>
            </div>


        </div>
        <!-- END CUSTOM CONTENT -->

    </section>
    <!-- END CONTENT -->




@endsection
