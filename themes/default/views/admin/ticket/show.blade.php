@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-4 sm:p-8">
    <!-- Header -->
    <header class="max-w-screen-2xl mx-auto mb-6 sm:mb-8">
        <div class="glass-panel p-4 sm:p-6">
            <h1 class="text-2xl sm:text-3xl font-light text-white">{{ __('Ticket') }} #{{ $ticket->ticket_id }}</h1>
            <div class="text-zinc-400 text-sm mt-2">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2">
                        <li><a href="{{ route('home') }}" class="text-primary-400 hover:text-primary-300">{{ __('Dashboard') }}</a></li>
                        <li><span class="text-zinc-600 mx-1">/</span></li>
                        <li><a href="{{ route('admin.ticket.index') }}" class="text-primary-400 hover:text-primary-300">{{ __('Ticket') }}</a></li>
                        <li><span class="text-zinc-600 mx-1">/</span></li>
                        <li class="text-zinc-400">#{{ $ticket->ticket_id }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto space-y-6">
        <!-- Ticket Details -->
        <div class="card glass-morphism">
            <div class="p-6 border-b border-zinc-800/50">
                <h3 class="text-white font-medium flex items-center gap-2">
                    <i class="fas fa-info-circle text-zinc-400"></i>
                    {{ __('Ticket Details') }}
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Server -->
                    @if(!empty($server))
                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-blue-500/10">
                                <i class="fas fa-server text-blue-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('Server') }}</span>
                        </div>
                        <div class="text-lg font-medium text-white">
                            <a href="{{ $pterodactyl_url }}/admin/servers/view/{{ $server->pterodactyl_id }}" target="_blank" class="text-primary-400 hover:text-primary-300">
                                {{ $server->name }}
                            </a>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Title -->
                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-purple-500/10">
                                <i class="fas fa-heading text-purple-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('Title') }}</span>
                        </div>
                        <div class="text-lg font-medium text-white">{{ $ticket->title }}</div>
                    </div>
                    
                    <!-- Category -->
                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-emerald-500/10">
                                <i class="fas fa-tag text-emerald-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('Category') }}</span>
                        </div>
                        <div class="text-lg font-medium text-white">{{ $ticketcategory->name }}</div>
                    </div>
                    
                    <!-- Status -->
                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-amber-500/10">
                                <i class="fas fa-circle text-amber-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('Status') }}</span>
                        </div>
                        <div class="text-lg font-medium flex items-center gap-2">
                            @switch($ticket->status)
                                @case("Open")
                                    <span class="bg-emerald-500/10 text-emerald-400 px-2 py-1 rounded-full text-xs">{{ __('Open') }}</span>
                                    @break
                                @case("Reopened")
                                    <span class="bg-emerald-500/10 text-emerald-400 px-2 py-1 rounded-full text-xs">{{ __('Reopened') }}</span>
                                    @break
                                @case("Closed")
                                    <span class="bg-red-500/10 text-red-400 px-2 py-1 rounded-full text-xs">{{ __('Closed') }}</span>
                                    @break
                                @case("Answered")
                                    <span class="bg-blue-500/10 text-blue-400 px-2 py-1 rounded-full text-xs">{{ __('Answered') }}</span>
                                    @break
                                @case("Client Reply")
                                    <span class="bg-amber-500/10 text-amber-400 px-2 py-1 rounded-full text-xs">{{ __('Client Reply') }}</span>
                                    @break
                            @endswitch
                        </div>
                    </div>
                    
                    <!-- Priority -->
                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-red-500/10">
                                <i class="fas fa-flag text-red-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('Priority') }}</span>
                        </div>
                        <div class="text-lg font-medium flex items-center gap-2">
                            @switch($ticket->priority)
                                @case("Low")
                                    <span class="bg-emerald-500/10 text-emerald-400 px-2 py-1 rounded-full text-xs">{{ __('Low') }}</span>
                                    @break
                                @case("Medium")
                                    <span class="bg-amber-500/10 text-amber-400 px-2 py-1 rounded-full text-xs">{{ __('Medium') }}</span>
                                    @break
                                @case("High")
                                    <span class="bg-red-500/10 text-red-400 px-2 py-1 rounded-full text-xs">{{ __('High') }}</span>
                                    @break
                            @endswitch
                        </div>
                    </div>
                    
                    <!-- Created Date -->
                    <div class="glass-panel bg-zinc-800/30 p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="rounded-lg p-2 bg-blue-500/10">
                                <i class="fas fa-calendar-alt text-blue-400"></i>
                            </div>
                            <span class="text-sm text-zinc-400">{{ __('Created on') }}</span>
                        </div>
                        <div class="text-lg font-medium text-white">{{ $ticket->created_at->diffForHumans() }}</div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="mt-6 flex flex-wrap gap-3">
                    @if($ticket->status=='Closed')
                        <form class="inline" method="post" action="{{ route('admin.ticket.changeStatus', ['ticket_id' => $ticket->ticket_id ]) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-redo mr-2"></i>{{ __('Reopen') }}
                            </button>
                        </form>
                    @else
                        <form class="inline" method="post" action="{{ route('admin.ticket.changeStatus', ['ticket_id' => $ticket->ticket_id ]) }}">
                            @csrf
                            <button type="submit" class="btn bg-red-800/80 text-red-200 hover:bg-red-700/80">
                                <i class="fas fa-times mr-2"></i>{{ __('Close') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Comments -->
        <div class="card glass-morphism">
            <div class="p-6 border-b border-zinc-800/50">
                <h3 class="text-white font-medium flex items-center gap-2">
                    <i class="fas fa-comments text-zinc-400"></i>
                    {{ __('Comments') }}
                </h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- Original message -->
                <div class="glass-panel bg-zinc-800/30">
                    <div class="p-4 border-b border-zinc-700/30 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="https://www.gravatar.com/avatar/{{ md5(strtolower($ticket->user->email)) }}?s=40" class="rounded-full w-10 h-10" alt="User Image">
                            <div>
                                <div class="font-medium text-white">
                                    <a href="/admin/users/{{$ticket->user->id}}" class="hover:text-primary-300">{{ $ticket->user->name }}</a>
                                </div>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach ($ticket->user->roles as $role)
                                        <span style="background-color: {{ $role->color }}" class="px-2 py-0.5 rounded-full text-xs">{{ $role->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="text-xs text-zinc-500">{{ $ticket->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="p-4 text-zinc-300 whitespace-pre-wrap">{{ $ticket->message }}</div>
                </div>
                
                <!-- Comments -->
                @foreach ($ticketcomments as $ticketcomment)
                <div class="glass-panel bg-zinc-800/30">
                    <div class="p-4 border-b border-zinc-700/30 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="https://www.gravatar.com/avatar/{{ md5(strtolower($ticketcomment->user->email)) }}?s=40" class="rounded-full w-10 h-10" alt="User Image">
                            <div>
                                <div class="font-medium text-white">
                                    <a href="/admin/users/{{$ticketcomment->user->id}}" class="hover:text-primary-300">{{ $ticketcomment->user->name }}</a>
                                </div>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach ($ticketcomment->user->roles as $role)
                                        <span style="background-color: {{ $role->color }}" class="px-2 py-0.5 rounded-full text-xs">{{ $role->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="text-xs text-zinc-500">{{ $ticketcomment->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="p-4 text-zinc-300 whitespace-pre-wrap">{{ $ticketcomment->ticketcomment }}</div>
                </div>
                @endforeach
                
                <!-- Reply Form -->
                <div class="glass-panel bg-zinc-800/30 p-6">
                    <h4 class="text-white text-lg mb-4">{{ __('Add Reply') }}</h4>
                    <form action="{{ route('admin.ticket.reply') }}" method="POST" class="reply-form">
                        @csrf
                        <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                        <div class="mb-4">
                            <textarea rows="6" id="ticketcomment" name="ticketcomment" 
                                class="form-textarea w-full @error('ticketcomment') border-red-500/50 @enderror"
                                placeholder="{{ __('Your reply...') }}"></textarea>
                            @if ($errors->has('ticketcomment'))
                                <div class="text-red-400 text-sm mt-1">{{ $errors->first('ticketcomment') }}</div>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-primary reply-once">
                            {{ __('Submit Reply') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const replyForm = document.querySelector(".reply-form");
        if (replyForm) {
            replyForm.addEventListener("submit", function() {
                const submitButton = document.querySelector(".reply-once");
                if (submitButton) {
                    submitButton.disabled = true;
                }
            });
        }
    });
</script>
@endsection

