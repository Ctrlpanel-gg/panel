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
                        <div class="p-0 col-12 col-md-auto settings-menu">
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


                                <button class="btn btn-outline-secondary settings-extensions-toggle" type="button" data-toggle="collapse"
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
                        <div class="p-0 col-12 col-md settings-content">
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
                                                @foreach ($options['sections'] as $sectionKey => $section)
                                                    @if ($section['label'])
                                                        <div class="mt-3 mb-3 pb-2 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-md-between">
                                                            <h6 class="mb-1 mb-md-0 font-weight-bold">{{ __($section['label']) }}</h6>
                                                            @if ($section['description'])
                                                                <small class="text-muted">{{ __($section['description']) }}</small>
                                                            @endif
                                                        </div>
                                                    @endif
                                                    @foreach ($section['options'] as $key => $value)
                                                    <div class="mb-3 row" id="field_{{ $category }}_{{ $key }}" @if (isset($value['visible_when'])) data-visible-when='{{ json_encode($value['visible_when']) }}' @endif>
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

                                                                        @case($value['type'] == 'secret')
                                                                            <input type="text" class="form-control"
                                                                                name="{{ $key }}"
                                                                                value="{{ $value['value'] }}"
                                                                                autocomplete="off"
                                                                                spellcheck="false">
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
                                                                            @if (isset($value['suffix']))
                                                                                <div class="input-group">
                                                                                    <input type="number" step="{{ $value['step'] ?? '1' }}" class="form-control"
                                                                                        name="{{ $key }}"
                                                                                        value="{{ isset($value['converted_value']) ? $value['converted_value'] : $value['value'] }}">
                                                                                    <div class="input-group-append">
                                                                                        <span class="input-group-text"
                                                                                            data-suffix-for="{{ $key }}"
                                                                                            data-suffix-mapping='{{ json_encode($value['suffix']) }}'>&nbsp;</span>
                                                                                    </div>
                                                                                </div>
                                                                            @else
                                                                                <input type="number" step="{{ $value['step'] ?? '1' }}" class="form-control"
                                                                                    name="{{ $key }}"
                                                                                    value="{{ isset($value['converted_value']) ? $value['converted_value'] : $value['value'] }}">
                                                                            @endif
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

								@if ($sectionKey === 'recaptcha' && !empty($section['options']))
                                                        <div class="row">
                                                            <div class="col-md-4 col-12 d-flex align-items-center">
                                                                <label class="w-100 mb-0" for="recaptcha_preview">{{ __('Captcha Preview') }}</label>
                                                            </div>

                                                            <div class="col-md-8 col-12">
                                                                @php
                                                                    $captchaVersion = $section['options']['recaptcha_version']['value'] ?? null;
                                                                    $captchaSiteKey = $section['options']['recaptcha_site_key']['value'] ?? null;
                                                                    $captchaConfigured = $captchaVersion && $captchaSiteKey;
                                                                @endphp

                                                                @if ($captchaConfigured && $captchaVersion === 'v2')
                                                                    <div class="mb-3 input-group">
                                                                        @captchaScripts
                                                                        <x-captcha />
                                                                    </div>
                                                                @elseif ($captchaConfigured)
                                                                    <div class="alert alert-info mb-0 py-2 d-flex align-items-center">
                                                                        <i class="fas fa-eye-slash mr-2"></i>
                                                                        <span class="small">
                                                                            @if ($captchaVersion === 'v3')
                                                                                {{ __('reCAPTCHA V3 runs invisibly in the background so users are not shown a widget. A badge appears in the bottom-left corner of the page') }}
                                                                            @elseif ($captchaVersion === 'turnstile')
                                                                                {{ __('Turnstile widget visibility is configured in the Cloudflare dashboard so it may appear as an interactive checkbox or run invisibly, and the panel cannot detect which mode you selected') }}
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                @else
                                                                    <div class="alert alert-secondary mb-0 py-2 d-flex align-items-center">
                                                                        <i class="fas fa-info-circle mr-2"></i>
                                                                        <span class="small">
                                                                            {{ __('Captcha is not configured yet, select a version and provide a site key to see the preview') }}
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach

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

    <style>
        .settings-menu .nav-sidebar .nav-link {
            display: flex;
            align-items: center;
            white-space: nowrap;
            overflow: hidden;
        }

        .settings-menu .settings-extensions-toggle {
            margin-bottom: 0.2rem;
        }

        .settings-menu .nav-sidebar .nav-link .nav-icon {
            flex: 0 0 auto;
        }

        .settings-menu .nav-sidebar .nav-link p {
            flex: 0 1 auto;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            width: auto !important;
            margin-left: 0 !important;
            visibility: visible !important;
            animation: none !important;
        }

        .settings-menu .nav-sidebar .nav-header {
            white-space: nowrap;
            overflow: hidden;
        }

        @media (min-width: 768px) {
            .settings-menu {
                max-width: 240px;
            }
        }

        @media (max-width: 767.98px) {
            .settings-menu .nav-sidebar {
                flex-direction: row;
                flex-wrap: nowrap;
                overflow-x: auto;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>

    <script>
        function showSettingsTab(tabLink) {
            $('.settings-menu .nav-link').removeClass('active');
            $('.tab-content .tab-pane').removeClass('active show');
            $(tabLink).addClass('active');
            $($(tabLink).attr('href')).addClass('active show');
        }

        $('.settings-menu a[data-toggle="pill"]').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showSettingsTab(this);
            history.replaceState(null, '', this.hash);
        });

        const tabPaneHash = window.location.hash;
        if (tabPaneHash) {
            const tabLink = $('.settings-menu a[href="' + tabPaneHash + '"]');
            if (tabLink.length) {
                showSettingsTab(tabLink);
                if (tabLink.closest('#collapseExtensions').length) {
                    $('#collapseExtensions').collapse('show');
                }
            }
        }

        function applySettingsFieldVisibility() {
            // Hide/show fields based on the value of another option (visible_when).
            // Lookups are scoped to the enclosing form so that duplicate option
            // keys across categories (e.g. fee_type on every gateway) don't clash.
            $('[data-visible-when]').each(function () {
                const $row = $(this);
                const $form = $row.closest('form');
                const conditions = $row.data('visible-when');
                let visible = true;

                for (const [depKey, expected] of Object.entries(conditions || {})) {
                    const $dep = $form.find('#' + depKey);
                    if (!$dep.length) {
                        visible = false;
                        break;
                    }

                    const actual = $dep.val();
                    const expectedValues = Array.isArray(expected) ? expected : [expected];
                    if (!expectedValues.includes(actual)) {
                        visible = false;
                        break;
                    }
                }

                $row.toggle(visible);
            });

            // Update the suffix of an input based on the value of another option.
            $('[data-suffix-mapping]').each(function () {
                const $suffix = $(this);
                const $form = $suffix.closest('form');
                const mapping = $suffix.data('suffix-mapping') || {};
                const $dep = $form.find('#' + (mapping.depends_on || ''));
                const value = $dep.length ? $dep.val() : null;
                const text = (mapping.map && mapping.map[value]) || '';
                $suffix.text(text || '\u00A0');
            });
        }

        $(function () {
            $('.custom-select').select2({
                width: '100%',
            });

            applySettingsFieldVisibility();
        });

        $(document).on('change', '.custom-select', applySettingsFieldVisibility);

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
