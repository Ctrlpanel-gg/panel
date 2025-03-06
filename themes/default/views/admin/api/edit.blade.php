@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="max-w-screen-2xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Edit API Token') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li><a href="{{ route('admin.api.index') }}" class="hover:text-white transition-colors">{{ __('Application API') }}</a></li>
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
        <div class="glass-panel">
            <div class="p-6 border-b border-zinc-800/50">
                <h5 class="text-lg font-medium text-white">{{__('Edit API Token')}}</h5>
            </div>
            <div class="p-6">
                <form action="{{route('admin.api.update', $applicationApi->token)}}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="max-w-xl">
                        <div class="mb-6">
                            <label for="memo" class="block text-sm font-medium text-zinc-400 mb-2">{{__('Memo')}}</label>
                            <input value="{{$applicationApi->memo}}" id="memo" name="memo" type="text"
                                   class="form-input @error('memo') border-red-500 @enderror"
                                   placeholder="Enter a description for this token">
                            @error('memo')
                            <p class="mt-1 text-sm text-red-500">{{$message}}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary">
                                {{__('Update Token')}}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
