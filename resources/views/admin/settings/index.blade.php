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

                            <form method="POST" enctype="multipart/form-data" class="mb-3"
                                  action="{{route('admin.settings.update.icons')}}">
                                @csrf
                                @method('PATCH')

                                <div class="row">
                                    <div class="col-md-6 col-lg-4 col-12">
                                        <div class="form-group">
                                            <div class="custom-file mb-3 mt-3">
                                                <input type="file" accept="image/png,image/jpeg,image/jpg"
                                                       class="custom-file-input" name="icon" id="icon">
                                                <label class="custom-file-label selected"
                                                       for="icon">{{__('Select panel icon')}}</label>
                                            </div>
                                            @error('icon')
                                            <span class="text-danger">
                                                   {{$message}}
                                               </span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-file mb-3">
                                                <input type="file" accept="image/x-icon" class="custom-file-input"
                                                       name="favicon" id="favicon">
                                                <label class="custom-file-label selected"
                                                       for="favicon">{{__('Select panel favicon')}}</label>
                                            </div>
                                            @error('favicon')
                                            <span class="text-danger">
                                                   {{$message}}
                                               </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <button class="btn btn-primary">Submit</button>
                            </form>

                            <p class="text-muted">Images and Icons may be cached, use <code>CNTRL + F5</code><sup>(google
                                    chrome hotkey)</sup> to reload without cache to see your changes appear :)</p>

                        </div>
                    </div>


                </div>
            </div>


        </div>
        <!-- END CUSTOM CONTENT -->

    </section>
    <!-- END CONTENT -->

    <script>
        // Add the following code if you want the name of the file appear on select
        document.addEventListener('DOMContentLoaded', ()=>{
            $(".custom-file-input").on("change", function () {
                var fileName = $(this).val().split("\\").pop();
                $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
            });
        })
    </script>


@endsection
