@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="w-full mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Servers') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Servers') }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="flex gap-3">
                    <form method="POST" action="{{ route('admin.servers.sync') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sync-alt mr-2"></i>{{ __('Sync Servers') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-full">
        <div class="glass-panel">
            <div class="p-6 border-b border-zinc-800/50">
                <div class="flex justify-between items-center">
                    <h5 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-server mr-2 text-zinc-400"></i>
                        {{ __('Servers') }}
                    </h5>
                </div>
            </div>
            <div class="p-6 relative">
                <div class="flex items-center justify-between mb-6">
                    <!-- Custom Length Control -->
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-zinc-400">{{ __('Show') }}</span>
                        <select id="datatable_length" class="bg-zinc-900/90 border border-zinc-800/50 text-zinc-300 rounded-lg py-1.5 px-3 pr-8 w-20 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all appearance-none cursor-pointer">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-sm text-zinc-400">{{ __('entries') }}</span>
                    </div>
                    
                    <!-- Custom Search Control -->
                    <div class="relative">
                        <input type="search" id="datatable_search" class="w-64 bg-zinc-900/90 border border-zinc-800/50 text-zinc-300 rounded-lg py-1.5 pl-8 pr-3 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-zinc-600" placeholder="{{ __('Search...') }}">
                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                            <i class="fas fa-search text-zinc-500 text-sm"></i>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-x-auto">
                    <div id="custom-loader" style="display: none;">
                        <div class="loader-container">
                            <div class="loader"></div>
                        </div>
                    </div>
                    <table id="datatable" class="text-left">
                        <thead>
                            <tr>
                                <th width="20"></th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Server id') }}</th>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Suspended at') }}</th>
                                <th>{{ __('Created at') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/10">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function submitResult() {
        return confirm("{{ __('Are you sure you wish to delete?') }}") !== false;
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Get reference to the table container
        const tableContainer = document.querySelector('.overflow-x-auto');
        const customLoader = document.getElementById('custom-loader');

        // Initialize DataTable with disabled processing display
        const dataTable = $('#datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{ $locale_datatables }}.json',
                paginate: {
                    first: '<i class="fas fa-angle-double-left flex items-center justify-center w-full h-full"></i>',
                    previous: '<i class="fas fa-angle-left flex items-center justify-center w-full h-full"></i>',
                    next: '<i class="fas fa-angle-right flex items-center justify-center w-full h-full"></i>',
                    last: '<i class="fas fa-angle-double-right flex items-center justify-center w-full h-full"></i>'
                }
            },
            processing: false,
            serverSide: true,
            stateSave: true,
            ajax: {
                url: "{{ route('admin.servers.datatable') }}{{ $filter ?? '' }}",
                beforeSend: function() {
                    customLoader.style.display = 'flex';
                },
                complete: function() {
                    customLoader.style.display = 'none';
                }
            },
            order: [[6, "desc"]],
            columns: [
                { data: 'status', name: 'servers.suspended', sortable: false },
                { data: 'name' },
                { data: 'user', name: 'user.name' },
                { data: 'identifier' },
                { data: 'resources', name: 'product.name', sortable: false },
                { data: 'suspended' },
                { data: 'created_at' },
                { data: 'actions', sortable: false }
            ],
            fnDrawCallback: function(oSettings) {
                $('[data-toggle="popover"]').popover({
                    trigger: 'hover',
                    placement: 'top'
                });
                $('.dataTables_processing').hide();
            },
            dom: 'rtp',
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pagingType: "full_numbers",
            drawCallback: function() {
                $('.dataTables_processing').hide();
                $('[data-toggle="popover"]').popover({
                    trigger: 'hover',
                    placement: 'top',
                    html: true,
                    template: '<div class="popover custom-popover" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
                });
            }
        });

        // Custom search functionality
        const customSearch = document.getElementById('datatable_search');
        customSearch.addEventListener('input', function() {
            dataTable.search(this.value).draw();
        });

        // Custom entries functionality
        const customEntries = document.getElementById('datatable_length');
        customEntries.addEventListener('change', function() {
            dataTable.page.len(this.value).draw();
        });

        // Loading on interactions
        $('#datatable').on('page.dt length.dt search.dt', function() {
            customLoader.style.display = 'flex';
        });

        // Hide default processing
        $('<style>.dataTables_processing { display: none !important; }</style>').appendTo('head');
    });
</script>
@endsection
