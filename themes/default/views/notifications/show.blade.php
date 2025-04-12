@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="w-full mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Notification Details') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li><a href="{{ route('notifications.index') }}" class="hover:text-white transition-colors">{{ __('Notifications') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Show') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-full">
        <div class="glass-panel">
            <div class="p-6">
                <div class="p-4 bg-zinc-800 rounded-lg">
                    <div class="flex justify-between items-center mb-4">
                        <h5 class="text-lg font-medium text-white">{{ $notification->data['title'] }}</h5>
                        <small class="text-zinc-500">
                            <i class="fas fa-paper-plane mr-2"></i>{{ $notification->created_at->diffForHumans() }}
                        </small>
                    </div>
                    <div class="text-white">
                        {!! $notification->data['content'] !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
