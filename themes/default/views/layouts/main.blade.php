<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @php($website_settings = app(App\Settings\WebsiteSettings::class))
    @php($general_settings = app(App\Settings\GeneralSettings::class))
    @php($discord_settings = app(App\Settings\DiscordSettings::class))
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

    <script src="{{ asset('plugins/alpinejs/3.12.0_cdn.min.js') }}" defer></script>

    {{-- <link rel="stylesheet" href="{{asset('css/adminlte.min.css')}}"> --}}
    <link rel="stylesheet" href="{{ asset('plugins/datatables/jquery.dataTables.min.css') }}">

    {{-- summernote --}}
    <link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">

    {{-- datetimepicker --}}
    <link rel="stylesheet"
        href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">


    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="preload" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    </noscript>
    <script src="{{ asset('js/app.js') }}"></script>
    <!-- tinymce -->
    <script src="{{ asset('plugins/tinymce/js/tinymce/tinymce.min.js') }}"></script>

    <!-- SweetAlert2 with Dark Theme -->
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <style>
        /* SweetAlert2 Glass Theme Overrides */
        .swal2-popup {
            @apply bg-zinc-900/95 backdrop-blur-sm border border-zinc-800/50 !important;

        }
        .swal2-title {
            @apply text-white !important;
        }
        .swal2-html-container {
            @apply text-zinc-300 !important;
        }
        .swal2-confirm {
            @apply bg-primary-800 text-primary-200 hover:bg-primary-700 !important;
        }
        .swal2-deny {
            @apply bg-red-800 text-red-200 hover:bg-red-700 !important;
        }
        .swal2-cancel {
            @apply bg-zinc-800 text-zinc-200 hover:bg-zinc-700 !important;
        }
    </style>

    <style>
        #userDropdown.dropdown-toggle::after {
            display: none !important;
        }
        
        .sidebar-mini.sidebar-collapse .brand-link span {
            opacity: 0;
            visibility: hidden;
            width: 0;
            display: none;
        }

        .brand-link img {
            transition: margin .3s ease-in-out;
        }

        .sidebar-mini.sidebar-collapse .brand-link img {
            margin-right: 0;
        }
    </style>
    @vite('themes/default/sass/app.scss')
    @vite('themes/default/css/select2.css')
	@vite('themes/default/css/app.css')
</head>

<body class="min-h-screen bg-zinc-950 sidebar-mini layout-fixed">
    <div class="wrapper bg-zinc-950">
        @include('layouts.navbar')

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary bg-zinc-900/50 backdrop-blur-sm border-r border-zinc-800/50">
            @include('layouts.sidebar')
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper bg-zinc-950">

            <!--
            @if (!Auth::user()->hasVerifiedEmail())
                @if (Auth::user()->created_at->diffInHours(now(), false) > 1)
                    <div class="p-2 m-2 alert alert-warning">
                        <h5><i class="icon fas fa-exclamation-circle"></i> {{ __('Warning!') }}</h5>
                        {{ __('You have not yet verified your email address') }} <a class="text-primary"
                            href="{{ route('verification.send') }}">{{ __('Click here to resend verification email') }}</a>
                        <br>
                        {{ __('Please contact support If you didnt receive your verification email.') }}
                    </div>
                @endif
            @endif
            -->

            @yield('content')

            @include('models.redeem_voucher_modal')
        </div>
        <!-- /.content-wrapper -->
        <footer class="main-footer bg-zinc-900/50 border-t border-zinc-800/50 p-4 mt-auto">
            <div class="w-full flex flex-wrap justify-between items-center text-sm text-zinc-400">
                <div>
                    <strong>Copyright &copy; 2021-{{ date('Y') }} 
                        <a href="{{ url('/') }}" class="text-zinc-300 hover:text-white transition-colors">
                            {{ env('APP_NAME', 'Laravel') }}
                        </a>
                    </strong>
                    <span class="px-1">·</span>
                    Powered by <a href="https://CtrlPanel.gg" class="text-zinc-300 hover:text-white transition-colors">CtrlPanel</a>
                    @if (!str_contains(config('BRANCHNAME'), 'main') && !str_contains(config('BRANCHNAME'), 'unknown'))
                        <span class="px-1">·</span> 
                        Version <b>{{ config('app')['version'] }} - {{ config('BRANCHNAME') }}</b>
                    @endif
                </div>

                <div class="flex gap-3">
                    @if ($website_settings->show_imprint)
                        <a href="{{ route('terms', 'imprint') }}" target="_blank" 
                           class="hover:text-white transition-colors">
                            {{ __('Imprint') }}
                        </a>
                    @endif

                    @if ($website_settings->show_privacy)
                        <a href="{{ route('terms', 'privacy') }}" target="_blank"
                           class="hover:text-white transition-colors">
                            {{ __('Privacy') }}
                        </a>
                    @endif

                    @if ($website_settings->show_tos)
                        <a href="{{ route('terms', 'tos') }}" target="_blank"
                           class="hover:text-white transition-colors">
                            {{ __('Terms of Service') }}
                        </a>
                    @endif
                </div>
            </div>
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- Scripts -->
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <!-- Summernote -->
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
    <!-- select2 -->
    <script src="{{ asset('plugins/select2/js/select2.min.js') }}"></script>

    <!-- Moment.js -->
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>

    <!-- Datetimepicker -->
    <script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>

    <!-- Select2 -->
    <script src={{ asset('plugins/select2/js/select2.min.js') }}></script>


    <script>
        $(document).ready(function() {
            $('[data-toggle="popover"]').popover();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });
    </script>
    <script>
        // Toast notification configuration
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: 'rgb(24 24 27 / 0.9)',
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Session notifications
        @if (Session::has('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: '{{ Session::get('error') }}',
                customClass: {
                    popup: 'glass-panel !bg-zinc-900/95',
                }
            });
        @endif

        @if (Session::has('success'))
            Toast.fire({
                icon: 'success',
                title: '{{ Session::get('success') }}'
            });
        @endif

        @if (Session::has('info'))
            Toast.fire({
                icon: 'info',
                title: '{{ Session::get('info') }}'
            });
        @endif

        @if (Session::has('warning'))
            Toast.fire({
                icon: 'warning',
                title: '{{ Session::get('warning') }}'
            });
        @endif
    </script>

    <style>
        /* Dark Theme Overrides */
        .main-sidebar {
            transition: width 0.3s ease-in-out;
        }

        .sidebar-mini.sidebar-collapse .main-sidebar:hover {
            box-shadow: 0 0 35px 0 rgba(0,0,0,0.3);
        }

        .dropdown-menu {
            animation: dropdownFade 0.2s ease-in-out;
        }

        @keyframes dropdownFade {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Override AdminLTE dark theme with our custom dark theme */
        .dark-mode .nav-sidebar .nav-item > .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
        }

        .content-wrapper {
            background: rgb(9, 9, 11) !important;
        }

        /* Glass morphism effects */
        .glass-panel {
            background: rgba(24, 24, 27, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(63, 63, 70, 0.5);
        }

        /* Custom scrollbar for webkit browsers */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: rgba(161, 161, 170, 0.3);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background-color: rgba(161, 161, 170, 0.5);
        }

        /* Hide scrollbar for Firefox */
        .sidebar {
            scrollbar-width: thin;
            scrollbar-color: rgba(161, 161, 170, 0.3) transparent;
        }
    </style>
</body>

</html>
