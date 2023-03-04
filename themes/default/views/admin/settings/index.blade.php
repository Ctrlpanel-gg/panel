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
                        <div class="col-2 p-0">
                            <nav class="mt-1">
                                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="tablist"
                                    data-accordion="false">
                                    @foreach ($settings as $category => $options)
                                        <li class="nav-item border-bottom-0">
                                            <a href="#{{ $category }}"
                                                class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="pill"
                                                role="tab">
                                                <i
                                                    class="nav-icon fas {{ $options['category_icon'] ?? 'fas fa-cog' }}"></i>
                                                <p>
                                                    {{ $category }}
                                                </p>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </nav>
                        </div>
                        <!-- /.sidebar-menu -->
                        <!-- Content in $settings -->
                        <div class="col-10 p-0">
                            <div class="tab-content ml-3" style="width: 100%;">
                                @foreach ($settings as $category => $options)
                                    <div container class="tab-pane fade container {{ $loop->first ? 'active show' : '' }}"
                                        id="{{ $category }}" role="tabpanel">

                                        <form action="{{ route('admin.settings.update') }}" method="POST">
                                            @csrf
                                            @method('POST')
                                            <input type="hidden" name="settings_class"
                                                value="{{ $options['settings_class'] }}">
                                            <input type="hidden" name="category" value="{{ $category }}">

                                            @foreach ($options as $key => $value)
                                                @if ($key == 'category_icon' || $key == 'settings_class')
                                                    @continue
                                                @endif
                                                <div class="row">
                                                    <div class="col-4 d-flex align-items-center">
                                                        <label for="{{ $key }}">{{ $value['label'] }}</label>
                                                    </div>

                                                    <div class="col-8">
                                                        <div class="custom-control mb-3 d-flex align-items-center">
                                                            @if ($value['description'])
                                                                <i class="fas fa-info-circle mr-4" data-toggle="popover"
                                                                    data-trigger="hover" data-placement="top"
                                                                    data-html="true"
                                                                    data-content="{{ $value['description'] }}"></i>
                                                            @else
                                                                <i class="fas fa-info-circle mr-4 invisible"></i>
                                                            @endif

                                                            <div class="w-100">
                                                                @switch($value)
                                                                    @case($value['type'] == 'string')
                                                                        <input type="text" class="form-control"
                                                                            name="{{ $key }}"
                                                                            value="{{ $value['value'] }}">
                                                                    @break

                                                                    @case($value['type'] == 'boolean')
                                                                        <input type="checkbox" name="{{ $key }}"
                                                                            value="{{ $value['value'] }}"
                                                                            {{ $value['value'] ? 'checked' : '' }}>
                                                                    @break

                                                                    @case($value['type'] == 'number')
                                                                        <input type="number" class="form-control"
                                                                            name="{{ $key }}"
                                                                            value="{{ $value['value'] }}">
                                                                    @break

                                                                    @case($value['type'] == 'select')
                                                                        <select id="{{ $key }}"
                                                                            class="custom-select w-100" name="{{ $key }}">
                                                                            @foreach ($value['options'] as $option)
                                                                                <option value="{{ $option }}"
                                                                                    {{ $value['value'] == $option ? 'selected' : '' }}>
                                                                                    {{ __($option) }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    @break

                                                                    @case($value['type'] == 'multiselect')
                                                                        <select id="{{ $key }}"
                                                                            class="custom-select w-100" name="{{ $key }}"
                                                                            multiple>
                                                                            @foreach ($value['options'] as $option)
                                                                                <option value="{{ $option }}"
                                                                                    {{ $value['value'] == $option ? 'selected' : '' }}>
                                                                                    {{ __($option) }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    @break

                                                                    @case($value['type'] == 'textarea')
                                                                        <textarea class="form-control" name="{{ $key }}" rows="3">{{ $value['value'] }}</textarea>
                                                                    @break

                                                                    @default
                                                                @endswitch
                                                                @error($key)
                                                                    <div class="text-danger ">
                                                                        {{ $message }}
                                                                    </div>
                                                                @enderror
                                                            </div>


                                                        </div>

                                                    </div>
                                                </div>
                                            @endforeach
                                            <div class="row">
                                                <div class="col-12 d-flex align-items-center justify-content-end">
                                                    <button type="submit"
                                                        class="btn btn-primary float-right ">Save</button>
                                                    <button type="reset"
                                                        class="btn btn-secondary float-right ml-2">Reset</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                @endforeach

                            </div>
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

        document.addEventListener('DOMContentLoaded', (event) => {
            $('.custom-select').select2();
        })

        tinymce.init({
            selector: 'textarea',
            promotion: false,
            skin: "oxide-dark",
            content_css: "dark",
            branding: false,
            height: 500,
            width: '100%',
            plugins: ['image', 'link'],
        });
    </script>
@endsection
