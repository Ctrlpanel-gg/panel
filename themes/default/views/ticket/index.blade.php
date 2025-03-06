@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-4 sm:p-8">
    <!-- Header -->
    <header class="max-w-screen-2xl mx-auto mb-6">
        <div class="glass-panel p-4 sm:p-6">
            <h1 class="text-2xl sm:text-3xl font-light text-white">{{ __('Ticket') }}</h1>
            <div class="text-zinc-400 text-sm mt-2">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2">
                        <li><a href="{{ route('home') }}" class="text-primary-400 hover:text-primary-300">{{ __('Dashboard') }}</a></li>
                        <li><span class="text-zinc-600 mx-1">/</span></li>
                        <li class="text-zinc-400">{{ __('Ticket') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Ticket List -->
            <div class="lg:col-span-8">
                <div class="card glass-morphism">
                    <div class="p-6 border-b border-zinc-800/50">
                        <div class="flex flex-wrap justify-between items-center gap-4">
                            <h5 class="text-lg font-medium text-white flex items-center">
                                <i class="fas fa-ticket-alt mr-2 text-zinc-400"></i>
                                {{__('My Ticket')}}
                            </h5>
                            <a href="{{route('ticket.new')}}" 
                               class="btn btn-primary @cannot('user.ticket.write') opacity-50 cursor-not-allowed @endcannot">
                                <i class="fas fa-plus mr-2"></i>{{__('New Ticket')}}
                            </a>
                        </div>
                    </div>
                    <div class="p-4 sm:p-6">
                        <div class="table-responsive">
                            <table id="datatable" class="w-full">
                                <thead>
                                    <tr class="text-left text-zinc-400">
                                        <th class="px-2 py-3">{{__('Category')}}</th>
                                        <th class="px-2 py-3">{{__('Title')}}</th>
                                        <th class="px-2 py-3">{{__('Priority')}}</th>
                                        <th class="px-2 py-3">{{__('Status')}}</th>
                                        <th class="px-2 py-3">{{__('Last Updated')}}</th>
                                        <th class="px-2 py-3">{{__('Actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticket Information -->
            <div class="lg:col-span-4">
                <div class="card glass-morphism">
                    <div class="p-6 border-b border-zinc-800/50">
                        <h5 class="text-lg font-medium text-white">{{__('Ticket Information')}}</h5>
                    </div>
                    <div class="p-6 prose prose-invert max-w-none">
                        {!! $ticketsettings->information !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        $('#datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{ $locale_datatables }}.json'
            },
            processing: true,
            serverSide: true,
            stateSave: true,
            responsive: true,
            ajax: "{{route('ticket.datatable')}}",
            order: [[ 4, "desc" ]],
            columns: [
                {data: 'category'},
                {data: 'title'},
                {data: 'priority', render: function(data, type, row) {
                    if (type === 'display') {
                        switch(data) {
                            case 'High':
                                return '<span class="px-2 py-1 text-xs rounded-full bg-red-500/10 text-red-500">' + data + '</span>';
                            case 'Medium':
                                return '<span class="px-2 py-1 text-xs rounded-full bg-amber-500/10 text-amber-500">' + data + '</span>';
                            case 'Low':
                                return '<span class="px-2 py-1 text-xs rounded-full bg-emerald-500/10 text-emerald-500">' + data + '</span>';
                            default:
                                return data;
                        }
                    }
                    return data;
                }},
                {data: 'status'},
                {data: 'updated_at', type: 'num', render: {_: 'display', sort: 'raw'}},
                {data: 'actions', sortable: false},
            ],
            fnDrawCallback: function( oSettings ) {
                $('[data-toggle="popover"]').popover();
            }
        });
    });
</script>
@endsection

