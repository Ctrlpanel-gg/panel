@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1>{{ __('Profile') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                href="{{ route('profile.index') }}">{{ __('Profile') }}</a>
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
                <div class="px-0 col-lg-12">
                    @if (!Auth::user()->hasVerifiedEmail() && $force_email_verification)
                        <div class="p-2 m-2 alert alert-warning">
                            <h5><i class="icon fas fa-exclamation-circle"></i>{{ __('Required Email verification!') }}
                            </h5>
                            {{ __('You have not yet verified your email address') }}
                            <a class="text-primary"
                                href="{{ route('verification.send') }}">{{ __('Click here to resend verification email') }}</a>
                            <br>
                            {{ __('Please contact support If you didnt receive your verification email.') }}

                        </div>
                    @endif

                    @if (is_null(Auth::user()->discordUser) && $force_discord_verification)
                        @if (!empty($discord_client_id) && !empty($discord_client_secret))
                            <div class="p-2 m-2 alert alert-warning">
                                <h5>
                                    <i class="icon fas fa-exclamation-circle"></i>{{ __('Required Discord verification!') }}
                                </h5>
                                {{ __('You have not yet verified your discord account') }}
                                <a class="text-primary" href="{{ route('auth.redirect') }}">{{ __('Login with discord') }}</a> <br>
                                {{ __('Please contact support If you face any issues.') }}
                            </div>
                        @else
                            <div class="p-2 m-2 alert alert-danger">
                                <h5>
                                    <i class="icon fas fa-exclamation-circle"></i>{{ __('Required Discord verification!') }}
                                </h5>
                                {{ __('Due to system settings you are required to verify your discord account!') }} <br>
                                {{ __('It looks like this hasnt been set-up correctly! Please contact support.') }}
                            </div>
                        @endif
                    @endif

                </div>
            </div>

            <form class="form" action="{{ route('profile.update', Auth::user()->id) }}" method="post">
                @csrf
                @method('PATCH')
                <div class="card">
                    <div class="card-body">
                        <div class="e-profile">
                            <div class="row">
                                <div class="mb-4 col-12 col-sm-auto">
                                    <div class="border slim rounded-circle border-secondary text-gray-dark"
                                        data-label="Change your avatar" data-max-file-size="3"
                                        data-save-initial-image="true" style="width: 140px;height:140px; cursor: pointer"
                                        data-size="140,140">
                                        <img src="{{ $user->getAvatar() }}" alt="avatar">
                                    </div>
                                </div>
                                <div class="mb-3 col d-flex flex-column flex-sm-row justify-content-between">
                                    <div class="mb-2 text-center text-sm-left mb-sm-0">
                                        <h4 class="pb-1 mb-0 pt-sm-2 text-nowrap">{{ $user->name }}</h4>
                                        <p class="mb-0">{{ $user->email }}
                                            @if ($user->hasVerifiedEmail())
                                                <i data-toggle="popover" data-trigger="hover" data-content="Verified"
                                                    class="text-success fas fa-check-circle"></i>
                                            @else
                                                <i data-toggle="popover" data-trigger="hover" data-content="Not verified"
                                                    class="text-danger fas fa-exclamation-circle"></i>
                                            @endif

                                        </p>
                                        <div class="mt-1">
                                            <span class="badge badge-primary"><i
                                                    class="mr-2 fa fa-coins"></i>{{ Currency::formatForDisplay($user->credits) }}</span>
                                        </div>

                                        @if ($referral_enabled)
                                            <div class="mt-1">
                                                @can('user.referral')
                                                    <span class="badge badge-success">
                                                        <i class="mr-2 fa fa-user-check"></i>
                                                        {{ __('Referral URL') }} :
                                                        <span onclick="onClickCopy()" id="RefLink" style="cursor: pointer;">
                                                            {{ route('register') }}?ref={{ $user->referral_code }}
                                                        </span>
                                                    </span>
                                                @else
                                                    <span class="badge badge-warning">
                                                        <i class="mr-2 fa fa-user-check"></i>
                                                        {{ __('You can not see your Referral Code') }}
                                                    </span>
                                                @endcan
                                            </div>
                                        @endif
                                    </div>

                                    <div class="text-center text-sm-right">
                                        @foreach ($user->roles as $role)
                                            <span style='background-color: {{ $role->color }}'
                                                class='badge'>{{ $role->name }}</span>
                                        @endforeach
                                        <div class="text-muted">
                                            <small>{{ $user->created_at->isoFormat('LL') }}</small>
                                        </div>
                                        <div class="text-muted">
                                            <small>
                                                <button class="badge badge-danger" id="confirmDeleteButton"
                                                    type="button">{{ __('Permanently delete my account') }}</button>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="account-tab" data-toggle="tab" href="#account" role="tab"
                                        aria-controls="account" aria-selected="true">{{ __('Account Settings') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="security-tab" data-toggle="tab" href="#security" role="tab"
                                        aria-controls="security" aria-selected="false">{{ __('Security') }}</a>
                                </li>
                            </ul>
                            <div class="pt-3 tab-content" id="profileTabsContent">
                                <!-- Account Settings Tab -->
                                <div class="tab-pane fade show active" id="account" role="tabpanel"
                                    aria-labelledby="account-tab">
                                    <div class="row">
                                        <div class="col">
                                            <div class="row">
                                                <div class="col">
                                                    @if ($errors->has('pterodactyl_error_message'))
                                                        @foreach ($errors->get('pterodactyl_error_message') as $err)
                                                            <span class="text-danger" role="alert">
                                                                <small><strong>{{ $err }}</strong></small>
                                                            </span>
                                                        @endforeach
                                                    @endif
                                                    @if ($errors->has('pterodactyl_error_status'))
                                                        @foreach ($errors->get('pterodactyl_error_status') as $err)
                                                            <span class="text-danger" role="alert">
                                                                <small><strong>{{ $err }}</strong></small>
                                                            </span>
                                                        @endforeach
                                                    @endif
                                                    <div class="form-group"><label>{{ __('Name') }}</label> <input
                                                            class="form-control @error('name') is-invalid @enderror"
                                                            type="text" name="name" placeholder="{{ $user->name }}"
                                                            value="{{ $user->name }}">

                                                        @error('name')
                                                            <div class="invalid-feedback">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col">
                                                    <div class="form-group"><label>{{ __('Email') }}</label> <input
                                                            class="form-control @error('email') is-invalid @enderror"
                                                            type="text" placeholder="{{ $user->email }}" name="email"
                                                            value="{{ $user->email }}">

                                                        @error('email')
                                                            <div class="invalid-feedback">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if (!empty($discord_client_id) && !empty($discord_client_secret))
                                        <div class="row">
                                            <div class="mb-3 col-12">
                                                <hr>
                                                @if (is_null(Auth::user()->discordUser))
                                                    <div class="verify-discord">
                                                        <b>{{ __('Link your discord account!') }}</b>
                                                        <div class="mb-3">
                                                            @if ($credits_reward_after_verify_discord)
                                                                <p>
                                                                    {{ __('By verifying your discord account, you receive an extra :amount credits and increased Server amounts', ['amount' => Currency::formatForDisplay($credits_reward_after_verify_discord)]) }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                        <a class="btn btn-light" href="{{ route('auth.redirect') }}">
                                                            <i class="mr-2 fab fa-discord"></i>{{ __('Login with Discord') }}
                                                        </a>
                                                    </div>
                                                @else
                                                    <div class="verified-discord">
                                                        <div class="pl-2 row">
                                                            <div class="small-box bg-dark d-inline-block">
                                                                <div class="d-flex justify-content-between">
                                                                    <div class="p-3">
                                                                        <h3>{{ $user->discordUser->username }}</h3>
                                                                        <p class="mb-0">{{ $user->discordUser->email }}</p>
                                                                        <p class="mb-0 text-muted text-sm">
                                                                            {{ $user->discordUser->id }}</p>
                                                                    </div>
                                                                    <div class="p-3"><img width="100px" height="100px"
                                                                            class="rounded-circle"
                                                                            src="{{ $user->discordUser->getAvatar() }}"
                                                                            alt="avatar"></div>
                                                                </div>
                                                                <div class="small-box-footer">
                                                                    <a href="{{ route('auth.redirect') }}">
                                                                        <i
                                                                            class="mr-1 fab fa-discord"></i>{{ __('Re-Sync Discord') }}
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                    <div class="row">
                                        <div class="col d-flex justify-content-end">
                                            <button class="btn btn-primary" type="submit">{{ __('Save Changes') }}</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Security Tab -->
                                <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                                    <div class="row">
                                        <div class="mb-3 col-12 col-sm-6">
                                            <div class="mb-3"><b>{{ __('Change Password') }}</b></div>
                                            <div class="row">
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label>{{ __('Current Password') }}</label>
                                                        <input
                                                            class="form-control @error('current_password') is-invalid @enderror"
                                                            name="current_password" type="password" placeholder="••••••">

                                                        @error('current_password')
                                                            <div class="invalid-feedback">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col">
                                                    <div class="form-group"><label>{{ __('New Password') }}</label>
                                                        <input
                                                            class="form-control @error('new_password') is-invalid @enderror"
                                                            name="new_password" type="password" placeholder="••••••">

                                                        @error('new_password')
                                                            <div class="invalid-feedback">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label>{{ __('Confirm Password') }}</span></label>
                                                        <input
                                                            class="form-control @error('new_password_confirmation') is-invalid @enderror"
                                                            name="new_password_confirmation" type="password"
                                                            placeholder="••••••">

                                                        @error('new_password_confirmation')
                                                            <div class="invalid-feedback">
                                                                {{ $message }}
                                                            </div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-12 col-sm-5 offset-sm-1">
                                            <div class="mb-3"><b>{{ __('Two-Factor Authentication') }}</b></div>
                                            @if (!$user->two_factor_enabled)
                                                <p>
                                                    {{ __('Two-factor authentication adds an additional layer of security to your account by requiring more than just a password to log in.') }}
                                                </p>
                                                <button type="button" class="btn btn-success" data-toggle="modal"
                                                    data-target="#enable2faModal">
                                                    {{ __('Enable 2FA') }}
                                                </button>
                                            @else
                                                <div class="mb-3 callout callout-success">
                                                    <h5><i class="fas fa-check-circle mr-2"></i>{{ __('2FA is enabled') }}
                                                    </h5>
                                                    <p>{{ __('Your account is protected with two-factor authentication.') }}
                                                    </p>
                                                </div>
                                                <button type="button" class="btn btn-danger" data-toggle="modal"
                                                    data-target="#disable2faModal">
                                                    {{ __('Disable 2FA') }}
                                                </button>
                                                <button type="button" class="btn btn-info" data-toggle="modal"
                                                    data-target="#download2faModal">
                                                    <i class="fas fa-download mr-1"></i>{{ __('Recovery Codes') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col d-flex justify-content-end">
                                            <button class="btn btn-primary" type="submit">{{ __('Save Changes') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="_token" value="{{ csrf_token() }}">
            </form>

        </div>
        <!-- END CUSTOM CONTENT -->

        </div>
    </section>
    <!-- END CONTENT -->

    <!-- Enable 2FA Modal -->
    <div class="modal fade" id="enable2faModal" tabindex="-1" role="dialog" aria-labelledby="enable2faModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="enable2faModalLabel">{{ __('Enable Two-Factor Authentication') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="enabletwofa-step-1">
                        <p>{{ __('Please enter your current password to continue.') }}</p>
                        <div class="form-group">
                            <label>{{ __('Current Password') }}</label>
                            <input type="password" id="twofa-enable-password" class="form-control">
                        </div>
                        <button type="button" class="btn btn-primary" onclick="generate2FA()">{{ __('Next') }}</button>
                    </div>
                    <div id="enabletwofa-step-2" style="display: none;">
                        <p>{{ __('Scan the QR code with your authenticator app (e.g. Google Authenticator, Authy).') }}</p>
                        <div id="twofa-qr-code" class="mb-3 text-center"></div>
                        <div class="mb-3 text-center">
                            <strong>{{ __('Secret Key') }}:</strong> <code id="twofa-secret-key"></code>
                        </div>
                        <div class="form-group">
                            <label>{{ __('Authentication Code') }}</label>
                            <input type="text" id="twofa-enable-code" class="form-control" placeholder="123456">
                        </div>
                        <button type="button" class="btn btn-success"
                            onclick="enable2FA()">{{ __('Confirm Activation') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Disable 2FA Modal -->
    <div class="modal fade" id="disable2faModal" tabindex="-1" role="dialog" aria-labelledby="disable2faModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="disable2faModalLabel">{{ __('Disable Two-Factor Authentication') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Please enter your password and the code from your authenticator app to disable 2FA.') }}</p>
                    <div class="form-group">
                        <label>{{ __('Current Password') }}</label>
                        <input type="password" id="twofa-disable-password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{ __('Authentication Code') }}</label>
                        <input type="text" id="twofa-disable-code" class="form-control" placeholder="123456">
                    </div>
                    <button type="button" class="btn btn-danger"
                        onclick="disable2FA()">{{ __('Confirm Deactivation') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Download Recovery Codes Modal -->
    <div class="modal fade" id="download2faModal" tabindex="-1" role="dialog" aria-labelledby="download2faModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="download2faModalLabel">{{ __('Download Recovery Codes') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('profile.2fa.recovery-codes') }}" method="POST"
                        onsubmit="$('#download2faModal').modal('hide')">
                        @csrf
                        <p>{{ __('Please enter your password to download your recovery codes.') }}</p>
                        <div class="form-group">
                            <label>{{ __('Current Password') }}</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-info">{{ __('Download') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let generatedSecret = '';

        async function generate2FA() {
            const password = $('#twofa-enable-password').val();
            if (!password) {
                Swal.fire("{{ __('Error') }}", "{{ __('Please enter your password.') }}", 'error');
                return;
            }

            try {
                const response = await $.ajax({
                    type: "POST",
                    url: "{{ route('profile.2fa.generate') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        password: password
                    }
                });

                generatedSecret = response.secret;
                $('#twofa-qr-code').html(response.qr_code);
                $('#twofa-secret-key').text(response.secret);
                $('#enabletwofa-step-1').hide();
                $('#enabletwofa-step-2').show();
            } catch (error) {
                Swal.fire("{{ __('Error') }}", (error.responseJSON && error.responseJSON.message) ? error.responseJSON.message : "{{ __('Something went wrong') }}", 'error');
            }
        }

        async function enable2FA() {
            const code = $('#twofa-enable-code').val();

            if (!code) {
                Swal.fire("{{ __('Error') }}", "{{ __('Please enter the authentication code.') }}", 'error');
                return;
            }

            try {
                const response = await $.ajax({
                    type: "POST",
                    url: "{{ route('profile.2fa.enable') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        code: code,
                        secret: generatedSecret
                    }
                });

                Swal.fire({
                    title: "{{ __('2FA Enabled') }}",
                    text: "{{ __('2FA has been successfully enabled! Please download your recovery codes.') }}",
                    icon: 'success'
                }).then(() => {
                    location.reload();
                });
            } catch (error) {
                Swal.fire("{{ __('Error') }}", (error.responseJSON && error.responseJSON.message) ? error.responseJSON.message : "{{ __('Something went wrong') }}", 'error');
            }
        }

        async function disable2FA() {
            const password = $('#twofa-disable-password').val();
            const code = $('#twofa-disable-code').val();

            try {
                const response = await $.ajax({
                    type: "POST",
                    url: "{{ route('profile.2fa.disable') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        password: password,
                        code: code
                    }
                });

                Swal.fire({
                    title: "{{ __('2FA Disabled') }}",
                    text: "{{ __('2FA has been successfully disabled.') }}",
                    icon: 'success'
                }).then(() => {
                    location.reload();
                });
            } catch (error) {
                Swal.fire("{{ __('Error') }}", (error.responseJSON && error.responseJSON.message) ? error.responseJSON.message : "{{ __('Something went wrong') }}", 'error');
            }
        }

        document.getElementById("confirmDeleteButton").onclick = async () => {
            const {
                value: enterConfirm
            } = await Swal.fire({
                input: 'text',
                inputLabel: '{{ __('Are you sure you want to permanently delete your account and all of your servers?') }} \n Type "{{ __('Delete my account') }}" in the Box below',
                inputPlaceholder: "{{ __('Delete my account') }}",
                showCancelButton: true
            });

            if (enterConfirm === "{{ __('Delete my account') }}") {
                $.ajax({
                    type: "POST",
                    url: "{{ route('profile.selfDestroyUser') }}",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "confirmed": "yes"
                    },
                    success: function (result) {
                        Swal.fire("{{ __('Account has been destroyed') }}", '', 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function (result) {
                        Swal.fire("{{ __('Error') }}", "{{ __('Something went wrong.') }}", 'error');
                    }
                });
            } else {
                Swal.fire("{{ __('Account was NOT deleted.') }}", '', 'info');
            }
        }

        function onClickCopy() {
            let textToCopy = document.getElementById('RefLink').innerText;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(textToCopy).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __('URL copied to clipboard') }}',
                        position: 'top-middle',
                        showConfirmButton: false,
                        background: '#343a40',
                        toast: false,
                        timer: 1000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                            toast.addEventListener('click', () => Swal.close())

                        }
                    })
                })
            } else {
                console.log('Browser Not compatible')
            }
        }
    </script>
@endsection
