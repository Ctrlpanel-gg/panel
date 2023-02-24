@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Settings') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                href="{{ route('admin.settings.index') }}">{{ __('Settings') }}</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->
    @if(!file_exists(base_path()."/install.lock"))
        <div class="callout callout-danger">
            <h4>{{ __('The installer is not locked!') }}</h4>
            <p>{{ __('please create a file called "install.lock" in your dashboard Root directory. Otherwise no settings will be loaded!') }}</p>
            <a href="/install?step=7"><button class="btn btn-outline-danger">{{__('or click here')}}</button></a>

        </div>
    @endif
    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h5 class="card-title"><i class="fas fa-tools mr-2"></i>{{ __('Settings') }}</h5>
                    </div>
                </div>
                <div class="card-body">
                    <aside class="d-flex" style="">
                        <div class="sidebar d-flex flex-column" style="height: fit-content">
                            <nav class="container">
                                <ul class="row nav nav-pills nav-sidebar flex-column" style="width: fit-content" data-widget="treeview" role="menu" data-accordion="false">
                                    <li class="nav-item col">
                                        <a href="#general"
                                            class="nav-link @if (Request::routeIs('home')) active @endif">
                                            <p>{{ __('General') }}</p>
                                        </a>
                                    </li>
                                    <li class="nav-item col">
                                        <a href="#system"
                                            class="nav-link @if (Request::routeIs('home')) active @endif">
                                            <p>{{ __('System') }}</p>
                                        </a>
                                    </li>
                                    <li class="nav-item col">
                                        <a href="#mail"
                                            class="nav-link @if (Request::routeIs('home')) active @endif">
                                            <p>{{ __('Mail') }}</p>
                                        </a>
                                    </li>
                                    <li class="nav-item col">
                                        <a href="#discord"
                                            class="nav-link @if (Request::routeIs('home')) active @endif">
                                            <p>{{ __('Discord') }}</p>
                                        </a>
                                    </li>
                                    <li class="nav-item col">
                                        <a href="#invoices"
                                            class="nav-link @if (Request::routeIs('home')) active @endif">
                                            <p>{{ __('Invoices') }}</p>
                                        </a>
                                    </li>
                                    <li class="nav-item col">
                                        <a href="#locales"
                                            class="nav-link @if (Request::routeIs('home')) active @endif">
                                            <p>{{ __('Locales') }}</p>
                                        </a>
                                    </li>
                                    <li class="nav-item col">
                                        <a href="#pterodactyl"
                                            class="nav-link @if (Request::routeIs('home')) active @endif">
                                            <p>{{ __('Pterodactyl') }}</p>
                                        </a>
                                    </li>
                                    <li class="nav-item col">
                                        <a href="#referral"
                                            class="nav-link @if (Request::routeIs('home')) active @endif">
                                            <p>{{ __('Referral') }}</p>
                                        </a>
                                    </li>
                                    <li class="nav-item col">
                                        <a href="#servers"
                                            class="nav-link @if (Request::routeIs('home')) active @endif">
                                            <p>{{ __('Servers') }}</p>
                                        </a>
                                    </li>
                                    <li class="nav-item col">
                                        <a href="#tickets"
                                            class="nav-link @if (Request::routeIs('home')) active @endif">
                                            <p>{{ __('Tickets') }}</p>
                                        </a>
                                    </li>
                                    <li class="nav-item col">
                                        <a href="#users"
                                            class="nav-link @if (Request::routeIs('home')) active @endif">
                                            <p>{{ __('Users') }}</p>
                                        </a>
                                    </li>
                                    <li class="nav-item col">
                                        <a href="#website"
                                            class="nav-link @if (Request::routeIs('home')) active @endif">
                                            <p>{{ __('Website') }}</p>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <div class="d-flex justify-content-around w-100">
                            <div class="d-flex" style="height: fit-content;">
                                Ptero API
                            </div>
                            <div class="d-flex" style="height: fit-content;">
                                <input type="text" name="text" id="#general">
                            </div>
                        </div>
                    </aside>
                </div>
            </div>

        </div>
        </div>


        <!-- END CUSTOM CONTENT -->

    </section>
    <!-- END CONTENT -->

    <script>
        // Add the following code if you want the name of the file appear on select

        document.addEventListener('DOMContentLoaded', () => {
            $(".custom-file-input").on("change", function() {
                var fileName = $(this).val().split("\\").pop();
                $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
            });
        })

        const tabPaneHash = window.location.hash;
        if (tabPaneHash) {
            $('.nav-tabs a[href="' + tabPaneHash + '"]').tab('show');
        }

        $('.nav-tabs a').click(function(e) {
            $(this).tab('show');
            const scrollmem = $('body').scrollTop();
            window.location.hash = this.hash;
            $('html,body').scrollTop(scrollmem);
        });
    </script>


@endsection
