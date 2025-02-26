<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  @php($website_settings = app(App\Settings\WebsiteSettings::class))

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


  <link rel="preload" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
  <noscript>
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  </noscript>
  <script src="{{ asset('js/app.js') }}"></script>

  @vite('themes/default/sass/app.scss')
</head>


<body class="sidebar-mini layout-fixed dark-mode d-flex align-items-center justify-content-center" style="height: auto; ">





<!-- /.card -->

  <div class="card card-default" style="max-width: 40vw; margin-top: 20vh">
    <div class="card-header d-flex align-items-center justify-content-center">
      <h3 class="card-title">
        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
        <h1 class="mb-2 text-5xl font-extrabold">  <span class="badge bg-danger">{{__("ERROR")}} {{ $errorCode }} - {{ $title }}</span></h1>
      </h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body py-0 pb-2">

      <div class="row d-flex justify-content-center text-center">

        <h5>{{$message}}</h5>

      </div>

      @if($homeLink ?? false)
        <div class="row d-flex justify-content-center">


          <hr style="width: 100%; height:1px; border-width:0; background-color:#6c757d; margin-bottom: 5px">

        <a href="{{ route("home") }}" class="mr-1 btn btn-sm btn-primary mt-2"><i class="fas fa-sign-in-alt"></i>Go home</a>

        </div>

          <hr style="width: 100%; height:1px; border-width:0; background-color:#6c757d; margin-bottom: 0px">
      @endif

    </div>
    <!-- /.card-body -->
  </div>

<!-- /.card -->


</body>

</html>

