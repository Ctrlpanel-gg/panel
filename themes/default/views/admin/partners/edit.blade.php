@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-4 sm:p-8">
    <div class="w-full">
        <!-- Header -->
        <div class="glass-panel p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-light text-white mb-2">{{ __('Edit Partner') }}</h1>
                    <nav class="text-zinc-400 text-sm" aria-label="Breadcrumb">
                        <ol class="list-none p-0 inline-flex">
                            <li class="flex items-center">
                                <a href="{{ route('home') }}">{{ __('Dashboard') }}</a>
                                <span class="mx-2">/</span>
                            </li>
                            <li class="flex items-center">
                                <a href="{{ route('admin.partners.index') }}">{{ __('Partners') }}</a>
                                <span class="mx-2">/</span>
                            </li>
                            <li class="flex items-center text-zinc-500">
                                {{ __('Edit') }}
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="text-white flex items-center gap-2">
                        <i class="fas fa-handshake"></i>{{ __('Partner details') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.partners.update', $partner->id) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label for="user_id" class="block text-sm font-medium text-zinc-400 mb-2">
                                {{ __('User') }}
                            </label>
                            <select id="user_id" name="user_id" class="form-select">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" 
                                        @if($partners->contains('user_id', $user->id) && $partner->user_id != $user->id) disabled @endif
                                        @if($partner->user_id == $user->id) selected @endif>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="partner_discount" class="block text-sm font-medium text-zinc-400 mb-2">
                                {{ __('Partner discount') }}
                                <i class="fas fa-info-circle ml-1" data-toggle="popover" data-trigger="hover"
                                   data-content="{{ __('The discount in percent given to the partner at checkout.') }}"></i>
                            </label>
                            <input type="number" step="any" min="0" max="100" id="partner_discount" 
                                   name="partner_discount" class="form-input" 
                                   value="{{ $partner->partner_discount }}"
                                   placeholder="{{ __('Discount in percent') }}">
                            @error('partner_discount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="registered_user_discount" class="block text-sm font-medium text-zinc-400 mb-2">
                                {{ __('Registered user discount') }}
                                <i class="fas fa-info-circle ml-1" data-toggle="popover" data-trigger="hover"
                                   data-content="{{ __('The discount in percent given to all users registered using the partners referral link.') }}"></i>
                            </label>
                            <input type="number" id="registered_user_discount" name="registered_user_discount" 
                                   class="form-input" value="{{ $partner->registered_user_discount }}"
                                   placeholder="{{ __('Discount in percent') }}" required>
                            @error('registered_user_discount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="referral_system_commission" class="block text-sm font-medium text-zinc-400 mb-2">
                                {{ __('Referral system commission') }}
                                <i class="fas fa-info-circle ml-1" data-toggle="popover" data-trigger="hover"
                                   data-content="{{ __('Override value for referral system commission. You can set it to -1 to get the default commission from settings.') }}"></i>
                            </label>
                            <input type="number" step="any" min="-1" max="100" id="referral_system_commission" 
                                   name="referral_system_commission" class="form-input" 
                                   value="{{ $partner->referral_system_commission }}"
                                   placeholder="{{ __('Commission in percent') }}">
                            @error('referral_system_commission')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Submit') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
        document.addEventListener('DOMContentLoaded', (event) => {
            $('#expires_at').datetimepicker({
                format: 'DD-MM-yyyy HH:mm:ss',
                icons: {
                    time: 'far fa-clock',
                    date: 'far fa-calendar',
                    up: 'fas fa-arrow-up',
                    down: 'fas fa-arrow-down',
                    previous: 'fas fa-chevron-left',
                    next: 'fas fa-chevron-right',
                    today: 'fas fa-calendar-check',
                    clear: 'far fa-trash-alt',
                    close: 'far fa-times-circle'
                }
            });
        })
        function setMaxUses() {
            let element = document.getElementById('uses')
            element.value = element.max;
            console.log(element.max)
        }
        function setRandomCode() {
            let element = document.getElementById('code')
            element.value = getRandomCode(36)
        }
        function getRandomCode(length) {
            let result = '';
            let characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-';
            let charactersLength = characters.length;
            for (let i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() *
                    charactersLength));
            }
            return result;
        }
</script>
@endsection