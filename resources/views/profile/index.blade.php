@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Profile</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a class="text-muted" href="{{route('profile.index')}}">Profile</a>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-lg-12 px-0">
                    @if(!Auth::user()->hasVerifiedEmail() && strtolower($force_email_verification) == 'true')
                        <div class="alert alert-warning p-2 m-2">
                            <h5><i class="icon fas fa-exclamation-circle"></i>Required Email verification!</h5>
                            You have not yet verified your email address
                            <a class="text-primary" href="{{route('verification.send')}}">Click here to resend
                                verification email</a> <br>
                            Please contact support If you didn't receive your verification email.
                        </div>
                    @endif

                    @if(is_null(Auth::user()->discordUser) && strtolower($force_discord_verification) == 'true')
                        @if(!empty(env('DISCORD_CLIENT_ID')) && !empty(env('DISCORD_CLIENT_SECRET')))
                            <div class="alert alert-warning p-2 m-2">
                                <h5><i class="icon fas fa-exclamation-circle"></i>Required Discord verification!</h5>
                                You have not yet verified your discord account
                                <a class="text-primary" href="{{route('auth.redirect')}}">Login with discord</a> <br>
                                Please contact support If you face any issues.
                            </div>
                        @else
                            <div class="alert alert-danger p-2 m-2">
                                <h5><i class="icon fas fa-exclamation-circle"></i>Required Discord verification!</h5>
                                Due to system settings you are required to verify your discord account! <br>
                                It looks like this hasn't been set-up correctly! Please contact support.
                            </div>
                        @endif
                    @endif

                </div>
            </div>

            <form class="form" action="{{route('profile.update' , Auth::user()->id)}}" method="post">
                @csrf
                @method('PATCH')
                <div class="card">
                    <div class="card-body">
                        <div class="e-profile">
                            <div class="row">
                                <div class="col-12 col-sm-auto mb-4">
                                    <div class="slim rounded-circle  border-secondary border text-gray-dark"
                                         data-label="Change your avatar"
                                         data-max-file-size="3"
                                         data-save-initial-image="true"
                                         style="width: 140px;height:140px; cursor: pointer"
                                         data-size="140,140">
                                        <img src="{{$user->getAvatar()}}" alt="avatar">
                                    </div>
                                </div>
                                <div class="col d-flex flex-column flex-sm-row justify-content-between mb-3">
                                    <div class="text-center text-sm-left mb-2 mb-sm-0"><h4
                                            class="pt-sm-2 pb-1 mb-0 text-nowrap">{{$user->name}}</h4>
                                        <p class="mb-0">{{$user->email}}
                                            @if($user->hasVerifiedEmail())
                                                <i data-toggle="popover" data-trigger="hover" data-content="Verified" class="text-success fas fa-check-circle"></i>
                                            @else
                                                <i data-toggle="popover" data-trigger="hover" data-content="Not verified" class="text-danger fas fa-exclamation-circle"></i>
                                            @endif

                                        </p>
                                        <div class="mt-1">
                                            <span class="badge badge-primary"><i class="fa fa-coins mr-2"></i>{{$user->Credits()}}</span>
                                        </div>
                                    </div>

                                    <div class="text-center text-sm-right"><span
                                            class="badge badge-secondary">{{$user->role}}</span>
                                        <div class="text-muted"><small>{{$user->created_at->isoFormat('LL')}}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <ul class="nav nav-tabs">
                                <li class="nav-item"><a href="javasript:void(0)" class="active nav-link">Settings</a>
                                </li>
                            </ul>
                            <div class="tab-content pt-3">
                                <div class="tab-pane active">
                                    <div class="row">
                                        <div class="col">
                                            <div class="row">
                                                <div class="col">
                                                    <div class="form-group"><label>Name</label> <input
                                                            class="form-control @error('name') is-invalid @enderror"
                                                            type="text" name="name"
                                                            placeholder="{{$user->name}}" value="{{$user->name}}">

                                                        @error('name')
                                                        <div class="invalid-feedback">
                                                            {{$message}}
                                                        </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col">
                                                    <div class="form-group"><label>Email</label> <input
                                                            class="form-control @error('email') is-invalid @enderror"
                                                            type="text"
                                                            placeholder="{{$user->email}}" name="email"
                                                            value="{{$user->email}}">

                                                        @error('email')
                                                        <div class="invalid-feedback">
                                                            {{$message}}
                                                        </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-sm-6 mb-3">
                                            <div class="mb-3"><b>Change Password</b></div>
                                            <div class="row">
                                                <div class="col">
                                                    <div class="form-group"><label>Current Password</label> <input
                                                            class="form-control @error('current_password') is-invalid @enderror"
                                                            name="current_password" type="password"
                                                            placeholder="••••••">

                                                        @error('current_password')
                                                        <div class="invalid-feedback">
                                                            {{$message}}
                                                        </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col">
                                                    <div class="form-group"><label>New Password</label> <input
                                                            class="form-control @error('new_password') is-invalid @enderror"
                                                            name="new_password" type="password" placeholder="••••••">

                                                        @error('new_password')
                                                        <div class="invalid-feedback">
                                                            {{$message}}
                                                        </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col">
                                                    <div class="form-group"><label>Confirm <span
                                                                class="d-none d-xl-inline">Password</span></label>
                                                        <input
                                                            class="form-control @error('new_password_confirmation') is-invalid @enderror"
                                                            name="new_password_confirmation" type="password"
                                                            placeholder="••••••">

                                                        @error('new_password_confirmation')
                                                        <div class="invalid-feedback">
                                                            {{$message}}
                                                        </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if(!empty(env('DISCORD_CLIENT_ID')) && !empty(env('DISCORD_CLIENT_SECRET')))
                                            <div class="col-12 col-sm-5 offset-sm-1 mb-3">
                                                <b>Link your discord account!</b>
                                                @if(is_null(Auth::user()->discordUser))
                                                    <div class="verify-discord">
                                                        <div class="mb-3">
                                                            @if($credits_reward_after_verify_discord)
                                                                <p>By verifying your discord account, you receive an
                                                                    extra
                                                                    <b><i
                                                                            class="fa fa-coins mx-1"></i>{{$credits_reward_after_verify_discord}}
                                                                    </b> {{CREDITS_DISPLAY_NAME}} and increased server limit
                                                                </p>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <a class="btn btn-light" href="{{route('auth.redirect')}}">
                                                        <i class="fab fa-discord mr-2"></i>Login with Discord
                                                    </a>
                                                @else
                                                    <div class="verified-discord">
                                                        <div class="my-3 callout callout-info">
                                                            <p>You are verified!</p>
                                                        </div>
                                                    </div>
                                                @endif

                                            </div>
                                        @endif
                                    </div>
                                    <div class="row">
                                        <div class="col d-flex justify-content-end">
                                            <button class="btn btn-primary" type="submit">Save Changes</button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>


        </div>
        <!-- END CUSTOM CONTENT -->

        </div>
    </section>
    <!-- END CONTENT -->

@endsection
