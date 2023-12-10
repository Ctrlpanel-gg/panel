@extends('layouts.app')
@php($website_settings = app(App\Settings\WebsiteSettings::class))
<body class="hold-transition dark-mode login-page">

<img src="data:image/png;base64, {{$qrcode_image}} "/>

<form action="/2fa/authenticate" method="POST">
  @csrf
  <input name="one_time_password" type="text">
  @error('one_time_password')
  <span class="text-danger" role="alert">
    <small><strong>{{ $message }}</strong></small>
  </span>
  @enderror

  <button type="submit">Authenticate</button>
</form>


</body>
@section('content')

@endsection
