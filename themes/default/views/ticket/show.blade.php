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
                                                       href="{{ route('ticket.index') }}">{{ __('Ticket') }}</a>
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
                                <p><b>{{__("Server")}}:</b> <a href="{{ $pterodactyl_url }}/server/{{ $server->identifier }}" target="__blank">{{ $server->name }} </a></p>
                                @endif
                                <p><b>{{__("Title")}}:</b> {{ $ticket->title }}</p>
                                <p><b>{{__("Category")}}:</b> {{ $ticketcategory->name }}</p>
                                <p><b>{{__("Status")}}:</b>
                                    @switch($ticket->status)
                                        @case("Open")
                                            <span class="badge badge-success">{{__("Open")}}</span>
                                            @break
                                        @case("Reopened")
                                            <span class="badge badge-success">{{__("Reopened")}}</span>
                                            @break
                                        @case("Closed")
                                            <span class="badge badge-danger">{{__("Closed")}}</span>
                                            @break
                                        @case("Answered")
                                            <span class="badge badge-info">{{__("Answered")}}</span>
                                            @break
                                        @case("Client Reply")
                                            <span class="badge badge-warning">{{__("Client Reply")}}</span>
                                            @break
                                    @endswitch
                                </p>
                                <p><b>Priority:</b>
                                    @switch($ticket->priority)
                                        @case("Low")
                                            <span class="badge badge-success">{{__("Low")}}</span>
                                            @break
                                        @case("Medium")
                                            <span class="badge badge-warning">{{__("Medium")}}</span>
                                            @break
                                        @case("High")
                                            <span class="badge badge-danger">{{__("High")}}</span>
                                            @break
                                    @endswitch
                                </p>
                                <p><b>{{__("Created on")}}:</b> {{ $ticket->created_at->diffForHumans() }}</p>
                                @if($ticket->status=='Closed')
                                    <form class="d-inline" method="post"
                                          action="{{route('ticket.changeStatus', ['ticket_id' => $ticket->ticket_id ])}}">
                                        {{csrf_field()}}
                                        {{method_field("POST") }}
                                        <button data-content="{{__("Reopen")}}" data-toggle="popover"
                                                data-trigger="hover" data-placement="top"
                                                class="btn btn-sm text-white btn-success mr-1"><i
                                                class="fas fa-redo"></i>{{__("Reopen")}}</button>
                                    </form>
                                @else
                                    <form class="d-inline" method="post"
                                          action="{{route('ticket.changeStatus', ['ticket_id' => $ticket->ticket_id ])}}">
                                        {{csrf_field()}}
                                        {{method_field("POST") }}
                                        <button data-content="{{__("Close")}}" data-toggle="popover"
                                                data-trigger="hover" data-placement="top"
                                                class="btn btn-sm text-white btn-warning mr-1"><i
                                                class="fas fa-times"></i>{{__("Close")}}</button>
                                    </form>
                                @endif
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
                                            <a href="/admin/users/{{$ticket->user->id}}">{{ $ticket->user->name }} </a>
                                            @foreach ($ticket->user->roles as $role)
                                                <span style='background-color: {{$role->color}}' class='badge'>{{$role->name}}</span>
                                            @endforeach
                                        </h5>
                                        <span
                                            class="badge badge-primary">{{ $ticket->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <div class="card-body" style="white-space:pre-wrap">{{ $ticket->message }}</div>
                            </div>
                            <div id="ticket-comments">
                                @foreach ($ticketcomments as $ticketcomment)
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="d-flex justify-content-between">
                                                <h5 class="card-title"><img
                                                        src="https://www.gravatar.com/avatar/{{ md5(strtolower($ticketcomment->user->email)) }}?s=25"
                                                        class="user-image" alt="User Image">
                                                    <a href="/admin/users/{{$ticketcomment->user->id}}">{{ $ticketcomment->user->name }}</a>
                                                    @foreach ($ticketcomment->user->roles as $role)
                                                        <span style='background-color: {{$role->color}}' class='badge'>{{$role->name}}</span>
                                                    @endforeach
                                                </h5>
                                                <span
                                                    class="badge badge-primary">{{ $ticketcomment->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                        <div class="card-body"
                                             style="white-space:pre-wrap">{{ $ticketcomment->ticketcomment }}</div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="comment-form">
                                <form action="{{ route('ticket.reply')}}" method="POST" class="form reply-form">
                                    {!! csrf_field() !!}
                                    <input type="hidden" name="ticket_id" value="{{ $ticket->id }}">
                                    <div class="form-group{{ $errors->has('ticketcomment') ? ' has-error' : '' }}">
                                        <textarea rows="10" id="ticketcomment" class="form-control"
                                                  name="ticketcomment"></textarea>
                                        @if ($errors->has('ticketcomment'))
                                            <span class="help-block">
                                            <strong>{{ $errors->first('ticketcomment') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary reply-once">Submit</button>
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
    <script type="text/javascript">
        $(".reply-form").submit(function (e) {
            $(".reply-once").attr("disabled", true);
            return true;
        })

        document.addEventListener("DOMContentLoaded", function () {
            setInterval(function () {
                fetch("{{ route('ticket.comments', $ticket->ticket_id) }}")
                    .then(response => response.json())
                    .then(data => {
                        const container = document.getElementById('ticket-comments');
                        if (!container) {
                            return;
                        }

                        // Clear existing comments
                        while (container.firstChild) {
                            container.removeChild(container.firstChild);
                        }

                        data.forEach(comment => {
                            if (!comment || !comment.user) {
                                return;
                            }

                            const card = document.createElement('div');
                            card.className = 'card';

                            const cardHeader = document.createElement('div');
                            cardHeader.className = 'card-header';

                            const headerFlex = document.createElement('div');
                            headerFlex.className = 'd-flex justify-content-between';

                            const title = document.createElement('h5');
                            title.className = 'card-title';

                            const img = document.createElement('img');
                            img.className = 'user-image';
                            img.alt = 'User Image';
                            if (comment.user.avatar) {
                                img.src = comment.user.avatar;
                            }
                            title.appendChild(img);

                            const nameLink = document.createElement('a');
                            nameLink.href = '/admin/users/' + String(comment.user.id || '');
                            nameLink.textContent = comment.user.name || '';
                            title.appendChild(document.createTextNode(' '));
                            title.appendChild(nameLink);

                            if (Array.isArray(comment.user.roles)) {
                                comment.user.roles.forEach(role => {
                                    if (!role) {
                                        return;
                                    }
                                    const span = document.createElement('span');
                                    span.className = 'badge';
                                    // Allow only simple hex colors; fall back to a safe default
                                    const color = typeof role.color === 'string' && /^#[0-9a-fA-F]{3,6}$/.test(role.color)
                                        ? role.color
                                        : '#777777';
                                    span.style.backgroundColor = color;
                                    span.textContent = role.name || '';
                                    title.appendChild(document.createTextNode(' '));
                                    title.appendChild(span);
                                });
                            }

                            const createdSpan = document.createElement('span');
                            createdSpan.className = 'badge badge-primary';
                            createdSpan.textContent = comment.created_at || '';

                            headerFlex.appendChild(title);
                            headerFlex.appendChild(createdSpan);

                            cardHeader.appendChild(headerFlex);

                            const cardBody = document.createElement('div');
                            cardBody.className = 'card-body';
                            cardBody.style.whiteSpace = 'pre-wrap';
                            cardBody.textContent = comment.ticketcomment || '';

                            card.appendChild(cardHeader);
                            card.appendChild(cardBody);

                            container.appendChild(card);
                        });
                    });
            }, 15000);
        });
    </script>
@endsection
