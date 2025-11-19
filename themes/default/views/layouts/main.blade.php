<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @php($website_settings = app(App\Settings\WebsiteSettings::class))
    @php($general_settings = app(App\Settings\GeneralSettings::class))
    @php($discord_settings = app(App\Settings\DiscordSettings::class))
    @use('App\Constants\PermissionGroups')

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="{{ $website_settings->seo_title }}" property="og:title">
    <meta content="{{ $website_settings->seo_description }}" property="og:description">
    <meta
        content='{{ \Illuminate\Support\Facades\Storage::disk('public')->exists('logo.png') ? asset('storage/logo.png') : asset('images/ctrlpanel_logo.png') }}'
        property="og:image">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon"
        href="{{ \Illuminate\Support\Facades\Storage::disk('public')->exists('favicon.ico') ? asset('storage/favicon.ico') : asset('favicon.ico') }}"
        type="image/x-icon">

    {{-- Core Styles --}}
    @vite('themes/default/css/app.css')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    {{-- Core JS --}}
    @vite('themes/default/js/app.js')

    {{-- Additional Plugin Styles --}}
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">

    <style>
        /* Alpine.js x-cloak - hide elements until Alpine is ready */
        [x-cloak] {
            display: none !important;
        }

        /* Custom scrollbar (theme-aware) */
        ::-webkit-scrollbar {
            width: var(--scroll-size, 8px);
            height: var(--scroll-size, 8px);
        }

        ::-webkit-scrollbar-track {
            background-color: var(--scroll-track, transparent);
            border-radius: var(--scroll-radius, 8px);
        }

        ::-webkit-scrollbar-thumb {
            background-color: var(--scroll-thumb-color, rgb(var(--gray-600) / 0.35));
            border-radius: var(--scroll-radius, 8px);
        }

        ::-webkit-scrollbar-thumb:hover {
            background-color: var(--scroll-thumb-hover, rgb(var(--gray-500) / 0.55));
        }

        /* Select2 Tailwind styling with accent colors */
        .select2-container--default .select2-selection--single {
            @apply bg-gray-800 border-gray-700 text-gray-300 rounded-lg;
            height: 42px;
            padding: 8px 12px;
            transition: all 0.2s;
        }

        .select2-container--default .select2-selection--single:focus,
        .select2-container--default.select2-container--open .select2-selection--single {
            @apply border-accent-500 ring-2 ring-accent-500/30;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            @apply text-gray-300;
            line-height: 26px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }

        .select2-dropdown {
            @apply bg-gray-800 border-gray-700 rounded-lg shadow-2xl;
            border-color: rgb(var(--accent-500));
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background: linear-gradient(to right, rgb(var(--accent-600)), rgb(var(--accent-500)));
        }

        .select2-container--default .select2-results__option--selected {
            @apply bg-accent-700;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            @apply bg-gray-700 border-gray-600 text-gray-300 rounded-lg;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            @apply border-accent-500 ring-2 ring-accent-500/30;
        }
    </style>
    <x-theming />

</head>

<body class="bg-gray-900 antialiased" style="height: auto;">
    <div class="wrapper min-h-screen flex flex-col" x-data="{
        sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false'
    }" x-init="$watch('sidebarOpen', value => localStorage.setItem('sidebarOpen', value))"
        @sidebar-toggle.window="sidebarOpen = $event.detail.open" x-cloak>
        @include('layouts.navbar')

        @include('layouts.sidebar')

        <div class="content-wrapper flex-1  pt-14 transition-all duration-300 ease-in-out"
            :class="sidebarOpen ? 'md:ml-64 ml-0' : 'md:ml-20 ml-0'">
            @yield('content')
            @include('modals.redeem_voucher_modal')
        </div>

        @include('layouts.footer')
    </div>

    <script>
        // jQuery setup for legacy components
        $(document).ready(function() {
            // AJAX CSRF setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });
    </script>
    <script>
        @if (Session::has('error'))
            SwalCustom.fire({
                icon: 'error',
                title: 'Oops...',
                html: '{{ Session::get('error') }}',
            })
        @endif
        @if (Session::has('success'))
            Swal.fire({
                icon: 'success',
                title: '{{ Session::get('success') }}',
                position: 'top-end',
                showConfirmButton: false,
                background: 'linear-gradient(135deg, rgb(var(--gray-800)) 0%, rgb(var(--gray-900)) 100%)',
                color: '#fff',
                toast: true,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    popup: 'rounded-xl border shadow-2xl',
                    timerProgressBar: 'bg-gradient-to-r',
                },
                didOpen: (toast) => {
                    toast.style.borderColor = 'rgb(var(--success) / 0.3)';
                    const progressBar = toast.querySelector('.swal2-timer-progress-bar');
                    if (progressBar) {
                        progressBar.style.background =
                            'linear-gradient(to right, rgb(var(--success)), rgb(var(--info)))';
                    }
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            })
        @endif
        @if (Session::has('info'))
            Swal.fire({
                icon: 'info',
                title: '{{ Session::get('info') }}',
                position: 'top-end',
                showConfirmButton: false,
                background: 'linear-gradient(135deg, rgb(var(--gray-800)) 0%, rgb(var(--gray-900)) 100%)',
                color: '#fff',
                toast: true,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    popup: 'rounded-xl border shadow-2xl',
                    timerProgressBar: 'bg-gradient-to-r',
                },
                didOpen: (toast) => {
                    toast.style.borderColor = 'rgb(var(--accent-500) / 0.3)';
                    const progressBar = toast.querySelector('.swal2-timer-progress-bar');
                    if (progressBar) {
                        progressBar.style.background =
                            'linear-gradient(to right, rgb(var(--accent-500)), rgb(var(--accent-600)))';
                    }
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            })
        @endif
        @if (Session::has('warning'))
            Swal.fire({
                icon: 'warning',
                title: '{{ Session::get('warning') }}',
                position: 'top-end',
                showConfirmButton: false,
                background: 'linear-gradient(135deg, rgb(var(--gray-800)) 0%, rgb(var(--gray-900)) 100%)',
                color: '#fff',
                toast: true,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    popup: 'rounded-xl border shadow-2xl',
                    timerProgressBar: 'bg-gradient-to-r',
                },
                didOpen: (toast) => {
                    toast.style.borderColor = 'rgb(var(--warning) / 0.3)';
                    const progressBar = toast.querySelector('.swal2-timer-progress-bar');
                    if (progressBar) {
                        progressBar.style.background =
                            'linear-gradient(to right, rgb(var(--warning)), rgb(var(--danger)))';
                    }
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            })
        @endif
    </script>
</body>

</html>
