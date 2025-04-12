@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="w-full mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Products') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Products') }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>{{ __('Create new') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-full mx-auto">
        <div class="glass-panel p-6">
            <div class="overflow-x-auto">
                <table id="datatable" class="w-full">
                    <thead>
                        <tr class="text-left text-zinc-400">
                            <th class="px-2 py-3">{{ __('Active') }}</th>
                            <th class="px-2 py-3">{{ __('Name') }}</th>
                            <th class="px-2 py-3">{{ __('Price') }}</th>
                            <th class="px-2 py-3">{{ __('Billing period') }}</th>
                            <th class="px-2 py-3">{{ __('Memory') }}</th>
                            <th class="px-2 py-3">{{ __('Cpu') }}</th>
                            <th class="px-2 py-3">{{ __('Swap') }}</th>
                            <th class="px-2 py-3">{{ __('Disk') }}</th>
                            <th class="px-2 py-3">{{ __('Databases') }}</th>
                            <th class="px-2 py-3">{{ __('Backups') }}</th>
                            <th class="px-2 py-3">{{ __('OOM Killer') }}</th>
                            <th class="px-2 py-3">{{ __('Nodes') }}</th>
                            <th class="px-2 py-3">{{ __('Eggs') }}</th>
                            <th class="px-2 py-3">{{ __('Min Credits') }}</th>
                            <th class="px-2 py-3">{{ __('Servers') }}</th>
                            <th class="px-2 py-3">{{ __('Serverlimit') }}</th>
                            <th class="px-2 py-3">{{ __('Created at') }}</th>
                            <th class="px-2 py-3">{{ __('Actions') }}</th>
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
        return confirm("{{ __('Are you sure you wish to delete?') }}") !== false;
    }

    document.addEventListener("DOMContentLoaded", function () {
        $("#datatable").DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{ $locale_datatables }}.json'
            },
            processing: true,
            serverSide: true,
            stateSave: true,
            order: [
                [2, "asc"]
            ],
            ajax: "{{ route('admin.products.datatable') }}",
            columns: [
                {data: "disabled"},
                {data: "name"},
                {data: "price"},
                {data: "billing_period"},
                {data: "memory"},
                {data: "cpu"},
                {data: "swap"},
                {data: "disk"},
                {data: "databases"},
                {data: "backups"},
                {data: "oom_killer"},
                {data: "nodes", sortable: false},
                {data: "eggs", sortable: false},
                {data: "minimum_credits"},
                {data: "servers", sortable: false},
                {data: "serverlimit"},
                {data: "created_at"},
                {data: "actions", sortable: false}
            ],
            fnDrawCallback: function (oSettings) {
                $("[data-toggle=\"popover\"]").popover();
            }
        });
    });
</script>
@endsection
