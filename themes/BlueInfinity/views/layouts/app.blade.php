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
  <link rel="stylesheet" href="{{ asset('themes/BlueInfinity/app.css') }}">
</head>
@yield('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10.14.1/dist/sweetalert2.all.min.js"></script>

<script>
  @if (Session::has('error'))
  Swal.fire({
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
    background: '#343a40',
    toast: true,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.addEventListener('mouseenter', Swal.stopTimer)
      toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
  })
  @endif
</script>

</html>
