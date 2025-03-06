@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-4 sm:p-8">
    <!-- Header -->
    <header class="max-w-screen-2xl mx-auto mb-6 sm:mb-8">
        <div class="glass-panel p-4 sm:p-6">
            <h1 class="text-2xl sm:text-3xl font-light text-white">{{ __('Ticket Management') }}</h1>
            <div class="text-zinc-400 text-sm mt-2">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2">
                        <li><a href="{{ route('home') }}" class="text-primary-400 hover:text-primary-300">{{ __('Dashboard') }}</a></li>
                        <li><span class="text-zinc-600 mx-1">/</span></li>
                        <li class="text-zinc-400">{{ __('Ticket List') }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto space-y-6">
        <!-- Ticket List -->
        <div class="card glass-morphism">
            <div class="p-6 border-b border-zinc-800/50 flex justify-between items-center">
                <h3 class="text-white font-medium flex items-center gap-2">
                    <i class="fas fa-ticket-alt text-zinc-400"></i>
                    {{ __('Ticket List') }}
                </h3>
                <a href="{{ route('admin.ticket.category.index') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>{{ __('Add Category') }}
                </a>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table id="datatable" class="table table-striped w-full">
                        <thead>
                            <tr>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Priority') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Last Updated') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
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
            fnDrawCallback: function( oSettings ) {
                $('[data-toggle="popover"]').popover();
            }
        });
    });
</script>
@endsection
