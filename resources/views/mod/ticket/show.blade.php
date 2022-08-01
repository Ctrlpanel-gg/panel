@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Ticket') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                                       href="{{ route('mod.ticket.index') }}">{{ __('Ticket') }}</a>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title"><i class="fas fa-users mr-2"></i>#{{ $ticket->ticket_id }}</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="ticket-info">
                                @if(!empty($server))
                                <p><b>Server:</b> <a href="{{ config("SETTINGS::SYSTEM:PTERODACTYL:URL") . '/admin/servers/view/' . $server->pterodactyl_id }}" target="__blank">{{ $server->name }}</a></p>
                                @endif
                                <p><b>Title:</b> {{ $ticket->title }}</p>
                                <p><b>Category:</b> {{ $ticketcategory->name }}</p>
                                <p>
                                    @if ($ticket->status === 'Open')
                                    <b>Status:</b> <span class="badge badge-success">Open</span>
                                    @elseif ($ticket->status === 'Closed')
                                    <b>Status:</b> <span class="badge badge-danger">Closed</span>
                                    @elseif ($ticket->status === 'Answered')
                                    <b>Status:</b> <span class="badge badge-info">Answered</span>
                                    @elseif ($ticket->status === 'Client Reply')
                                    <b>Status:</b> <span class="badge badge-warning">Client Reply</span>
                                    @endif
                                </p>
                                <p><b>Created on:</b> {{ $ticket->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title"><i class="fas fa-cloud mr-2"></i>{{__('Comment')}}</h5>
                            </div>
                        </div> 
                        <div class="card-body">
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="card-title"><img
                                            src="https://www.gravatar.com/avatar/{{ md5(strtolower($ticket->user->email)) }}?s=25"
                                            class="user-image" alt="User Image">
                                        <a href="/admin/users/{{$ticket->user->id}}">{{ $ticket->user->name }}</a>
                                        @if($ticket->user->role === "member")
                                            <span class="badge badge-secondary"> Member </span>
                                        @elseif ($ticket->user->role === "client")
                                            <span class="badge badge-success"> Client </span>
                                        @elseif ($ticket->user->role === "mod")
                                            <span class="badge badge-info"> Moderator </span>
                                        @elseif ($ticket->user->role === "admin")
                                            <span class="badge badge-danger"> Admin </span>
                                        @endif
                                    </h5>
                                        <span class="badge badge-primary">{{ $ticket->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    {{ $ticket->message }}
                                </div>
                            </div>
                            @foreach ($ticketcomments as $ticketcomment)
                            <div class="card">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="card-title"><img
                                            src="https://www.gravatar.com/avatar/{{ md5(strtolower($ticketcomment->user->email)) }}?s=25"
                                            class="user-image" alt="User Image">
                                        <a href="/admin/users/{{$ticketcomment->user->id}}">{{ $ticketcomment->user->name }}</a>
                                        @if($ticketcomment->user->role === "member")
                                            <span class="badge badge-secondary"> Member </span>
                                        @elseif ($ticketcomment->user->role === "client")
                                            <span class="badge badge-success"> Client </span>
                                        @elseif ($ticketcomment->user->role === "mod")
                                            <span class="badge badge-info"> Moderator </span>
                                        @elseif ($ticketcomment->user->role === "admin")
                                            <span class="badge badge-danger"> Admin </span>
                                        @endif
                                    </h5>
                                        <span class="badge badge-primary">{{ $ticketcomment->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    {{ $ticketcomment->ticketcomment }}
                                </div>
                            </div>
                            @endforeach
                            <div class="comment-form">
                                <form action="{{ route('mod.ticket.reply')}}" method="POST" class="form">
                                    {!! csrf_field() !!}
                                    <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                                    <div class="form-group{{ $errors->has('ticketcomment') ? ' has-error' : '' }}">
                                        <textarea rows="10" id="ticketcomment" class="form-control" name="ticketcomment"></textarea>
                                        @if ($errors->has('ticketcomment'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('ticketcomment') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT -->
@endsection

