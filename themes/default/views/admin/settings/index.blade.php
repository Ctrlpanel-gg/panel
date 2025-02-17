@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1>{{ __('Settings') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                href="{{ route('admin.settings.index') }}">{{ __('Settings') }}</a>
                        </li>
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
            <a href="/install?step=7">
                <button class="btn btn-outline-danger">{{ __('or click here') }}</button>
            </a>

        </div>
    @endif
    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h5 class="card-title"><i class="mr-2 fas fa-tools"></i>{{ __('Settings') }}</h5>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Sidebar Menu -->
                    <div class="row d-flex">
                        <div class="p-0 col-md-2 col-12">
                            <nav class="mt-1">
                                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="tablist"
                                    data-accordion="false">
                                  @can("admin.icons.edit")
                                    <li class="nav-item border-bottom-0">
                                        <a href="#icons" class="nav-link" data-toggle="pill" role="tab">
                                            <i class="nav-icon fas fa-image"></i>
                                            <p>
                                                {{ __('Images / Icons') }}
                                            </p>
                                        </a>
                                    </li>
                                  @endcan
                                    @foreach ($settings as $category => $options)
                                        @if (!str_contains($options['settings_class'], 'Extension'))
                                            @canany(['settings.' . strtolower($category) . '.read', 'settings.' .
                                                strtolower($category) . '.write'])
                                                <li class="nav-item border-bottom-0">
                                                    <a href="#{{ $category }}"
                                                        class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="pill"
                                                        role="tab">
                                                        <i
                                                            class="nav-icon {{ $options['category_icon'] ?? 'fas fa-cog' }}"></i>
                                                        <p>
                                                            {{ $category }}
                                                        </p>
                                                    </a>
                                                </li>
                                            @endcanany
                                        @endif
                                    @endforeach
                                </ul>


                                <button class="btn btn-outline-secondary mb-2 mb-md-0" type="button" data-toggle="collapse"
                                    data-target="#collapseExtensions" aria-expanded="false"
                                    aria-controls="collapseExtensions">
                                    {{ __('Extension Settings') }}
                                </button>


                                <div class="collapse" id="collapseExtensions">
                                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="tablist"
                                        data-accordion="false">
                                        @foreach ($settings as $category => $options)
                                            @if (str_contains($options['settings_class'], 'Extension'))
                                                @canany(['settings.' . strtolower($category) . '.read', 'settings.' .
                                                    strtolower($category) . '.write'])
                                                    <li class="nav-item border-bottom-0">
                                                        <a href="#{{ $category }}" class="nav-link" data-toggle="pill"
                                                            role="tab">
                                                            <i
                                                                class="nav-icon fas {{ $options['category_icon'] ?? 'fas fa-cog' }}"></i>
                                                            <p>
                                                                {{ $category }}
                                                            </p>
                                                        </a>
                                                    </li>
                                                @endcanany
                                            @endif
                                        @endforeach
                                </div>
                            </nav>
                        </div>
                        <!-- /.sidebar-menu -->
                        <!-- Content in $settings -->
                        <div class="p-0 col-md-10 col-12">
                            <div class="tab-content">
                                <div class="container tab-pane fade" id="icons" role="tabpanel">

                                    <form method="POST" enctype="multipart/form-data" class="mb-3"
                                        action="{{ route('admin.settings.updateIcons') }}">
                                        @csrf
                                        @method('POST')
                                        <div class="row">
                                            <div class="ml-0 ml-md-5">
                                              @error('favicon')
                                                <p class="text-danger">
                                                    {{ $message }}
                                                </p>
                                              @enderror
                                              <div class="card" style="width: 18rem;">
                                                  <span class="text-center h3">{{ __('FavIcon') }} </span>
                                                <img src="{{ $images['favicon'] }}"
                                                     style="width:5vw;display: block; margin-left: auto;margin-right: auto"
                                                     class="card-img-top" alt="...">
                                                  <div class="card-body">

                                                  </div>
                                                  <input type="file" accept="image/x-icon" class="form-control"
                                                      name="favicon" id="favicon">
                                              </div>
                                            </div>

                                            <div class="ml-0 ml-md-5">
                                              @error('icon')
                                                <p class="text-danger">
                                                    {{ $message }}
                                                </p>
                                              @enderror
                                              <div class="card" style="width: 18rem;">
                                                  <span class="text-center h3">{{ __('Icon') }} </span>
                                                  <img src="{{ $images['icon'] }}"
                                                      style="width:5vw;display: block; margin-left: auto;margin-right: auto"
                                                      class="card-img-top" alt="...">
                                                  <div class="card-body">

                                                  </div>
                                                  <input type="file" accept="image/png,image/jpeg,image/jpg"
                                                      class="form-control" name="icon" id="icon">
                                              </div>
                                            </div>

                                            <div class="ml-0 ml-md-5">
                                              @error('logo')
                                                <p class="text-danger">
                                                    {{ $message }}
                                                </p>
                                              @enderror
                                              <div class="card" style="width: 18rem;">
                                                  <span class="text-center h3">{{ __('Login-page Logo') }} </span>
                                                  <img src="{{ $images['logo'] }}"
                                                      style="width:5vw;display: block; margin-left: auto;margin-right: auto"
                                                      class="card-img-top" alt="...">
                                                  <div class="card-body">

                                                  </div>
                                                  <input type="file" accept="image/png,image/jpeg,image/jpg"
                                                      class="form-control" name="logo" id="logo">
                                              </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 d-flex justify-content-end">
                                                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                                            </div>
                                        </div>

                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    </form>
                                </div>
                                @foreach ($settings as $category => $options)
                                    @canany(['settings.' . strtolower($category) . '.read', 'settings.' .
                                        strtolower($category) . '.write'])
                                        <div class="tab-pane fade container {{ $loop->first ? 'active show' : '' }}"
                                            id="{{ $category }}" role="tabpanel">

                                            <form action="{{ route('admin.settings.update') }}" method="POST">
                                                @csrf
                                                @method('POST')
                                                <input type="hidden" name="settings_class"
                                                    value="{{ $options['settings_class'] }}">
                                                <input type="hidden" name="category" value="{{ $category }}">
                                                @foreach ($options as $key => $value)
                                                    @if ($key == 'category_icon' || $key == 'settings_class' || $key == 'position')
                                                        @continue
                                                    @endif
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 col-12 d-flex align-items-center">
                                                          <label class="w-100 d-inline-flex justify-content-between align-items-center" for="{{ $key }}">
                                                            {{ $value['label'] }}
                                                            @if ($value['description'])
                                                              <i class="fas fa-info-circle" data-toggle="popover"
                                                                 data-trigger="hover" data-placement="top"
                                                                 data-html="true"
                                                                 data-content="{{ $value['description'] }}"></i>
                                                            @else
                                                              <i class="invisible fas fa-info-circle"></i>
                                                            @endif
                                                          </label>

                                                        </div>

                                                        <div class="col-md-8 col-12">
                                                            <div class="d-flex align-items-center">
                                                                <div class="w-100">
                                                                    @switch($value)
                                                                        @case($value['type'] == 'string')
                                                                            <input type="text" class="form-control"
                                                                                name="{{ $key }}"
                                                                                value="{{ $value['value'] }}">
                                                                        @break

                                                                        @case($value['type'] == 'password')
                                                                            <input type="password" class="form-control"
                                                                                name="{{ $key }}"
                                                                                value="{{ $value['value'] }}">
                                                                        @break

                                                                        @case($value['type'] == 'boolean')
                                                                            <input type="checkbox" name="{{ $key }}"
                                                                                value="{{ $value['value'] }}"
                                                                                {{ $value['value'] ? 'checked' : '' }}>
                                                                        @break

                                                                        @case($value['type'] == 'number')
                                                                            <input type="number" step="{{ $value['step'] ?? '1' }}" class="form-control"
                                                                                name="{{ $key }}"
                                                                                value="{{ $value['value'] }}">
                                                                        @break

                                                                        @case($value['type'] == 'select')
                                                                            <select id="{{ $key }}"
                                                                                class="custom-select w-100"
                                                                                name="{{ $key }}">
                                                                                @if ($value['identifier'] == 'display')
                                                                                    @foreach ($value['options'] as $option => $display)
                                                                                        <option value="{{ $display }}"
                                                                                            {{ $value['value'] == $display ? 'selected' : '' }}>
                                                                                            {{ __($display) }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                @else
                                                                                    @foreach ($value['options'] as $option => $display)
                                                                                        <option value="{{ $option }}"
                                                                                            {{ $value['value'] == $option ? 'selected' : '' }}>
                                                                                            {{ __($display) }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                @endif
                                                                            </select>
                                                                        @break

                                                                        @case($value['type'] == 'multiselect')
                                                                            <select id="{{ $key }}"
                                                                                class="custom-select w-100"
                                                                                name="{{ $key }}[]" multiple>
                                                                                @foreach ($value['options'] as $option)
                                                                                    <option value="{{ $option }}"
                                                                                        {{ strpos($value['value'], $option) !== false ? 'selected' : '' }}>
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

                                                <!-- TODO: Display this only on the General tab

                                                                                                                                                                    <div class="row">
                                                                                                                                                                        <div class="col-4 d-flex align-items-center">
                                                                                                                                                                            <label for="recaptcha_preview">{{ __('ReCAPTCHA Preview') }}</label>
                                                                                                                                                                        </div>

                                                                                                                                                                        <div class="col-8">

                                                                                                                                                                                <div class="w-100">
                                                                                                                                                                        <div class="mb-3 input-group">
                                                                                                                                                                            {!! htmlScriptTagJsApi() !!}
                                                                                                                                                                        {!! htmlFormSnippet() !!}
                                                                                                                                                                        @error('g-recaptcha-response')
            <span class="text-danger" role="alert">
                                                                                                                                                                                                                                                                <small><strong>{{ $message }}</strong></small>
                                                                                                                                                                                                                                                                    </span>
        @enderror
                                                                                                                                                                        </div>
                                                                                                                                                                                </div>
                                                                                                                                                                        </div>
                                                                                                                                                                    </div>
                                                                                                                                                                       -->


                                                <div class="row">
                                                    <div class="col-12 d-flex align-items-center justify-content-end">
                                                        <button type="submit" class="float-right btn btn-primary ">Save
                                                        </button>
                                                        <button type="reset"
                                                            class="float-right ml-2 btn btn-secondary">Reset
                                                        </button>
                                                    </div>
                                                </div>

                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                            </form>
                                        </div>
                                    @endcanany
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
            window.location.hash = this.hash;
        });

        document.addEventListener('DOMContentLoaded', (event) => {
            $('.custom-select').select2({
                width: '100%',
            });
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
