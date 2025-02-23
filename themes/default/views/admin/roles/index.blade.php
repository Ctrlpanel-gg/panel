@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="max-w-screen-2xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Role Management') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Roles List') }}</li>
                        </ol>
                    </nav>
                </div>
                @can('admin.roles.write')
                    <a href="{{route('admin.roles.create')}}" class="btn btn-primary">
                        <i class="fas fa-shield-alt mr-2"></i>{{ __('Create Role') }}
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto">
        <div class="glass-panel">
            <div class="p-6 border-b border-zinc-800/50">
                <div class="flex justify-between items-center">
                    <h5 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-user-check mr-2 text-zinc-400"></i>
                        {{__('Roles List')}}
                    </h5>
                </div>
            </div>
            <div class="p-6">
                <table id="datatable" class="w-full">
                    <thead>
                        <tr class="text-left text-zinc-400">
                            <th class="px-2 py-3">{{__('ID')}}</th>
                            <th class="px-2 py-3">{{__('Name')}}</th>
                            <th class="px-2 py-3">{{__('User count')}}</th>
                            <th class="px-2 py-3">{{__('Permissions count')}}</th>
                            <th class="px-2 py-3">{{__('Power')}}</th>
                            <th class="px-2 py-3">{{__('Actions')}}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    /* Override DataTables theme */
    .dataTables_wrapper {
        @apply text-zinc-400;
    }
    .dataTables_length select, 
    .dataTables_filter input {
        @apply bg-primary-950 border border-primary-800 rounded-lg text-white px-3 py-1.5;
    }
    .dataTables_paginate .paginate_button.current {
        @apply bg-primary-800 text-white !important;
        border: none !important;
    }
    .dataTables_paginate .paginate_button:hover {
        @apply bg-primary-900 text-white !important;
        border: none !important;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        $('#datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{config("SETTINGS::LOCALE:DATATABLES")}}.json'
            },
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax: "{{route('admin.roles.datatable')}}",
            columns: [
                {data: 'id'},
                {data: 'name'},
                {data: 'users_count'},
                {data: 'permissions_count'},
                {data: 'power', render: function(data) {
                    return `<span class="px-2 py-1 text-xs rounded-full bg-blue-500/10 text-blue-500">${data}</span>`;
                }},
                {data: 'actions', sortable: false},
            ],
            fnDrawCallback: function( oSettings ) {
                $('[data-toggle="popover"]').popover();
            }
        });
    });
</script>
@endsection
