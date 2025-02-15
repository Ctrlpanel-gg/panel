@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Ticket') }} #{{ $ticket->ticket_id }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li><a href="{{ route('ticket.index') }}" class="hover:text-white transition-colors">{{ __('Ticket') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">#{{ $ticket->ticket_id }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-screen-xl mx-auto space-y-6">
        <!-- Ticket Information -->
        <div class="glass-panel">
            <div class="p-6 border-b border-zinc-800/50">
                <h5 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-info-circle mr-2 text-zinc-400"></i>{{ __('Ticket Information') }}
                </h5>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @if(!empty($server))
                        <div>
                            <span class="text-sm text-zinc-400">{{ __('Server') }}</span>
                            <p class="mt-1">
                                <a href="{{ $pterodactyl_url }}/server/{{ $server->identifier }}" 
                                   target="_blank" 
                                   class="text-accent-blue hover:text-accent-blue/80">
                                    {{ $server->name }}
                                </a>
                            </p>
                        </div>
                    @endif
                    
                    <div>
                        <span class="text-sm text-zinc-400">{{ __('Title') }}</span>
                        <p class="mt-1 text-white">{{ $ticket->title }}</p>
                    </div>

                    <div>
                        <span class="text-sm text-zinc-400">{{ __('Category') }}</span>
                        <p class="mt-1 text-white">{{ $ticketcategory->name }}</p>
                    </div>

                    <div>
                        <span class="text-sm text-zinc-400">{{ __('Status') }}</span>
                        <p class="mt-1">
                            @switch($ticket->status)
                                @case("Open")
                                    <span class="px-2 py-1 text-xs rounded-full bg-emerald-500/10 text-emerald-500">{{__("Open")}}</span>
                                    @break
                                @case("Reopened")
                                    <span class="px-2 py-1 text-xs rounded-full bg-emerald-500/10 text-emerald-500">{{__("Reopened")}}</span>
                                    @break
                                @case("Closed")
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-500/10 text-red-500">{{__("Closed")}}</span>
                                    @break
                                @case("Answered")
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-500/10 text-blue-500">{{__("Answered")}}</span>
                                    @break
                                @case("Client Reply")
                                    <span class="px-2 py-1 text-xs rounded-full bg-amber-500/10 text-amber-500">{{__("Client Reply")}}</span>
                                    @break
                            @endswitch
                        </p>
                    </div>

                    <div>
                        <span class="text-sm text-zinc-400">{{ __('Priority') }}</span>
                        <p class="mt-1">
                            @switch($ticket->priority)
                                @case("Low")
                                    <span class="px-2 py-1 text-xs rounded-full bg-emerald-500/10 text-emerald-500">{{__("Low")}}</span>
                                    @break
                                @case("Medium")
                                    <span class="px-2 py-1 text-xs rounded-full bg-amber-500/10 text-amber-500">{{__("Medium")}}</span>
                                    @break
                                @case("High")
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-500/10 text-red-500">{{__("High")}}</span>
                                    @break
                            @endswitch
                        </p>
                    </div>

                    <div>
                        <span class="text-sm text-zinc-400">{{ __('Created') }}</span>
                        <p class="mt-1 text-white">{{ $ticket->created_at->diffForHumans() }}</p>
                    </div>
                </div>

                <div class="mt-6 flex gap-2">
                    @if($ticket->status=='Closed')
                        <form class="d-inline" method="post" action="{{route('ticket.changeStatus', ['ticket_id' => $ticket->ticket_id ])}}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-redo mr-2"></i>{{__("Reopen")}}
                            </button>
                        </form>
                    @else
                        <form class="d-inline" method="post" action="{{route('ticket.changeStatus', ['ticket_id' => $ticket->ticket_id ])}}">
                            @csrf
                            <button type="submit" class="btn bg-amber-600 text-white hover:bg-amber-500">
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
            <div class="glass-panel">
                <div class="p-6 border-b border-zinc-800/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="https://www.gravatar.com/avatar/{{ md5(strtolower($ticket->user->email)) }}?s=48" 
                                 class="w-12 h-12 rounded-full" 
                                 alt="{{ $ticket->user->name }}">
                            <div>
                                <h5 class="font-medium text-white">
                                    <a href="/admin/users/{{$ticket->user->id}}" class="hover:text-zinc-300">
                                        {{ $ticket->user->name }}
                                    </a>
                                </h5>
                                <div class="flex gap-1 mt-1">
                                    @foreach ($ticket->user->roles as $role)
                                        <span class="px-2 py-0.5 text-xs rounded-full" 
                                              style="background-color: {{ $role->color }}20; color: {{ $role->color }}">
                                            {{$role->name}}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <span class="text-sm text-zinc-400">{{ $ticket->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <div class="p-6 text-zinc-300 whitespace-pre-wrap">{{ $ticket->message }}</div>
            </div>

            <!-- Comments -->
            @foreach ($ticketcomments as $ticketcomment)
                <div class="glass-panel">
                    <div class="p-6 border-b border-zinc-800/50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <img src="https://www.gravatar.com/avatar/{{ md5(strtolower($ticketcomment->user->email)) }}?s=48" 
                                     class="w-12 h-12 rounded-full" 
                                     alt="{{ $ticketcomment->user->name }}">
                                <div>
                                    <h5 class="font-medium text-white">
                                        <a href="/admin/users/{{$ticketcomment->user->id}}" class="hover:text-zinc-300">
                                            {{ $ticketcomment->user->name }}
                                        </a>
                                    </h5>
                                    <div class="flex gap-1 mt-1">
                                        @foreach ($ticketcomment->user->roles as $role)
                                            <span class="px-2 py-0.5 text-xs rounded-full" 
                                                  style="background-color: {{ $role->color }}20; color: {{ $role->color }}">
                                                {{$role->name}}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <span class="text-sm text-zinc-400">{{ $ticketcomment->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="p-6 text-zinc-300 whitespace-pre-wrap">{{ $ticketcomment->ticketcomment }}</div>
                </div>
            @endforeach

            <!-- Reply Form -->
            <div class="glass-panel">
                <div class="p-6 border-b border-zinc-800/50">
                    <h5 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-reply mr-2 text-zinc-400"></i>{{ __('Reply') }}
                    </h5>
                </div>
                <div class="p-6">
                    <form action="{{ route('ticket.reply')}}" method="POST" class="reply-form space-y-4">
                        @csrf
                        <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                        <div>
                            <textarea rows="8" 
                                      id="ticketcomment" 
                                      class="input" 
                                      name="ticketcomment" 
                                      placeholder="{{ __('Your reply...') }}"></textarea>
                            @if ($errors->has('ticketcomment'))
                                <p class="mt-1 text-sm text-red-500">{{ $errors->first('ticketcomment') }}</p>
                            @endif
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary reply-once">
                                {{ __('Submit Reply') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(".reply-form").submit(function (e) {
        $(".reply-once").attr("disabled", true);
        return true;
    });
</script>
@endsection

