@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="max-w-screen-2xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Servers') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Servers') }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.servers.sync') }}" class="btn btn-primary">
                    <i class="fas fa-sync mr-2"></i>{{ __('Sync') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto">
        <div class="glass-panel">
            <div class="p-6 border-b border-zinc-800/50">
                <h3 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-server mr-2 text-zinc-400"></i>
                    {{ __('Server Management') }}
                </h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    @include('admin.servers.table')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
