@extends('layouts.main')

@section('content')
    <div class="min-h-screen bg-primary-950 p-4 sm:p-8">
        <!-- Header -->
        <header class="w-full mb-6 sm:mb-8">
            <div class="glass-panel p-4 sm:p-6">
                <h1 class="text-2xl sm:text-3xl font-light text-white">{{__('Users')}}</h1>
                <div class="text-zinc-400 text-sm mt-2">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{route('home')}}" class="inline-flex items-center text-sm font-medium text-zinc-400 hover:text-white">
                                    <i class="fas fa-home mr-2"></i>
                                    {{__('Dashboard')}}
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="fas fa-chevron-right text-zinc-600 text-xs mx-1"></i>
                                    <a href="{{route('admin.users.index')}}" class="ml-1 text-sm font-medium text-zinc-400 hover:text-white">
                                        {{__('Users')}}
                                    </a>
                                </div>
                            </li>
                            <li aria-current="page">
                                <div class="flex items-center">
                                    <i class="fas fa-chevron-right text-zinc-600 text-xs mx-1"></i>
                                    <span class="ml-1 text-sm font-medium text-zinc-500">
                                        {{__('Edit')}}
                                    </span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="w-full">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- User Information -->
                <div class="card glass-morphism">
                    <div class="p-6 border-b border-zinc-800/50">
                        <h3 class="text-white font-medium flex items-center gap-2">
                            <i class="fas fa-user text-zinc-400"></i>
                            {{__('User Information')}}
                        </h3>
                    </div>
                    <div class="p-6">
                        <form action="{{route('admin.users.update', $user->id)}}" method="POST">
                            @csrf
                            @method('PATCH')
                            
                            <div class="space-y-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-zinc-400 mb-2">{{__('Username')}}</label>
                                    <input value="{{$user->name}}" id="name" name="name" type="text"
                                           class="form-input @error('name') border-red-500 @enderror" required>
                                    @error('name')
                                        <div class="text-red-500 text-sm mt-1">
                                            {{$message}}
                                        </div>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="email" class="block text-sm font-medium text-zinc-400 mb-2">{{__('Email')}}</label>
                                    <input value="{{$user->email}}" id="email" name="email" type="text"
                                           class="form-input @error('email') border-red-500 @enderror" required>
                                    @error('email')
                                        <div class="text-red-500 text-sm mt-1">
                                            {{$message}}
                                        </div>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="pterodactyl_id" class="block text-sm font-medium text-zinc-400 mb-2">{{__('Pterodactyl ID')}}</label>
                                    <input value="{{$user->pterodactyl_id}}" id="pterodactyl_id" name="pterodactyl_id" type="number"
                                           class="form-input @error('pterodactyl_id') border-red-500 @enderror" required>
                                    @error('pterodactyl_id')
                                        <div class="text-red-500 text-sm mt-1">
                                            {{$message}}
                                        </div>
                                    @enderror
                                    <div class="text-zinc-500 text-sm mt-1">
                                        {{__('This ID refers to the user account created on pterodactyls panel.')}} <br>
                                        <small>{{__('Only edit this if you know what youre doing :)')}}</small>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="credits" class="block text-sm font-medium text-zinc-400 mb-2">{{ $credits_display_name }}</label>
                                    <input value="{{$user->credits}}" id="credits" name="credits" step="any" min="0" max="99999999"
                                           type="number" class="form-input @error('credits') border-red-500 @enderror" required>
                                    @error('credits')
                                        <div class="text-red-500 text-sm mt-1">
                                            {{$message}}
                                        </div>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="server_limit" class="block text-sm font-medium text-zinc-400 mb-2">{{__('Server Limit')}}</label>
                                    <input value="{{$user->server_limit}}" id="server_limit" name="server_limit" min="0" max="1000000"
                                           type="number" class="form-input @error('server_limit') border-red-500 @enderror" required>
                                    @error('server_limit')
                                        <div class="text-red-500 text-sm mt-1">
                                            {{$message}}
                                        </div>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="roles" class="block text-sm font-medium text-zinc-400 mb-2">{{__('Role')}}</label>
                                    <select id="roles" name="roles" class="form-select @error('role') border-red-500 @enderror" required>
                                        @foreach($roles as $role)
                                            <option style="color: {{$role->color}}"
                                                    @if(isset($user) && $user->roles->contains($role)) selected @endif 
                                                    value="{{$role->id}}">{{$role->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <div class="text-red-500 text-sm mt-1">
                                            {{$message}}
                                        </div>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="referral_code" class="block text-sm font-medium text-zinc-400 mb-2">{{__('Referral-Code')}}</label>
                                    <input value="{{$user->referral_code}}" id="referral_code" name="referral_code" type="text"
                                           class="form-input @error('referral_code') border-red-500 @enderror" required>
                                    @error('referral_code')
                                        <div class="text-red-500 text-sm mt-1">
                                            {{$message}}
                                        </div>
                                    @enderror
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" class="btn btn-primary">{{__('Submit')}}</button>
                                </div>
                            </div>
                    </div>
                </div>
                
                <!-- Password Reset -->
                <div class="card glass-morphism">
                    <div class="p-6 border-b border-zinc-800/50">
                        <h3 class="text-white font-medium flex items-center gap-2">
                            <i class="fas fa-lock text-zinc-400"></i>
                            {{__('Password Reset')}}
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-6">
                            <div>
                                <label for="new_password" class="block text-sm font-medium text-zinc-400 mb-2">{{__('New Password')}}</label>
                                <input class="form-input @error('new_password') border-red-500 @enderror"
                                       name="new_password" id="new_password" type="password" placeholder="••••••">
                                @error('new_password')
                                    <div class="text-red-500 text-sm mt-1">
                                        {{$message}}
                                    </div>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="new_password_confirmation" class="block text-sm font-medium text-zinc-400 mb-2">{{__('Confirm Password')}}</label>
                                <input class="form-input @error('new_password_confirmation') border-red-500 @enderror"
                                       name="new_password_confirmation" id="new_password_confirmation" type="password"
                                       placeholder="••••••">
                                @error('new_password_confirmation')
                                    <div class="text-red-500 text-sm mt-1">
                                        {{$message}}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
