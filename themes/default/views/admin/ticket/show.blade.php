@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <header class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <h1 class="text-3xl font-light text-white">{{ __('Ticket') }} #{{ $ticket->ticket_id }}</h1>
            <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-zinc-400">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li><a href="{{ route('admin.ticket.index') }}" class="hover:text-white transition-colors">{{ __('Tickets') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li class="text-zinc-500">#{{ $ticket->ticket_id }}</li>
                </ol>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-xl mx-auto space-y-8">
        <!-- Ticket Information Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-white font-medium flex items-center gap-2">
                    <i class="fas fa-ticket-alt text-zinc-400"></i>
                    {{ __('Ticket Information') }}
                </h3>
            </div>
            <div class="p-6 space-y-4">
                @if(!empty($server))
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-zinc-400 w-24">{{ __('Server') }}:</span>
                        <a href="{{ $pterodactyl_url . '/admin/servers/view/' . $server->pterodactyl_id }}" 
                           class="text-blue-400 hover:text-blue-300 transition-colors" 
                           target="_blank">{{ $server->name }}</a>
                    </div>
                @endif

                <div class="flex items-center gap-2 text-sm">
                    <span class="text-zinc-400 w-24">{{ __('Title') }}:</span>
                    <span class="text-zinc-300">{{ $ticket->title }}</span>
                </div>

                <div class="flex items-center gap-2 text-sm">
                    <span class="text-zinc-400 w-24">{{ __('Category') }}:</span>
                    <span class="text-zinc-300">{{ $ticketcategory->name }}</span>
                </div>

                <div class="flex items-center gap-2 text-sm">
                    <span class="text-zinc-400 w-24">{{ __('Status') }}:</span>
                    @switch($ticket->status)
                        @case('Open')
                            <span class="px-2 py-1 rounded text-xs bg-emerald-500/10 text-emerald-400">{{ __('Open') }}</span>
                            @break
                        @case('Reopened')
                            <span class="px-2 py-1 rounded text-xs bg-emerald-500/10 text-emerald-400">{{ __('Reopened') }}</span>
                            @break
                        @case('Closed')
                            <span class="px-2 py-1 rounded text-xs bg-red-500/10 text-red-400">{{ __('Closed') }}</span>
                            @break
                        @case('Answered')
                            <span class="px-2 py-1 rounded text-xs bg-blue-500/10 text-blue-400">{{ __('Answered') }}</span>
                            @break
                        @case('Client Reply')
                            <span class="px-2 py-1 rounded text-xs bg-amber-500/10 text-amber-400">{{ __('Client Reply') }}</span>
                            @break
                    @endswitch
                </div>

                <div class="flex items-center gap-2 text-sm">
                    <span class="text-zinc-400 w-24">{{ __('Priority') }}:</span>
                    @switch($ticket->priority)
                        @case('Low')
                            <span class="px-2 py-1 rounded text-xs bg-emerald-500/10 text-emerald-400">{{ __('Low') }}</span>
                            @break
                        @case('Medium')
                            <span class="px-2 py-1 rounded text-xs bg-amber-500/10 text-amber-400">{{ __('Medium') }}</span>
                            @break
                        @case('High')
                            <span class="px-2 py-1 rounded text-xs bg-red-500/10 text-red-400">{{ __('High') }}</span>
                            @break
                    @endswitch
                </div>

                <div class="flex items-center gap-2 text-sm">
                    <span class="text-zinc-400 w-24">{{ __('Created') }}:</span>
                    <span class="text-zinc-300">{{ $ticket->created_at->diffForHumans() }}</span>
                </div>

                <div class="flex gap-2 mt-6">
                    @if($ticket->status == 'Closed')
                        <form class="inline" method="post" action="{{ route('admin.ticket.changeStatus', ['ticket_id' => $ticket->ticket_id]) }}">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-redo mr-2"></i>{{ __('Reopen') }}
                            </button>
                        </form>
                    @else
                        <form class="inline" method="post" action="{{ route('admin.ticket.changeStatus', ['ticket_id' => $ticket->ticket_id]) }}">
                            @csrf
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-times mr-2"></i>{{ __('Close') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Messages Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-white font-medium flex items-center gap-2">
                    <i class="fas fa-comments text-zinc-400"></i>
                    {{ __('Messages') }}
                </h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- Original Message -->
                <div class="bg-zinc-800/50 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center gap-2">
                            <img src="https://www.gravatar.com/avatar/{{ md5(strtolower($ticket->user->email)) }}?s=40" 
                                 class="rounded-full w-10 h-10" alt="User Image">
                            <div>
                                <a href="/admin/users/{{$ticket->user->id}}" class="text-white hover:text-zinc-200 font-medium">
                                    {{ $ticket->user->name }}
                                </a>
                                <div class="flex gap-1 mt-1">
                                    @foreach ($ticket->user->roles as $role)
                                        <span class="px-2 py-0.5 rounded text-xs" style="background-color: {{ $role->color }}20; color: {{ $role->color }}">
                                            {{$role->name}}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <span class="text-xs text-zinc-500">{{ $ticket->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="text-zinc-300 whitespace-pre-wrap">{{ $ticket->message }}</div>
                </div>

                <!-- Comments -->
                @foreach ($ticketcomments as $ticketcomment)
                    <div class="bg-zinc-800/50 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center gap-2">
                                <img src="https://www.gravatar.com/avatar/{{ md5(strtolower($ticketcomment->user->email)) }}?s=40" 
                                     class="rounded-full w-10 h-10" alt="User Image">
                                <div>
                                    <a href="/admin/users/{{$ticketcomment->user->id}}" class="text-white hover:text-zinc-200 font-medium">
                                        {{ $ticketcomment->user->name }}
                                    </a>
                                    <div class="flex gap-1 mt-1">
                                        @foreach ($ticketcomment->user->roles as $role)
                                            <span class="px-2 py-0.5 rounded text-xs" style="background-color: {{ $role->color }}20; color: {{ $role->color }}">
                                                {{$role->name}}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <span class="text-xs text-zinc-500">{{ $ticketcomment->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-zinc-300 whitespace-pre-wrap">{{ $ticketcomment->ticketcomment }}</div>
                    </div>
                @endforeach

                <!-- Reply Form -->
                <form action="{{ route('admin.ticket.reply')}}" method="POST" class="mt-6">
                    @csrf
                    <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                    <div class="mb-4">
                        <textarea rows="6" 
                                id="ticketcomment" 
                                name="ticketcomment"
                                class="w-full bg-zinc-800/50 border-zinc-700 rounded-lg text-zinc-300 placeholder-zinc-500 focus:border-blue-500 focus:ring-blue-500"
                                placeholder="{{ __('Write your reply...') }}"></textarea>
                        @error('ticketcomment')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane mr-2"></i>{{ __('Send Reply') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

