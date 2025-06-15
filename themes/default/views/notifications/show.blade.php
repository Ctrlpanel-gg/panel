@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="mb-8">
        <div class="container">
            <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                <div>
                    <h1 class="h1">Notifications</h1>
                </div>
                <div>
                    <nav class="flex items-center space-x-2 text-sm text-zinc-500">
                        <a href="{{route('home')}}" class="hover:text-primary-400 transition-colors">Dashboard</a>
                        <span class="text-zinc-600">/</span>
                        <a href="{{route('notifications.index')}}" class="hover:text-primary-400 transition-colors">Notifications</a>
                        <span class="text-zinc-600">/</span>
                        <span class="text-zinc-400">Show</span>
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
                    <div class="card">
                        <div class="card-header border-b border-zinc-800/50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-2 h-2 bg-primary-400 rounded-full"></div>
                                    <h2 class="h3">{{ $notification->data['title'] }}</h2>
                                </div>
                                <div class="text-zinc-500">
                                    <small class="flex items-center">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="card-body prose prose-invert max-w-none">
                           {!! $notification->data['content'] !!}
                        </div>
                    </div>
                </div>
            </div>
            <!-- END CUSTOM CONTENT -->

        </div>
    </section>
    <!-- END CONTENT -->

@endsection
