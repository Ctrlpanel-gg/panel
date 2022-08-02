@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('Ticket')}}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">{{__('Dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                                       href="{{route('moderator.ticket.index')}}">{{__('Ticket List')}}</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <div class="card">

                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h5 class="card-title"><i class="fas fa-ticket-alt mr-2"></i>{{__('Ticket List')}}</h5>
                    </div>
                </div>

                <div class="card-body table-responsive">

                    <table id="datatable" class="table table-striped">
                        <thead>
                        <tr>
                            <th>Category</th>
                            <th>Title</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach ($tickets as $ticket)
                                <tr>
                                    <td>
                                        {{ $ticket->ticketcategory->name }}
                                    </td>
                                    <td>
                                        <a href="{{ route('moderator.ticket.show', ['ticket_id' => $ticket->ticket_id]) }}">
                                            #{{ $ticket->ticket_id }} - {{ $ticket->title }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="/admin/users/{{$ticket->user->id}}">
                                            {{ $ticket->user->name }}
                                        </a>
                                    </td>
                                    <td>
                                        @if ($ticket->status === 'Open')
                                        <span class="badge badge-success">Open</span>
                                        @elseif ($ticket->status === 'Closed')
                                        <span class="badge badge-danger">Closed</span>
                                        @elseif ($ticket->status === 'Answered')
                                        <span class="badge badge-info">Answered</span>
                                        @elseif ($ticket->status === 'Client Reply')
                                        <span class="badge badge-warning">Client Reply</span>
                                        @endif
                                    </td>
                                    <td>{{ $ticket->updated_at }}</td>
                                    <td>
                                        <a data-content="View" data-toggle="popover" data-trigger="hover" data-placement="top" href="{{ route('moderator.ticket.show', ['ticket_id' => $ticket->ticket_id]) }}" class="btn btn-sm text-white btn-info mr-1"><i class="fas fa-eye"></i></a>
                                        <form class="d-inline" action="{{ route('moderator.ticket.close', ['ticket_id' => $ticket->ticket_id ]) }}" method="POST">
                                        @csrf
                                        <button data-content="Close" data-toggle="popover" data-trigger="hover" data-placement="top" type="submit" class="btn btn-sm text-white btn-warning mr-1"><i class="fas fa-times"></i></button>
                                        </form>
                                        <form class="d-inline" action="{{ route('moderator.ticket.delete', ['ticket_id' => $ticket->ticket_id ]) }}" method="POST">
                                            @csrf
                                            <button data-content="Delete" data-toggle="popover" data-trigger="hover" data-placement="top" type="submit" class="btn btn-sm text-white btn-danger mr-1"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>


        </div>
        <!-- END CUSTOM CONTENT -->

    </section>
    <!-- END CONTENT -->



@endsection
