@extends('layouts.app')

@section('content')
    <body class="hold-transition dark-mode login-page">
    <div class="login-box">
        <!-- /.login-logo -->
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="{{route('welcome')}}" class="h1"><b class="mr-1">{{config('app.name', 'Laravel')}}</b></a>
            </div>
            <div class="card-body">
                <p class="login-box-msg">Sign in to start your session</p>

                @if (session('message'))
                    <div class="alert alert-danger">{{ session('message') }}</div>
                @endif

                <form action="{{route('login')}}" method="post">
                    @csrf
                    @if(Session::has('error'))
                        <span class="text-danger" role="alert">
                                <small><strong>{{Session::get('error')}}</strong></small>
                            </span>
                    @endif

                    <div class="form-group">
                        <div class="input-group mb-3">
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>

                        </div>
                        @error('email')
                        <span class="text-danger" role="alert">
                                <small><strong>{{ $message }}</strong></small>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="input-group mb-3">
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Password">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>

                        </div>
                        @error('password')
                        <span class="text-danger" role="alert">
                                <small><strong>{{ $message }}</strong></small>
                            </span>
                        @enderror
                    </div>

                    <div class="input-group mb-3">
                        {!! htmlFormSnippet() !!}
                        @error('g-recaptcha-response')
                        <span class="text-danger" role="alert">
                                <small><strong>{{ $message }}</strong></small>
                            </span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label for="remember">
                                    Remember Me
                                </label>
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>

            {{--                <div class="social-auth-links text-center mt-2 mb-3">--}}
            {{--                    <a href="#" class="btn btn-block btn-primary">--}}
            {{--                        <i class="fab fa-facebook mr-2"></i> Sign in using Facebook--}}
            {{--                    </a>--}}
            {{--                    <a href="#" class="btn btn-block btn-danger">--}}
            {{--                        <i class="fab fa-google-plus mr-2"></i> Sign in using Google+--}}
            {{--                    </a>--}}
            {{--                </div>--}}
            <!-- /.social-auth-links -->

                <p class="mb-1">
                    @if (Route::has('password.request'))
                        <a class="" href="{{ route('password.request') }}">
                            {{ __('Forgot Your Password?') }}
                        </a>
                    @endif
                </p>
                <p class="mb-0">
                    <a href="{{route('register')}}" class="text-center">Register a new membership</a>
                </p>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.login-box -->
    </body>
@endsection
