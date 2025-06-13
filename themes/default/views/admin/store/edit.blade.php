@extends('layouts.main')

@section('content')
    <div class="min-h-screen bg-primary-950 p-8">
        <!-- Header -->
        <div class="w-full mb-8">
            <div class="glass-panel p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-light text-white">{{ __('Store') }}</h1>
                        <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 text-zinc-400">
                                <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                                <li class="text-zinc-600">/</li>
                                <li><a href="{{ route('admin.store.index') }}" class="hover:text-white transition-colors">{{ __('Store') }}</a></li>
                                <li class="text-zinc-600">/</li>
                                <li class="text-zinc-500">{{ __('Edit') }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="w-full">
            <div class="glass-panel p-6">
                <h3 class="text-xl font-medium text-white mb-6">
                    <i class="fas fa-edit mr-2"></i>{{ __('Edit Store Item') }}
                </h3>
                
                <form action="{{ route('admin.store.update', $shopProduct->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="flex justify-end mb-4">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" @if ($shopProduct->disabled) checked @endif name="disabled"
                                class="custom-control-input custom-control-input-danger" id="switch1">
                            <label class="custom-control-label text-zinc-300" for="switch1">{{ __('Disabled') }} 
                                <i data-toggle="popover" data-trigger="hover"
                                   data-content="{{ __('Will hide this option from being selected') }}"
                                   class="fas fa-info-circle text-zinc-500"></i>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="type" class="text-zinc-300 mb-2 block">{{ __('Type') }}</label>
                            <select required name="type" id="type"
                                class="form-select bg-zinc-800/50 border-zinc-700 text-white rounded-lg w-full @error('name') is-invalid @enderror">
                                <option @if ($shopProduct->type == 'credits') selected @endif value="Credits">{{ $credits_display_name }}</option>
                                <option @if ($shopProduct->type == 'Server slots') selected @endif value="Server slots">{{__("Server Slots")}}</option>
                            </select>
                            @error('name')
                                <div class="text-red-500 mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="currency_code" class="text-zinc-300 mb-2 block">{{ __('Currency code') }}</label>
                            <select required name="currency_code" id="currency_code"
                                class="form-select bg-zinc-800/50 border-zinc-700 text-white rounded-lg w-full @error('name') is-invalid @enderror">
                                @foreach ($currencyCodes as $code)
                                    <option @if ($shopProduct->currency_code == $code) selected @endif value="{{ $code }}">
                                        {{ $code }}</option>
                                @endforeach
                            </select>
                            @error('currency_code')
                                <div class="text-red-500 mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="text-zinc-500 mt-1 text-sm">
                                {{ __('Checkout the paypal docs to select the appropriate code') }} 
                                <a target="_blank" class="text-blue-400 hover:underline"
                                   href="https://developer.paypal.com/docs/api/reference/currency-codes/">{{ __('Link') }}</a>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="price" class="text-zinc-300 mb-2 block">{{ __('Price') }}</label>
                            <input value="{{ $shopProduct->price }}" id="price" name="price" type="number"
                                placeholder="10.00" step="any"
                                class="form-input bg-zinc-800/50 border-zinc-700 text-white rounded-lg w-full @error('price') is-invalid @enderror" required="required">
                            @error('price')
                                <div class="text-red-500 mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="quantity" class="text-zinc-300 mb-2 block">{{ __('Quantity') }}</label>
                            <input value="{{ $shopProduct->quantity }}" id="quantity" name="quantity"
                                type="number" placeholder="1000"
                                class="form-input bg-zinc-800/50 border-zinc-700 text-white rounded-lg w-full @error('quantity') is-invalid @enderror" required="required">
                            @error('quantity')
                                <div class="text-red-500 mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="text-zinc-500 mt-1 text-sm">
                                {{ __('Amount given to the user after purchasing') }}
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="display" class="text-zinc-300 mb-2 block">{{ __('Display') }}</label>
                            <input value="{{ $shopProduct->display }}" id="display" name="display" type="text"
                                placeholder="750 + 250" 
                                class="form-input bg-zinc-800/50 border-zinc-700 text-white rounded-lg w-full @error('display') is-invalid @enderror" required="required">
                            @error('display')
                                <div class="text-red-500 mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="text-zinc-500 mt-1 text-sm">
                                {{ __('This is what the user sees at store and checkout') }}
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description" class="text-zinc-300 mb-2 block">{{ __('Description') }}</label>
                            <input value="{{ $shopProduct->description }}" id="description" name="description"
                                type="text" placeholder="{{ __('Adds 1000 credits to your account') }}"
                                class="form-input bg-zinc-800/50 border-zinc-700 text-white rounded-lg w-full @error('description') is-invalid @enderror" required="required">
                            @error('description')
                                <div class="text-red-500 mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="text-zinc-500 mt-1 text-sm">
                                {{ __('This is what the user sees at checkout') }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 text-right">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Submit') }}
                        </button>
                    </div>

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                </form>
            </div>
        </div>
    </div>
@endsection
