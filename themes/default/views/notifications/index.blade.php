@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="w-full mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Notifications') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Notifications') }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('notifications.readAll') }}" class="btn btn-primary">
                    <i class="fas fa-check mr-2"></i>{{ __('Mark all as read') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-full">
        <div class="glass-panel">
            <div class="p-6">
                <p class="text-white">{{ __('All notifications') }}</p>
                @foreach($notifications as $notification)
                <div class="p-4 mb-4 bg-zinc-800 rounded-lg">
                    <div class="flex justify-between items-center">
                        <a href="{{ route('notifications.show', $notification->id) }}" class="{{ $notification->read() ? 'text-zinc-400' : 'text-white' }} hover:underline">
                            <i class="fas fa-envelope mr-2"></i>{{ $notification->data['title'] }}
                        </a>
                        <small class="text-zinc-500">
                            <i class="fas fa-paper-plane mr-2"></i>{{ $notification->created_at->diffForHumans() }}
                        </small>
                    </div>
                </div>
                @endforeach

                <div class="mt-4">
                    {!! $notifications->links() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
