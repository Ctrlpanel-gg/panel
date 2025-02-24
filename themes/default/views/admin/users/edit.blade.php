@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="max-w-screen-2xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Edit User') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li><a href="{{ route('admin.users.index') }}" class="hover:text-white transition-colors">{{ __('Users') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Edit') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- User Details Form -->
            <div class="glass-panel">
                <div class="p-6 border-b border-zinc-800/50">
                    <h5 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-user-edit mr-2 text-zinc-400"></i>
                        {{__('User Details')}}
                    </h5>
                </div>
                <div class="p-6">
                    <form action="{{route('admin.users.update', $user->id)}}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="form-group">
                            <label for="name">{{__('Username')}}</label>
                            <input value="{{$user->name}}" id="name" name="name" type="text"
                                   class="form-control @error('name') is-invalid @enderror" required="required">
                            @error('name')
                            <div class="invalid-feedback">
                                {{$message}}
                            </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="email">{{__('Email')}}</label>
                            <input value="{{$user->email}}" id="email" name="email" type="text"
                                   class="form-control @error('email') is-invalid @enderror"
                                   required="required">
                            @error('email')
                            <div class="invalid-feedback">
                                {{$message}}
                            </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="pterodactyl_id">{{__('Pterodactyl ID')}}</label>
                            <input value="{{$user->pterodactyl_id}}" id="pterodactyl_id" name="pterodactyl_id"
                                   type="number"
                                   class="form-control @error('pterodactyl_id') is-invalid @enderror"
                                   required="required">
                            @error('pterodactyl_id')
                            <div class="invalid-feedback">
                                {{$message}}
                            </div>
                            @enderror
                            <div class="text-muted">
                                {{__('This ID refers to the user account created on pterodactyls panel.')}} <br>
                                <small>{{__('Only edit this if you know what youre doing :)')}}</small>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="credits">{{ $credits_display_name }}</label>
                            <input value="{{$user->credits}}" id="credits" name="credits" step="any" min="0"
                                   max="99999999"
                                   type="number" class="form-control @error('credits') is-invalid @enderror"
                                   required="required">
                            @error('credits')
                            <div class="invalid-feedback">
                                {{$message}}
                            </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="server_limit">{{__('Server Limit')}}</label>
                            <input value="{{$user->server_limit}}" id="server_limit" name="server_limit" min="0"
                                   max="1000000"
                                   type="number"
                                   class="form-control @error('server_limit') is-invalid @enderror"
                                   required="required">
                            @error('server_limit')
                            <div class="invalid-feedback">
                                {{$message}}
                            </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="role">{{__('Role')}}</label>
                            <div>
                                <select id="roles" name="roles"
                                        class="custom-select @error('role') is-invalid @enderror"
                                        required="required">
                                    @foreach($roles as $role)
                                        <option style="color: {{$role->color}}"
                                                @if(isset($user) && $user->roles->contains($role)) selected
                                                @endif value="{{$role->id}}">{{$role->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name">{{__('Referral-Code')}}</label>
                            <input value="{{$user->referral_code}}" id="referral_code" name="referral_code" type="text"
                                   class="form-control @error('referral_code') is-invalid @enderror" required="required">
                            @error('referral_code')
                            <div class="invalid-feedback">
                                {{$message}}
                            </div>
                            @enderror
                        </div>
                        @error('role')
                        <div class="text-danger">
                            {{$message}}
                        </div>
                        @enderror

                        <div class="mt-4 flex justify-end">
                            <button type="submit" class="btn btn-primary">{{__('Save Changes')}}</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Password Form -->
            <div class="glass-panel">
                <div class="p-6 border-b border-zinc-800/50">
                    <h5 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-key mr-2 text-zinc-400"></i>
                        {{__('Change Password')}}
                    </h5>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="form-group"><label>{{__('New Password')}}</label> <input
                                class="form-control @error('new_password') is-invalid @enderror"
                                name="new_password" id="new_password" type="password" placeholder="••••••">

                            @error('new_password')
                            <div class="invalid-feedback">
                                {{$message}}
                            </div>
                            @enderror
                        </div>
                        <div class="form-group"><label>{{__('Confirm Password')}}</label>
                            <input
                                class="form-control @error('new_password_confirmation') is-invalid @enderror"
                                name="new_password_confirmation" id="new_password_confirmation" type="password"
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
        </div>
    </div>
</div>
@endsection
