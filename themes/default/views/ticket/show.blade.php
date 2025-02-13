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
                    <li><a href="{{ route('ticket.index') }}" class="hover:text-white transition-colors">{{ __('Tickets') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li class="text-zinc-500">#{{ $ticket->ticket_id }}</li>
                </ol>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-xl mx-auto space-y-8">
        <!-- Ticket Info Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="text-white font-medium flex items-center gap-2">
                    <i class="fas fa-info-circle text-zinc-400"></i>
                    {{ __('Ticket Information') }}
                </h5>
            </div>
            <div class="card-body space-y-4">
                @if(!empty($server))
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-zinc-400 font-medium">{{__("Server")}}:</span>
                        <a href="{{ $pterodactyl_url }}/server/{{ $server->identifier }}" target="_blank" 
                           class="text-primary hover:text-primary-400 transition-colors">{{ $server->name }}</a>
                    </div>
                @endif
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="text-sm">
                        <span class="text-zinc-400 font-medium">{{__("Title")}}:</span>
                        <span class="text-zinc-200">{{ $ticket->title }}</span>
                    </div>
                    
                    <div class="text-sm">
                        <span class="text-zinc-400 font-medium">{{__("Category")}}:</span>
                        <span class="text-zinc-200">{{ $ticketcategory->name }}</span>
                    </div>
                    
                    <div class="text-sm">
                        <span class="text-zinc-400 font-medium">{{__("Created")}}:</span>
                        <span class="text-zinc-200">{{ $ticket->created_at->diffForHumans() }}</span>
                    </div>
                    
                    <div class="text-sm">
                        <span class="text-zinc-400 font-medium">{{__("Status")}}:</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            @switch($ticket->status)
                                @case('Open')
                                    bg-emerald-500/10 text-emerald-400
                                    @break
                                @case('Reopened')
                                    bg-emerald-500/10 text-emerald-400
                                    @break
                                @case('Closed')
                                    bg-red-500/10 text-red-400
                                    @break
                                @case('Answered')
                                    bg-blue-500/10 text-blue-400
                                    @break
                                @case('Client Reply')
                                    bg-amber-500/10 text-amber-400
                                    @break
                            @endswitch">
                            {{ __($ticket->status) }}
                        </span>
                    </div>
                    
                    <div class="text-sm">
                        <span class="text-zinc-400 font-medium">{{__("Priority")}}:</span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            @switch($ticket->priority)
                                @case('Low')
                                    bg-emerald-500/10 text-emerald-400
                                    @break
                                @case('Medium')
                                    bg-amber-500/10 text-amber-400
                                    @break
                                @case('High')
                                    bg-red-500/10 text-red-400
                                    @break
                            @endswitch">
                            {{ __($ticket->priority) }}
                        </span>
                    </div>
                </div>

                <div class="flex gap-2 mt-4">
                    @if($ticket->status == 'Closed')
                        <form method="post" action="{{route('ticket.changeStatus', ['ticket_id' => $ticket->ticket_id ])}}">
                            @csrf
                            <button class="btn btn-primary">
                                <i class="fas fa-redo mr-2"></i>{{__("Reopen")}}
                            </button>
                        </form>
                    @else
                        <form method="post" action="{{route('ticket.changeStatus', ['ticket_id' => $ticket->ticket_id ])}}">
                            @csrf
                            <button class="btn bg-amber-500/10 text-amber-400 hover:bg-amber-500/20">
                                <i class="fas fa-times mr-2"></i>{{__("Close")}}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Messages -->
        <div class="space-y-6">
            <!-- Original Message -->
            <div class="card">
                <div class="card-header flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <img src="https://www.gravatar.com/avatar/{{ md5(strtolower($ticket->user->email)) }}?s=40" 
                             class="rounded-full" alt="User Image">
                        <div>
                            <h5 class="text-white font-medium">{{ $ticket->user->name }}</h5>
                            <div class="flex gap-1">
                                @foreach ($ticket->user->roles as $role)
                                    <span class="px-2 py-1 rounded-full text-xs" 
                                          style="background-color: {{ $role->color }}20; color: {{ $role->color }}">
                                        {{$role->name}}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <span class="text-zinc-500 text-sm">{{ $ticket->created_at->diffForHumans() }}</span>
                </div>
                <div class="card-body prose prose-invert max-w-none">
                    {{ $ticket->message }}
                </div>
            </div>

            <!-- Comments -->
            @foreach ($ticketcomments as $comment)
                <div class="card">
                    <div class="card-header flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <img src="https://www.gravatar.com/avatar/{{ md5(strtolower($comment->user->email)) }}?s=40" 
                                 class="rounded-full" alt="User Image">
                            <div>
                                <h5 class="text-white font-medium">{{ $comment->user->name }}</h5>
                                <div class="flex gap-1">
                                    @foreach ($comment->user->roles as $role)
                                        <span class="px-2 py-1 rounded-full text-xs" 
                                              style="background-color: {{ $role->color }}20; color: {{ $role->color }}">
                                            {{$role->name}}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <span class="text-zinc-500 text-sm">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="card-body prose prose-invert max-w-none">
                        {{ $comment->ticketcomment }}
                    </div>
                </div>
            @endforeach

            <!-- Reply Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="text-white font-medium flex items-center gap-2">
                        <i class="fas fa-reply text-zinc-400"></i>
                        {{__('Reply')}}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('ticket.reply')}}" method="POST" class="reply-form">
                        @csrf
                        <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                        <div class="space-y-4">
                            <textarea rows="6" id="ticketcomment" name="ticketcomment" 
                                      class="input @error('ticketcomment') border-red-500/50 @enderror"></textarea>
                            @error('ticketcomment')
                                <p class="text-sm text-red-500">{{ $message }}</p>
                            @enderror
                            <div class="flex justify-end">
                                <button type="submit" class="btn btn-primary reply-once">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    {{__('Submit Reply')}}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('.reply-form');
        const submitBtn = document.querySelector('.reply-once');
        
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
        });
    });
</script>
@endsection

