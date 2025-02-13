@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <header class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <h1 class="text-3xl font-light text-white">{{ __('Create API Token') }}</h1>
            <div class="text-zinc-400 text-sm mt-2">
                <a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a>
                <span class="px-2">›</span>
                <a href="{{ route('admin.api.index') }}" class="hover:text-white transition-colors">{{ __('Application API') }}</a>
                <span class="px-2">›</span>
                <span>{{ __('Create') }}</span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-xl mx-auto">
        <div class="max-w-2xl">
            <div class="card glass-morphism">
                <div class="p-6">
                    <form action="{{ route('admin.api.store') }}" method="POST">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <label for="memo" class="block text-sm font-medium text-zinc-400">{{ __('Memo') }}</label>
                                <input type="text" 
                                       id="memo" 
                                       name="memo" 
                                       value="{{ old('memo') }}"
                                       class="mt-1 block w-full rounded-lg bg-zinc-800/50 border-zinc-700/50 text-zinc-300 focus:ring-blue-500 focus:border-blue-500">
                                @error('memo')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" 
                                        class="px-4 py-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg border border-blue-500/20 transition-colors">
                                    {{ __('Submit') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
