@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="w-full mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Ticket Blacklist') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Ticket Blacklist') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-full">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Blacklist Table -->
            <div class="lg:col-span-8">
                <div class="glass-panel">
                    <div class="p-6 border-b border-zinc-800/50">
                        <div class="flex justify-between items-center">
                            <h5 class="text-lg font-medium text-white flex items-center">
                                <i class="fas fa-user-times mr-2 text-zinc-400"></i>
                                {{__('Blacklist')}}
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
                            <table id="datatable" class="w-full">
                                <thead>
                                    <tr class="text-left text-zinc-400">
                                        <th class="px-2 py-3">{{__('User')}}</th>
                                        <th class="px-2 py-3">{{__('Status')}}</th>
                                        <th class="px-2 py-3">{{__('Reason')}}</th>
                                        <th class="px-2 py-3">{{__('Created At')}}</th>
                                        <th class="px-2 py-3">{{__('Actions')}}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add to Blacklist Form -->
            <div class="lg:col-span-4">
                <div class="glass-panel">
                    <div class="p-6 border-b border-zinc-800/50">
                        <div class="flex items-center gap-2">
                            <h5 class="text-lg font-medium text-white">{{__('Add To Blacklist')}}</h5>
                            <i class="fas fa-info-circle text-zinc-400 cursor-help" 
                               data-toggle="popover"
                               data-trigger="hover"
                               data-content="{{__('Please make the best of it')}}"></i>
                        </div>
                    </div>
                    <div class="p-6">
                        <form action="{{route('admin.ticket.blacklist.add')}}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">
                                    {{ __('User') }}
                                    <i class="fas fa-info-circle ml-1 text-zinc-500 cursor-help"
                                       data-toggle="popover"
                                       data-trigger="hover"
                                       data-content="{{ __('Please note, the blacklist will make the user unable to make a ticket/reply again') }}"></i>
                                </label>
                                <select id="user_id" class="select2-users" name="user_id" required></select>
                            </div>

                            <div>
                                <label for="reason" class="block text-sm font-medium text-zinc-400 mb-1">{{__("Reason")}}</label>
                                <input id="reason" 
                                       type="text" 
                                       class="input" 
                                       name="reason" 
                                       placeholder="{{__('Input Some Reason')}}" 
                                       required>
                            </div>

                            <button type="submit" class="btn btn-primary w-full ticket-once">
                                {{__('Submit')}}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
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
            scrollX: true, processing: false,
            serverSide: true,
            stateSave: true,
            ajax: {
                url: "{{route('admin.ticket.blacklist.datatable')}}",
                beforeSend: function() {
                    customLoader.style.display = 'flex';
                },
                complete: function() {
                    customLoader.style.display = 'none';
                }
            },
            columns: [
                { data: 'user', name: 'user.name' },
                { data: 'status' },
                { data: 'reason' },
                { data: 'created_at' },
                { data: 'actions', orderable: false }
            ],
            order: [[3, 'desc']],
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

        // Also show loading on search, pagination, and length change
        $('#datatable').on('page.dt length.dt search.dt', function() {
            customLoader.style.display = 'flex';
        });

        // Initialize Select2 for user selection
        $('#user_id').select2({
            ajax: {
                url: '/admin/users.json',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        filter: { email: params.term },
                        page: params.page
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            },
            placeholder: '{{ __("Search for a user...") }}',
            minimumInputLength: 2,
            templateResult: function(user) {
                if (!user.id || user.loading) return user.text;
                return $(`
                    <div class="flex items-center gap-2">
                        <img src="${user.avatarUrl}?s=32" class="rounded-full w-8 h-8">
                        <div>
                            <div class="text-white">${user.name}</div>
                            <div class="text-sm text-gray-400">${user.email}</div>
                        </div>
                    </div>
                `);
            },
            templateSelection: function(user) {
                if (!user.id) return user.text;
                return $(`
                    <div class="flex items-center gap-2">
                        <img src="${user.avatarUrl}?s=24" class="rounded-full w-6 h-6">
                        <span class="text-white">${user.name}</span>
                    </div>
                `);
            }
        });

        // Initialize with existing data if available
        @if (old('user_id'))
            $.ajax({
                url: '/admin/users.json?user_id={{ old('user_id') }}',
                dataType: 'json',
            }).then(function (data) {
                const option = new Option(data.name, data.id, true, true);
                $('#user_id').append(option).trigger('change');
            });
        @endif
    });
</script>
@endsection
