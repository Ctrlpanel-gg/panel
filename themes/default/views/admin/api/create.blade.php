@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="w-full mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('API Keys') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li><a href="{{ route('admin.api.index') }}" class="hover:text-white transition-colors">{{ __('API Keys') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Create') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-full max-w-3xl mx-auto">
        <div class="glass-panel overflow-hidden">
            <div class="p-6 border-b border-zinc-800/50">
                <h3 class="text-lg font-medium text-white flex items-center gap-2">
                    <i class="fas fa-key text-zinc-400"></i>
                    {{ __('Create New API Key') }}
                </h3>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.api.store') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label for="memo" class="block text-sm font-medium text-zinc-400 mb-2">
                            {{ __('Memo/Description') }}
                            <span class="text-xs text-zinc-500 ml-1">({{ __('optional') }})</span>
                        </label>
                        <input type="text" 
                               id="memo" 
                               name="memo" 
                               value="{{ old('memo') }}"
                               placeholder="{{ __('e.g. Production Server, Development Environment') }}"
                               class="input @error('memo') border-red-500/50 focus:border-red-500/50 @enderror">
                        @error('memo')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="bg-zinc-900/30 p-4 rounded-lg border border-zinc-800/50 mb-6">
                        <div class="flex items-start gap-3">
                            <div class="text-blue-400 mt-0.5">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="text-sm text-zinc-400">
                                {{ __('Adding a descriptive memo helps you identify what this API key is used for.') }}
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('admin.api.index') }}" class="btn bg-zinc-800 text-zinc-300 hover:bg-zinc-700">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            {{ __('Create API Key') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
