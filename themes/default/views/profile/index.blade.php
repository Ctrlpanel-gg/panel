@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <header class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <h1 class="text-3xl font-light text-white">{{ __('Profile') }}</h1>
            <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-zinc-400">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li class="text-zinc-500">{{ __('Profile') }}</li>
                </ol>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-xl mx-auto">
        <!-- Verification Alerts -->
        @if (!Auth::user()->hasVerifiedEmail() && $force_email_verification)
            <div class="card mb-4">
                <div class="p-4 bg-amber-500/10 text-amber-400 rounded-lg border border-amber-500/20">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-exclamation-circle"></i>
                        <h4 class="font-medium">{{ __('Required Email verification!') }}</h4>
                    </div>
                    <p>
                        {{ __('You have not yet verified your email address') }}
                        <a href="{{ route('verification.send') }}" class="text-amber-300 hover:text-amber-200">
                            {{ __('Click here to resend verification email') }}
                        </a>
                    </p>
                    <p class="mt-2">{{ __('Please contact support If you didnt receive your verification email.') }}</p>
                </div>
            </div>
        @endif

        @if (is_null(Auth::user()->discordUser) && $force_discord_verification)
            @if (!empty($discord_client_id) && !empty($discord_client_secret))
                <div class="card mb-4">
                    <div class="p-4 bg-amber-500/10 text-amber-400 rounded-lg border border-amber-500/20">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-exclamation-circle"></i>
                            <h4 class="font-medium">{{ __('Required Discord verification!') }}</h4>
                        </div>
                        <p>
                            {{ __('You have not yet verified your discord account') }}
                            <a href="{{ route('auth.redirect') }}" class="text-amber-300 hover:text-amber-200">
                                {{ __('Login with discord') }}
                            </a>
                        </p>
                        <p class="mt-2">{{ __('Please contact support If you face any issues.') }}</p>
                    </div>
                </div>
            @endif
        @endif

        <!-- Profile Form -->
        <form action="{{ route('profile.update', Auth::user()->id) }}" method="post">
            @csrf
            @method('PATCH')
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Profile Info Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-white font-medium flex items-center gap-2">
                            <i class="fas fa-user text-zinc-400"></i>
                            {{ __('Profile Information') }}
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <!-- Avatar -->
                        <div class="flex items-center gap-6">
                            <div class="relative">
                                <img src="{{ $user->getAvatar() }}" alt="avatar" 
                                     class="w-24 h-24 rounded-full border-2 border-zinc-700">
                                <button type="button" 
                                        class="absolute bottom-0 right-0 bg-primary-600 p-2 rounded-full hover:bg-primary-500 transition-colors">
                                    <i class="fas fa-camera text-white text-sm"></i>
                                </button>
                            </div>
                            <div>
                                <h4 class="text-white text-lg font-medium">{{ $user->name }}</h4>
                                <div class="flex items-center gap-2 text-zinc-400">
                                    <span>{{ $user->email }}</span>
                                    @if ($user->hasVerifiedEmail())
                                        <i class="fas fa-check-circle text-green-500" title="{{ __('Verified') }}"></i>
                                    @else
                                        <i class="fas fa-exclamation-circle text-red-500" title="{{ __('Not verified') }}"></i>
                                    @endif
                                </div>
                                <div class="mt-2">
                                    <span class="px-3 py-1 bg-primary-600/50 rounded-full text-sm text-white">
                                        <i class="fa fa-coins mr-2"></i>{{ $user->Credits() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Name & Email Fields -->
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-zinc-400 mb-2">{{ __('Name') }}</label>
                                <input type="text" id="name" name="name" 
                                       class="input @error('name') border-red-500/50 @enderror"
                                       value="{{ $user->name }}">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-zinc-400 mb-2">{{ __('Email') }}</label>
                                <input type="email" id="email" name="email" 
                                       class="input @error('email') border-red-500/50 @enderror"
                                       value="{{ $user->email }}">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Password & Discord Card -->
                <div class="space-y-8">
                    <!-- Password Change -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-white font-medium flex items-center gap-2">
                                <i class="fas fa-lock text-zinc-400"></i>
                                {{ __('Change Password') }}
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-zinc-400 mb-2">
                                    {{ __('Current Password') }}
                                </label>
                                <input type="password" id="current_password" name="current_password" 
                                       class="input @error('current_password') border-red-500/50 @enderror">
                                @error('current_password')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="new_password" class="block text-sm font-medium text-zinc-400 mb-2">
                                    {{ __('New Password') }}
                                </label>
                                <input type="password" id="new_password" name="new_password" 
                                       class="input @error('new_password') border-red-500/50 @enderror">
                                @error('new_password')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="new_password_confirmation" class="block text-sm font-medium text-zinc-400 mb-2">
                                    {{ __('Confirm Password') }}
                                </label>
                                <input type="password" id="new_password_confirmation" name="new_password_confirmation" 
                                       class="input @error('new_password_confirmation') border-red-500/50 @enderror">
                            </div>
                        </div>
                    </div>

                    <!-- Discord Integration -->
                    @if (!empty($discord_client_id) && !empty($discord_client_secret))
                        <div class="card">
                            <div class="card-header">
                                <h3 class="text-white font-medium flex items-center gap-2">
                                    <i class="fab fa-discord text-zinc-400"></i>
                                    {{ __('Discord Integration') }}
                                </h3>
                            </div>
                            <div class="p-6">
                                @if (is_null(Auth::user()->discordUser))
                                    <div class="text-center">
                                        @if ($credits_reward_after_verify_discord)
                                            <p class="text-zinc-400 mb-4">
                                                {{ __('By verifying your discord account, you receive extra Credits and increased Server amounts') }}
                                            </p>
                                        @endif
                                        <a href="{{ route('auth.redirect') }}" 
                                           class="btn bg-[#5865F2] hover:bg-[#4752C4] transition-colors">
                                            <i class="fab fa-discord mr-2"></i>
                                            {{ __('Login with Discord') }}
                                        </a>
                                    </div>
                                @else
                                    <div class="flex items-center justify-between p-4 bg-zinc-800/50 rounded-lg">
                                        <div class="flex items-center gap-4">
                                            <img src="{{ $user->discordUser->getAvatar() }}" 
                                                 class="w-12 h-12 rounded-full" alt="Discord Avatar">
                                            <div>
                                                <h4 class="text-white">{{ $user->discordUser->username }}</h4>
                                                <p class="text-zinc-400 text-sm">{{ $user->discordUser->id }}</p>
                                            </div>
                                        </div>
                                        <a href="{{ route('auth.redirect') }}" 
                                           class="btn bg-zinc-700 hover:bg-zinc-600 transition-colors">
                                            <i class="fab fa-discord mr-2"></i>
                                            {{ __('Re-Sync') }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between mt-8">
                <button type="button" id="confirmDeleteButton" 
                        class="btn bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-colors">
                    {{ __('Delete Account') }}
                </button>
                <button type="submit" class="btn btn-primary">
                    {{ __('Save Changes') }}
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Keep existing JavaScript -->
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
