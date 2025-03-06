@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="max-w-screen-2xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Application API') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Application API') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto">
        <div class="glass-panel">
            <div class="p-6 border-b border-zinc-800/50">
                <div class="flex justify-between items-center">
                    <h5 class="text-lg font-medium text-white flex items-center">
                        <i class="fa fa-gamepad mr-2 text-zinc-400"></i>
                        {{__('API Tokens')}}
                    </h5>
                    <a href="{{route('admin.api.create')}}" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>{{__('Create new')}}
                    </a>
                </div>
            </div>
            <div class="p-6">
                <table id="datatable" class="w-full">
                    <thead>
                        <tr class="text-left text-zinc-400">
                            <th class="px-2 py-3">{{__('Token')}}</th>
                            <th class="px-2 py-3">{{__('Memo')}}</th>
                            <th class="px-2 py-3">{{__('Last used')}}</th>
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
    function submitResult() {
        return confirm("{{__('Are you sure you wish to delete?')}}") !== false;
    }

    document.addEventListener("DOMContentLoaded", function () {
        $('#datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{ $locale_datatables }}.json'
            },
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax: "{{route('admin.api.datatable')}}",
            order: [[ 2, "desc" ]],
            columns: [
                {data: 'token'},
                {data: 'memo'},
                {data: 'last_used'},
                {data: 'actions', sortable: false},
            ],
            fnDrawCallback: function( oSettings ) {
                $('[data-toggle="popover"]').popover();
            }
        });
    });
</script>
@endsection
