<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @php($website_settings = app(App\Settings\WebsiteSettings::class))


    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="{{ $website_settings->seo_title }}" property="og:title">
    <meta content="{{ $website_settings->seo_description }}" property="og:description">
    <meta content='{{ \Illuminate\Support\Facades\Storage::disk('public')->exists('logo.png') ? asset('/logo.png') : asset('images/ctrlpanel_logo.png') }}' property="og:image">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon"
        href="{{ \Illuminate\Support\Facades\Storage::disk('public')->exists('favicon.ico') ? asset('storage/favicon.ico') : asset('favicon.ico') }}"
        type="image/x-icon">

    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <link rel="preload" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    </noscript>
    @vite(['themes/default/css/app.css'])
    
    @php ($recaptchaVersion = app(App\Settings\GeneralSettings::class)->recaptcha_version)
    @if ($recaptchaVersion)
        @switch($recaptchaVersion)
            @case("v2")
                {!! htmlScriptTagJsApi() !!}
                @break
            @case("v3")
                {!! RecaptchaV3::initJs() !!}
                @break
        @endswitch
    @endif

    <!-- SweetAlert2 with Dark Theme -->
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script src="https://unpkg.com/flowbite@1.5.3/dist/flowbite.js"></script>

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
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>
    <link href="https://unpkg.com/@tiptap/core@2.1.13/dist/tiptap.css" rel="stylesheet">
</head>
<body>
    @yield('content')

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10.14.1/dist/sweetalert2.all.min.js"></script>
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
    </script>
</body>
</html>
