@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="mb-8">
        <div class="container">
            <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                <div>
                    <h1 class="h1">{{__('Notifications')}}</h1>
                </div>
                <div>
                    <nav class="flex items-center space-x-2 text-sm text-zinc-500">
                        <a href="{{route('home')}}" class="hover:text-primary-400 transition-colors">{{__('Dashboard')}}</a>
                        <span class="text-zinc-600">/</span>
                        <span class="text-zinc-400">{{__('Notifications')}}</span>
                    </nav>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container">

            <!-- CUSTOM CONTENT -->
            <div class="flex justify-center">
                <div class="w-full max-w-4xl">
                    <div class="flex items-center justify-between mb-6">
                        <p class="body-1 text-zinc-300">{{__('All notifications')}}</p>
                        <a href="{{route('notifications.readAll')}}">
                            <button class="btn btn-secondary btn-sm">
                                <i class="fas fa-check-double mr-2"></i>
                                {{__('Mark all as read')}}
                            </button>
                        </a>
                    </div>

                    <div class="space-y-4">
                        @foreach($notifications as $notification)
                            <div class="card hover:border-primary-600/30 transition-all duration-200">
                                <div class="card-header border-b border-zinc-800/50">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-2 h-2 {{ $notification->read() ? 'bg-zinc-600' : 'bg-primary-400' }} rounded-full"></div>
                                            </div>
                                            <a 
                                                class="{{ $notification->read() ? 'text-zinc-400' : 'text-zinc-200' }} hover:text-primary-400 transition-colors flex items-center"
                                                href="{{route('notifications.show', $notification->id)}}"
                                            >
                                                <i class="fas fa-envelope mr-2"></i>
                                                {{ $notification->data['title'] }}
                                            </a>
                                        </div>
                                        <div class="text-zinc-500">
                                            <small class="flex items-center">
                                                <i class="fas fa-paper-plane mr-2"></i>
                                                {{ $notification->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Pagination -->
                                    <div class="mt-8 flex justify-center">
                                        {!! $notifications->links() !!}
                                    </div>
                                </div>
                            </div>

                            <!-- END CUSTOM CONTENT -->

                        </div>
                    </section>
                    <!-- END CONTENT -->

                @endsection
