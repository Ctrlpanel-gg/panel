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
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#invoice-settings">Invoice Settings</a>
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

                            <div class="tab-pane mt-3" id="invoice-settings">
                            <form method="POST" enctype="multipart/form-data" class="mb-3"
                                  action="{{route('admin.settings.update.invoicesettings')}}">
                                @csrf
                                @method('PATCH')

                                <div class="row">
                                    <div class="col-md-6 col-lg-4 col-12">
                                        <!-- Name -->
                                        <div class="form-group">
                                            <div class="custom-file mb-3 mt-3">
                                                <input type="text"
                                                       class="custom-text-input" name="company-name" id="company-name">
                                                <label class="custom-text-label selected"
                                                       for="company-phone">{{__('Enter your Company Name')}}</label>
                                            </div>
                                            @error('company-name')
                                            <span class="text-danger">
                                                   {{$message}}
                                               </span>
                                            @enderror
                                        </div>
                                        <!-- adress -->
                                        <div class="form-group">
                                            <div class="custom-file mb-3 mt-3">
                                                <input type="text"
                                                       class="custom-text-input" name="company-adress" id="company-adress">
                                                <label class="custom-text-label selected"
                                                       for="company-phone">{{__('Enter your Company Adress')}}</label>
                                            </div>
                                            @error('company-adress')
                                            <span class="text-danger">
                                                   {{$message}}
                                               </span>
                                            @enderror
                                        </div>
                                        <!-- Phone -->
                                        <div class="form-group">
                                            <div class="custom-file mb-3 mt-3">
                                                <input type="text"
                                                       class="custom-text-input" name="company-phone" id="company-phone">
                                                <label class="custom-text-label selected"
                                                       for="company-phone">{{__('Enter your Company Phone Number')}}</label>
                                            </div>
                                            @error('company-phone')
                                            <span class="text-danger">
                                                   {{$message}}
                                               </span>
                                            @enderror
                                        </div>

                                        <!-- VAT -->
                                        <div class="form-group">
                                            <div class="custom-file mb-3 mt-3">
                                                <input type="text"
                                                       class="custom-text-input" name="company-vat" id="company-vat">
                                                <label class="custom-text-label selected"
                                                       for="company-phone">{{__('Enter your Company VAT')}}</label>
                                            </div>
                                            @error('company-vat')
                                            <span class="text-danger">
                                                   {{$message}}
                                               </span>
                                            @enderror
                                        </div>

                                        <!-- email -->
                                        <div class="form-group">
                                            <div class="custom-file mb-3 mt-3">
                                                <input type="text"
                                                       class="custom-text-input" name="company-mail" id="company-mail">
                                                <label class="custom-text-label selected"
                                                       for="company-phone">{{__('Enter your Company mail')}}</label>
                                            </div>
                                            @error('company-mail')
                                            <span class="text-danger">
                                                   {{$message}}
                                               </span>
                                            @enderror
                                        </div>
                                        <!-- website -->
                                        <div class="form-group">
                                            <div class="custom-file mb-3 mt-3">
                                                <input type="text"
                                                       class="custom-text-input" name="company-web" id="company-web">
                                                <label class="custom-text-label selected"
                                                       for="company-phone">{{__('Enter your Company web')}}</label>
                                            </div>
                                            @error('company-web')
                                            <span class="text-danger">
                                                   {{$message}}
                                               </span>
                                            @enderror
                                        </div>

                                        <!-- logo -->
                                        <div class="form-group">
                                            <div class="custom-file mb-3">
                                                <input type="file" accept="image/x-icon" class="custom-file-input"
                                                       name="logo" id="logo">
                                                <label class="custom-file-label selected"
                                                       for="favicon">{{__('Select Invoice Logo')}}</label>
                                            </div>
                                            @error('logo')
                                            <span class="text-danger">
                                                   {{$message}}
                                               </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                        <!-- end -->

                                    </div>
                                </div>

                                <button class="btn btn-primary">Submit</button>
                            </form>


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
