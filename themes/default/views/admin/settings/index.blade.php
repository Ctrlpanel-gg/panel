@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="max-w-screen-2xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Settings') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Settings') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Settings Navigation -->
            <div class="lg:col-span-3">
                <div class="glass-panel">
                    <div class="p-6 border-b border-zinc-800/50">
                        <h5 class="text-lg font-medium text-white flex items-center">
                            <i class="fas fa-cog mr-2 text-zinc-400"></i>
                            {{__('Settings Categories')}}
                        </h5>
                    </div>
                    <div class="p-4">
                        <nav class="space-y-2">
                            @can("admin.icons.edit")
                            <button type="button" data-tab="icons" 
                                    class="nav-link w-full text-left" role="tab">
                                <i class="fas fa-image w-5 mr-3"></i>
                                {{ __('Images / Icons') }}
                            </button>
                            @endcan

                            @foreach ($settings as $category => $options)
                                @if (!str_contains($options['settings_class'], 'Extension'))
                                    @canany(['settings.' . strtolower($category) . '.read', 'settings.' . strtolower($category) . '.write'])
                                    <button type="button" data-tab="{{ $category }}"
                                            class="nav-link w-full text-left {{ $loop->first ? 'active' : '' }}" role="tab">
                                        <i class="{{ $options['category_icon'] ?? 'fas fa-cog' }} w-5 mr-3"></i>
                                        {{ $category }}
                                    </button>
                                    @endcanany
                                @endif
                            @endforeach

                            <!-- Extension Settings Section -->
                            <div class="mt-6">
                                <div class="mb-2 text-sm font-medium text-zinc-500">{{ __('Extension Settings') }}</div>
                                @foreach ($settings as $category => $options)
                                    @if (str_contains($options['settings_class'], 'Extension'))
                                        @canany(['settings.' . strtolower($category) . '.read', 'settings.' . strtolower($category) . '.write'])
                                        <button type="button" data-tab="{{ $category }}"
                                                class="nav-link w-full text-left pl-6" role="tab">
                                            <i class="{{ $options['category_icon'] ?? 'fas fa-cog' }} w-5 mr-3"></i>
                                            {{ $category }}
                                        </button>
                                        @endcanany
                                    @endif
                                @endforeach
                            </div>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Settings Content -->
            <div class="lg:col-span-9">
                <div class="glass-panel">
                    <div class="tab-content">
                        <!-- Icons Tab -->
                        <div class="tab-pane fade" id="icons" role="tabpanel">
                            <div class="p-6 border-b border-zinc-800/50">
                                <h5 class="text-lg font-medium text-white">{{ __('Icons & Images') }}</h5>
                            </div>
                            <div class="p-6">
                                <form method="POST" enctype="multipart/form-data" action="{{ route('admin.settings.updateIcons') }}">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div class="ml-5">
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
                                                <input type="file" 
                                                       accept="image/x-icon" 
                                                       class="block w-full text-zinc-400 rounded-lg cursor-pointer
                                                              file:mr-4 file:py-2 file:px-4 file:border-0
                                                              file:text-sm file:font-medium file:bg-primary-800/80
                                                              file:text-primary-200 hover:file:bg-primary-700/80
                                                              file:rounded-lg border border-zinc-800/50 bg-zinc-900/50"
                                                       name="favicon" 
                                                       id="favicon">
                                            </div>
                                        </div>

                                        <div class="ml-5">
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
                                                <input type="file" 
                                                       accept="image/png,image/jpeg,image/jpg"
                                                       class="block w-full text-zinc-400 rounded-lg cursor-pointer
                                                              file:mr-4 file:py-2 file:px-4 file:border-0
                                                              file:text-sm file:font-medium file:bg-primary-800/80
                                                              file:text-primary-200 hover:file:bg-primary-700/80
                                                              file:rounded-lg border border-zinc-800/50 bg-zinc-900/50"
                                                       name="icon" 
                                                       id="icon">
                                            </div>
                                        </div>

                                        <div class="ml-5">
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
                                                <input type="file" 
                                                       accept="image/png,image/jpeg,image/jpg"
                                                       class="block w-full text-zinc-400 rounded-lg cursor-pointer
                                                              file:mr-4 file:py-2 file:px-4 file:border-0
                                                              file:text-sm file:font-medium file:bg-primary-800/80
                                                              file:text-primary-200 hover:file:bg-primary-700/80
                                                              file:rounded-lg border border-zinc-800/50 bg-zinc-900/50"
                                                       name="logo" 
                                                       id="logo">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-6 flex justify-end">
                                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Settings Tabs -->
                        @foreach ($settings as $category => $options)
                            @canany(['settings.' . strtolower($category) . '.read', 'settings.' . strtolower($category) . '.write'])
                            <div class="tab-pane fade {{ $loop->first ? 'active show' : '' }}" id="{{ $category }}" role="tabpanel">
                                <div class="p-6 border-b border-zinc-800/50">
                                    <h5 class="text-lg font-medium text-white">{{ $category }} {{ __('Settings') }}</h5>
                                </div>
                                <div class="p-6">
                                    <form action="{{ route('admin.settings.update') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="settings_class" value="{{ $options['settings_class'] }}">
                                        <input type="hidden" name="category" value="{{ $category }}">
                                        
                                        <div class="space-y-6">
                                            @foreach ($options as $key => $value)
                                                @if ($key == 'category_icon' || $key == 'settings_class' || $key == 'position')
                                                    @continue
                                                @endif
                                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
                                                    <div class="lg:pt-2">
                                                        <label for="{{ $key }}" class="text-zinc-300">
                                                            {{ $value['label'] }}
                                                            @if ($value['description'])
                                                                <i class="fas fa-info-circle ml-1 text-zinc-500" data-toggle="popover" 
                                                                   data-trigger="hover" data-placement="top" data-html="true"
                                                                   data-content="{{ $value['description'] }}"></i>
                                                            @endif
                                                        </label>
                                                    </div>
                                                    <div class="lg:col-span-2">
                                                        <div class="w-full">
                                                            @switch($value)
                                                                @case($value['type'] == 'string')
                                                                    <input type="text" 
                                                                           class="w-full rounded-lg bg-zinc-900/50 border border-zinc-800/50 text-zinc-300 h-[42px] px-3
                                                                                  focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500" 
                                                                           name="{{ $key }}"
                                                                           value="{{ $value['value'] }}">
                                                                @break

                                                                @case($value['type'] == 'password')
                                                                    <input type="password" 
                                                                           class="w-full rounded-lg bg-zinc-900/50 border border-zinc-800/50 text-zinc-300 h-[42px] px-3
                                                                                  focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500" 
                                                                           name="{{ $key }}"
                                                                           value="{{ $value['value'] }}">
                                                                @break

                                                                @case($value['type'] == 'boolean')
                                                                    <label class="flex items-center space-x-3">
                                                                        <input type="checkbox" 
                                                                               class="w-5 h-5 rounded bg-zinc-900/50 border-zinc-800/50 text-primary-600
                                                                                      checked:bg-primary-600 checked:border-primary-600
                                                                                      focus:outline-none focus:ring-2 focus:ring-primary-500/20" 
                                                                               name="{{ $key }}"
                                                                               value="1"
                                                                               {{ $value['value'] ? 'checked' : '' }}>
                                                                        <span class="text-zinc-400">{{ __('Enabled') }}</span>
                                                                    </label>
                                                                @break

                                                                @case($value['type'] == 'number')
                                                                    <input type="number" 
                                                                           class="w-full rounded-lg bg-zinc-900/50 border border-zinc-800/50 text-zinc-300 h-[42px] px-3
                                                                                  focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500" 
                                                                           step="{{ $value['step'] ?? '1' }}" 
                                                                           name="{{ $key }}"
                                                                           value="{{ $value['value'] }}">
                                                                @break

                                                                @case($value['type'] == 'select')
                                                                    <select id="{{ $key }}"
                                                                            class="w-full rounded-lg bg-zinc-900/50 border border-zinc-800/50 text-zinc-300 h-[42px] px-4 py-2
                                                                                   focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"
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
                                                                            class="w-full rounded-lg bg-zinc-900/50 border border-zinc-800/50 text-zinc-300 min-h-[42px] px-4 py-2
                                                                                   focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"
                                                                            name="{{ $key }}[]" 
                                                                            multiple>
                                                                        @foreach ($value['options'] as $option)
                                                                            <option value="{{ $option }}"
                                                                                {{ strpos($value['value'], $option) !== false ? 'selected' : '' }}>
                                                                                {{ __($option) }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                @break

                                                                @case($value['type'] == 'textarea')
                                                                    <textarea class="w-full rounded-lg bg-zinc-900/50 border border-zinc-800/50 text-zinc-300 px-3 py-2
                                                                                     focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500" 
                                                                              name="{{ $key }}" 
                                                                              rows="3">{{ $value['value'] }}</textarea>
                                                                @break

                                                                @default
                                                            @endswitch
                                                            @error($key)
                                                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="mt-6 flex justify-end space-x-3">
                                            <button type="reset" class="btn bg-zinc-800 text-zinc-300 hover:bg-zinc-700">
                                                {{ __('Reset') }}
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                {{ __('Save Changes') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @endcanany
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

    // Add this to handle tab switching
    document.querySelectorAll('[data-tab]').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent default anchor behavior
            
            // Remove active class from all buttons
            document.querySelectorAll('[data-tab]').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to clicked button
            button.classList.add('active');
            
            // Hide all tab panes
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            
            // Show selected tab pane
            const tabId = button.getAttribute('data-tab');
            const tabPane = document.getElementById(tabId);
            if (tabPane) {
                tabPane.classList.add('show', 'active');
            }
            
            // Update URL hash without scrolling
            history.replaceState(null, null, '#' + tabId);
        });
    });

    // Show initial tab based on URL hash
    const initialTab = window.location.hash.replace('#', '');
    if (initialTab) {
        const tabButton = document.querySelector(`[data-tab="${initialTab}"]`);
        if (tabButton) {
            tabButton.click();
        }
    } else {
        // Show first tab by default
        const firstTab = document.querySelector('[data-tab]');
        if (firstTab) {
            firstTab.click();
        }
    }

    // Initialize Select2
    $(document).ready(function() {
        $('select').select2({
            width: '100%',
            closeOnSelect: false,
            dropdownParent: $('body')
        });
    });

