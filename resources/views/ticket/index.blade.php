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
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <h5 class="card-title"><i class="fas fa-ticket-alt mr-2"></i>{{__('My Ticket')}}</h5>
                                <a href="{{route('ticket.new')}}" class="btn btn-sm btn-primary"><i
                                        class="fas fa-plus mr-1"></i>{{__('New Ticket')}}</a>
                            </div>
                        </div>
                        <div class="card-body table-responsive">

                            <table id="datatable" class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tickets as $ticket)
                                    <tr>
                                        <td>
                                            {{ $ticket->ticketcategory->name }}
                                        </td>
                                        <td>
                                            <a href="{{ route('ticket.show', ['ticket_id' => $ticket->ticket_id]) }}">
                                                #{{ $ticket->ticket_id }} - {{ $ticket->title }}
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
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
        
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">{{__('Ticket Information')}}
                                <i data-toggle="popover"
                                data-trigger="hover"
                                data-content="{{__('please make the best of it')}}"
                                class="fas fa-info-circle"></i></h5>
                        </div>
                        <div class="card-body">
                            <p>Can't start your server? Need an additional port? Do you have any other questions? Let us know by
                                opening a ticket.</p>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT -->
@endsection

