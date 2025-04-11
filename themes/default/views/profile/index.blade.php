@extends('layouts.main')

@section('content')
    <div class="min-h-screen bg-primary-950 p-8">
        <!-- Header -->
        <div class="w-full mb-8">
            <div class="glass-panel p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-light text-white">{{__('Profile')}}</h1>
                        <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 text-zinc-400">
                                <li><a href="{{route('home')}}" class="hover:text-white transition-colors">{{__('Dashboard')}}</a></li>
                                <li class="text-zinc-600">/</li>
                                <li class="text-zinc-500">{{__('Profile')}}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @if (!Auth::user()->hasVerifiedEmail() && $force_email_verification)
            <div class="w-full mb-4">
                <div class="glass-panel p-4 bg-amber-500/10 border-amber-500/20">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-amber-500 text-xl"></i>
                        <div>
                            <h5 class="text-amber-500 font-medium">{{ __('Required Email verification!') }}</h5>
                            <p class="text-amber-300">
                                {{ __('You have not yet verified your email address') }}
                                <a href="{{ route('verification.send') }}" class="text-amber-200 hover:text-amber-100">{{ __('Click here to resend verification email') }}</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Profile -->
        <div class="w-full">
            <form action="{{ route('profile.update', Auth::user()->id) }}" method="post">
                @csrf
                @method('PATCH')
                
                <div class="glass-panel p-6">
                    <!-- Profile Header -->
                    <div class="flex flex-col md:flex-row gap-6 mb-8">
                        <div class="flex-shrink-0">
                            <div class="slim border border-zinc-700/50 rounded-full"
                                 data-label="Change your avatar" 
                                 data-max-file-size="3"
                                 data-save-initial-image="true"
                                 style="width: 128px; height: 128px; cursor: pointer"
                                 data-size="128,128">
                                <img src="{{ $user->getAvatar() }}" alt="avatar" class="w-full h-full object-cover">
                            </div>
                        </div>
                        
                        <div class="flex-grow">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h2 class="text-2xl font-light text-white mb-2">{{ $user->name }}</h2>
                                    <p class="text-zinc-400 flex items-center gap-2">
                                        {{ $user->email }}
                                        @if ($user->hasVerifiedEmail())
                                            <i class="fas fa-check-circle text-emerald-500" data-toggle="popover" data-trigger="hover" data-content="Verified"></i>
                                        @else
                                            <i class="fas fa-exclamation-circle text-red-500" data-toggle="popover" data-trigger="hover" data-content="Not verified"></i>
                                        @endif
                                    </p>
                                    <div class="mt-3 flex gap-2">
                                        <span class="badge info">
                                            <i class="fa fa-coins mr-1"></i>{{ $user->Credits() }}
                                        </span>
                                        @foreach ($user->roles as $role)
                                            <span class="badge" style="background-color: {{$role->color}}20; color: {{$role->color}}">
                                                {{$role->name}}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                                
                                <div class="text-right text-sm text-zinc-500">
                                    <p>{{ __('Joined') }}: {{ $user->created_at->isoFormat('LL') }}</p>
                                    <button type="button" id="confirmDeleteButton" class="mt-2 text-red-400 hover:text-red-300 text-xs">
                                        {{ __('Permanently delete my account') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Form -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <div class="form-group">
                                <label class="block text-sm font-medium text-zinc-400 mb-1">{{__('Name')}}</label>
                                <input class="form-input @error('name') border-red-500/50 @enderror" type="text" name="name" value="{{ $user->name }}">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="block text-sm font-medium text-zinc-400 mb-1">{{__('Email')}}</label>
                                <input class="form-input @error('email') border-red-500/50 @enderror" type="email" name="email" value="{{ $user->email }}">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Password Section -->
                            <div class="pt-4">
                                <h3 class="text-lg font-medium text-white mb-4">{{ __('Change Password') }}</h3>
                                <div class="space-y-4">
                                    <div class="form-group">
                                        <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Current Password') }}</label>
                                        <input class="form-input @error('current_password') border-red-500/50 @enderror" type="password" name="current_password">
                                        @error('current_password')
                                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('New Password') }}</label>
                                        <input class="form-input @error('new_password') border-red-500/50 @enderror" type="password" name="new_password">
                                        @error('new_password')
                                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Confirm Password') }}</label>
                                        <input class="form-input" type="password" name="new_password_confirmation">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Discord Section -->
                        @if (!empty($discord_client_id) && !empty($discord_client_secret))
                            <div class="space-y-4">
                                @if (is_null(Auth::user()->discordUser))
                                    <div class="glass-panel p-6 bg-primary-900/50">
                                        <h3 class="text-lg font-medium text-white mb-4">{{ __('Link Discord Account') }}</h3>
                                        @if ($credits_reward_after_verify_discord)
                                            <p class="text-zinc-400 mb-4">{{ __('By verifying your discord account, you receive extra Credits and increased Server amounts') }}</p>
                                        @endif
                                        <a href="{{ route('auth.redirect') }}" class="btn btn-primary inline-flex items-center">
                                            <i class="fab fa-discord mr-2"></i>
                                            {{ __('Login with Discord') }}
                                        </a>
                                    </div>
                                @else
                                    <div class="glass-panel p-6 bg-primary-900/50">
                                        <div class="flex items-center gap-4">
                                            <img src="{{ $user->discordUser->getAvatar() }}" alt="Discord Avatar" class="w-16 h-16 rounded-full">
                                            <div>
                                                <h3 class="text-lg font-medium text-white">{{ $user->discordUser->username }}</h3>
                                                <p class="text-zinc-400">{{ $user->discordUser->id }}</p>
                                                <span class="badge success mt-2">{{ __('Verified') }}</span>
                                            </div>
                                        </div>
                                        <a href="{{ route('auth.redirect') }}" class="btn btn-primary mt-4 text-sm">
                                            <i class="fab fa-discord mr-2"></i>{{ __('Re-Sync Discord') }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Save Button -->
                    <div class="mt-6 text-right">
                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById("confirmDeleteButton").onclick=async ()=>{
                const {value: enterConfirm} = await Swal.fire({
                    input: 'text',
                    inputLabel: '{{__("Are you sure you want to permanently delete your account and all of your servers?")}} \n Type "{{__('Delete my account')}}" in the Box below',
                    inputPlaceholder: "{{__('Delete my account')}}",
                    showCancelButton: true
                })
                if (enterConfirm === "{{__('Delete my account')}}") {
                    Swal.fire("{{__('Account has been destroyed')}}", '', 'error')
                    $.ajax({
                        type: "POST",
                        url: "{{route("profile.selfDestroyUser")}}",
                        data: `{
                        "confirmed": "yes",
                      }`,
                        success: function (result) {
                            console.log(result);
                        },
                        dataType: "json"
                    });
                    location.reload();

                } else {
                    Swal.fire("{{__('Account was NOT deleted.')}}", '', 'info')

                }

            }
        function onClickCopy() {
            let textToCopy = document.getElementById('RefLink').innerText;
            if(navigator.clipboard) {
                navigator.clipboard.writeText(textToCopy).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __("URL copied to clipboard")}}',
                        position: 'top-middle',
                        showConfirmButton: false,
                        background: '#343a40',
                        toast: false,
                        timer: 1000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    })
                })
            } else {
                console.log('Browser Not compatible')
            }
        }
    </script>
@endsection
