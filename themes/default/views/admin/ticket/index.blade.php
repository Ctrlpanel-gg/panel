@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="max-w-screen-2xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Ticket Management') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Ticket List') }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{route('admin.ticket.category.index')}}" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>{{ __('Add Category') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto">
        <div class="glass-panel">
            <div class="p-6 border-b border-zinc-800/50">
                <div class="flex justify-between items-center">
                    <h5 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-ticket-alt mr-2 text-zinc-400"></i>
                        {{__('Ticket List')}}
                    </h5>
                </div>
            </div>
            <div class="p-6">
                <table id="datatable" class="w-full">
                    <thead>
                        <tr class="text-left text-zinc-400">
                            <th class="px-2 py-3">{{__('Category')}}</th>
                            <th class="px-2 py-3">{{__('Title')}}</th>
                            <th class="px-2 py-3">{{__('User')}}</th>
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

<script>
    document.addEventListener("DOMContentLoaded", function () {
        $('#datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{ $locale_datatables }}.json'
            },
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax: "{{route('admin.ticket.datatable')}}",
            order: [[ 5, "desc" ]],
            columns: [
                {data: 'category'},
                {data: 'title'},
                {data: 'user_id'},
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
