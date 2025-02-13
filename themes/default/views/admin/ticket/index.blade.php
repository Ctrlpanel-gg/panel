@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <header class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <h1 class="text-3xl font-light text-white">{{ __('Support Tickets') }}</h1>
            <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-zinc-400">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li class="text-zinc-500">{{ __('Ticket List') }}</li>
                </ol>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-xl mx-auto">
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <h3 class="text-white font-medium">
                        <i class="fas fa-ticket-alt text-zinc-400 mr-2"></i>
                        {{ __('Ticket List') }}
                    </h3>
                </div>
                <div class="ml-auto">
                    <a href="{{ route('admin.ticket.category.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>{{ __('Add Category') }}
                    </a>
                </div>
            </div>

            <div class="p-6">
                <div class="overflow-x-auto">
                    <table id="datatable" class="w-full whitespace-nowrap">
                        <thead class="text-left">
                            <tr class="border-b border-zinc-800">
                                <th class="pb-3 text-sm font-medium text-zinc-400">{{ __('Category') }}</th>
                                <th class="pb-3 text-sm font-medium text-zinc-400">{{ __('Title') }}</th>
                                <th class="pb-3 text-sm font-medium text-zinc-400">{{ __('User') }}</th>
                                <th class="pb-3 text-sm font-medium text-zinc-400">{{ __('Priority') }}</th>
                                <th class="pb-3 text-sm font-medium text-zinc-400">{{ __('Status') }}</th>
                                <th class="pb-3 text-sm font-medium text-zinc-400">{{ __('Last Updated') }}</th>
                                <th class="pb-3 text-sm font-medium text-zinc-400">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="text-zinc-300"></tbody>
                    </table>
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
            ajax: "{{ route('admin.ticket.datatable') }}",
            order: [[ 4, "desc" ]],
            columns: [
                {data: 'category'},
                {data: 'title'},
                {data: 'user_id'},
                {data: 'priority'},
                {data: 'status'},
                {data: 'updated_at', type: 'num', render: {_: 'display', sort: 'raw'}},
                {data: 'actions', sortable: false},
            ],
            fnDrawCallback: function(oSettings) {
                $('[data-toggle="popover"]').popover();
            },
            // Custom styling
            dom: '<"flex items-center justify-between mb-4"lf>rt<"flex items-center justify-between mt-4"ip>',
            className: 'text-sm',
        });
    });
</script>
@endsection
