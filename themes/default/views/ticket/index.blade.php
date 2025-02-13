@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <header class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <h1 class="text-3xl font-light text-white">{{ __('Tickets') }}</h1>
            <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-zinc-400">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li class="text-zinc-500">{{ __('Tickets') }}</li>
                </ol>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Tickets Table -->
            <div class="lg:col-span-3">
                <div class="card">
                    <div class="card-header">
                        <div class="flex justify-between items-center">
                            <h5 class="text-white font-medium flex items-center gap-2">
                                <i class="fas fa-ticket-alt text-zinc-400"></i>
                                {{__('My Tickets')}}
                            </h5>
                            <a href="{{route('ticket.new')}}" 
                               class="btn btn-primary @cannot('user.ticket.write') opacity-50 cursor-not-allowed @endcannot">
                                <i class="fas fa-plus mr-2"></i>{{__('New Ticket')}}
                            </a>
                        </div>
                    </div>
                    <div class="card-body overflow-hidden">
                        <div class="overflow-x-auto">
                            <table id="datatable" class="w-full whitespace-nowrap">
                                <thead>
                                    <tr class="border-b border-zinc-800">
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 tracking-wider">{{__('Category')}}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 tracking-wider">{{__('Title')}}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 tracking-wider">{{__('Priority')}}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 tracking-wider">{{__('Status')}}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 tracking-wider">{{__('Last Updated')}}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-400 tracking-wider">{{__('Actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-800">
                                    <!-- DataTables will populate this -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Information Panel -->
            <div class="lg:col-span-1">
                <div class="card">
                    <div class="card-header">
                        <h5 class="text-white font-medium flex items-center gap-2">
                            <i class="fas fa-info-circle text-zinc-400"></i>
                            {{__('Ticket Information')}}
                        </h5>
                    </div>
                    <div class="card-body prose prose-invert max-w-none">
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
            ajax: "{{route('ticket.datatable')}}",
            order: [[ 4, "desc" ]],
            columns: [
                {data: 'category'},
                {data: 'title'},
                {data: 'priority'},
                {data: 'status'},
                {data: 'updated_at', type: 'num', render: {_: 'display', sort: 'raw'}},
                {data: 'actions', sortable: false},
            ],
            fnDrawCallback: function( oSettings ) {
                $('[data-toggle="popover"]').popover();
            }
        });

        // Style the entries dropdown
        $('.dataTables_length select').addClass('bg-zinc-900 border-zinc-700 text-zinc-300 rounded-lg');
    });
</script>

<style>
    .dataTables_length select {
        padding: 0.375rem 1.75rem 0.375rem 0.75rem;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 16px 12px;
    }
    
    .dataTables_length select option {
        background-color: rgb(24 24 27);
        color: rgb(212 212 216);
    }
</style>
@endsection

