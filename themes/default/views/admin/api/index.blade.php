@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <header class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <h1 class="text-3xl font-light text-white">{{ __('Application API') }}</h1>
            <div class="text-zinc-400 text-sm mt-2">
                <a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a>
                <span class="px-2">›</span>
                <span>{{ __('Application API') }}</span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-xl mx-auto">
        <div class="card glass-morphism">
            <div class="p-6 border-b border-zinc-800/50">
                <div class="flex justify-between items-center">
                    <h3 class="text-white font-medium flex items-center gap-2">
                        <i class="fa fa-gamepad text-zinc-400"></i>
                        {{ __('Application API') }}
                    </h3>
                    <a href="{{ route('admin.api.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 text-sm rounded-lg border border-blue-500/20 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        {{ __('Create new') }}
                    </a>
                </div>
            </div>

            <div class="p-6">
                <table id="datatable" class="min-w-full divide-y divide-zinc-800/50">
                    <thead>
                        <tr class="text-zinc-400 text-left text-sm">
                            <th class="px-4 py-3">{{ __('Token') }}</th>
                            <th class="px-4 py-3">{{ __('Memo') }}</th>
                            <th class="px-4 py-3">{{ __('Last used') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/50">
                    </tbody>
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
                {data: 'actions' , sortable : false},
            ],
            fnDrawCallback: function( oSettings ) {
                $('[data-toggle="popover"]').popover();
            }
        });
    });
</script>
@endsection