</script>

<style>
    select { width: 100%; }
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        background-color: rgb(9 9 11) !important;
        border: 1px solid rgb(39 39 42) !important;
        border-radius: 0.5rem !important;
        min-height: 42px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 4px !important;
        padding: 2px 8px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: rgb(212 212 216) !important;
        padding: 0 8px !important;
        line-height: normal !important;
    }
    .select2-dropdown {
        background-color: rgb(9 9 11) !important;
        border: 1px solid rgb(39 39 42) !important;
    }
    .select2-search__field {
        background-color: rgb(24 24 27) !important;
        border-color: rgb(39 39 42) !important;
        color: white !important;
        padding: 8px !important;
        margin: 4px !important;
    }
    
    .select2-search--dropdown {
        padding: 8px !important;
    }

    .select2-search--inline .select2-search__field {
        margin: 0 !important;
        padding: 0 !important;
        min-height: 28px !important;
    }

    .select2-results__option {
        padding: 8px 16px !important;
        color: rgb(212 212 216) !important;
    }
    .select2-results__option--highlighted[aria-selected] {
        background-color: rgb(59 130 246 / 0.2) !important;
        color: rgb(147 197 253) !important;
    }
    .select2-results__option[aria-selected=true] {
        background-color: rgb(29 78 216 / 0.2) !important;
        color: rgb(147 197 253) !important;
    }
    .select2-selection__choice {
        background-color: rgb(29 78 216 / 0.2) !important;
        border: none !important;
        color: rgb(147 197 253) !important;
        border-radius: 4px !important;
        padding: 4px 8px !important;
        margin: 4px !important;
    }
    .select2-selection__choice__remove {
        color: rgb(147 197 253 / 0.6) !important;
        margin-right: 6px !important;
    }
    .select2-selection__choice__remove:hover {
        color: rgb(147 197 253) !important;
    }
</style>
@endsection
