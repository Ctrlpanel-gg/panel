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
    @if (!file_exists(base_path() . '/install.lock'))
        <div class="callout callout-danger">
            <h4>{{ __('The installer is not locked!') }}</h4>
            <p>{{ __('please create a file called "install.lock" in your dashboard Root directory. Otherwise no settings will be loaded!') }}
            </p>
            <a href="/install?step=7"><button class="btn btn-outline-danger">{{ __('or click here') }}</button></a>

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
                    <!-- Sidebar Menu -->
                    <div class="d-flex w-100">
                        <nav class="mt-1">
                            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="tablist"
                                data-accordion="false">
                                @foreach ($settings as $category => $options)
                                    <li class="nav-item border-bottom-0">
                                        <a href="#{{ $category }}" class="nav-link {{ $loop->first ? 'active' : '' }}"
                                            data-toggle="pill" role="tab">
                                            <i class="nav-icon fas fa-cog"></i>
                                            <p>
                                                {{ $category }}
                                            </p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </nav>
                        <!-- /.sidebar-menu -->
                        <!-- Content in $settings -->
                        <div class="tab-content ml-3" style="width: 100%;">
                            @foreach ($settings as $category => $options)
                                <div container class="tab-pane fade container {{ $loop->first ? 'active show' : '' }}"
                                    id="{{ $category }}" role="tabpanel">
                                    @foreach ($options as $key => $value)
                                        <div class="row">
                                            <div class="col">
                                                {{ $value['label'] }}
                                            </div>
                                            <div class="col">
                                                @if (gettype($value['value']) == 'string')
                                                    <input type="text" class="form-control" name="{{ $key }}"
                                                        value="{{ $value['value'] }}">
                                                @elseif (gettype($value['value']) == 'boolean')
                                                    <input type="checkbox" class="form-control" name="{{ $key }}"
                                                        value="{{ $value['value'] }}">
                                                @elseif (gettype($value['value']) == 'integer' || gettype($value['value']) == 'double')
                                                    <input type="number" class="form-control" name="{{ $key }}"
                                                        value="{{ $value['value'] }}">
                                                @elseif (gettype($value['value']) == 'array')
                                                    <select class="form-control" name="{{ $key }}">
                                                        @foreach ($value['value'] as $option)
                                                            <option value="{{ $option }}">{{ $option }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- END CUSTOM CONTENT -->

    </section>
    <!-- END CONTENT -->

    <script>
        const tabPaneHash = window.location.hash;
        if (tabPaneHash) {
            $('.nav-item a[href="' + tabPaneHash + '"]').tab('show');
        }

        $('.nav-pills a').click(function(e) {
            $(this).tab('show');
            const scrollmem = $('body').scrollTop();
            window.location.hash = this.hash;
            $('html,body').scrollTop(scrollmem);
        });
    </script>
@endsection
