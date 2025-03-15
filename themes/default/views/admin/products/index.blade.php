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
                <table id="datatable" class="table w-full">
                    <thead>
                    <tr>
                        <th>{{__('Active')}}</th>
                        <th>{{__('Name')}}</th>
                        <th>{{__('Price')}}</th>
                        <th>{{__('Billing period')}}</th>
                        <th>{{__('Memory')}}</th>
                        <th>{{__('Cpu')}}</th>
                        <th>{{__('Swap')}}</th>
                        <th>{{__('Disk')}}</th>
                        <th>{{__('Databases')}}</th>
                        <th>{{__('Backups')}}</th>
                        <th>{{__('OOM Killer')}}</th>
                        <th>{{__('Nodes')}}</th>
                        <th>{{__('Eggs')}}</th>
                        <th>{{__('Min Credits')}}</th>
                        <th>{{__('Servers')}}</th>
                        <th>{{__('Serverlimit')}}</th>
                        <th>{{__('Created at')}}</th>
                        <th>{{ __('Actions') }}</th>
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
