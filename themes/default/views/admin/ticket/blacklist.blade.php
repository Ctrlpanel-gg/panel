@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="max-w-screen-2xl mx-auto mb-8">
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
    <div class="max-w-screen-2xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Blacklist Table -->
            <div class="lg:col-span-8">
                <div class="glass-panel">
                    <div class="p-6 border-b border-zinc-800/50">
                        <h5 class="text-lg font-medium text-white flex items-center">
                            <i class="fas fa-user-times mr-2 text-zinc-400"></i>
                            {{__('Blacklist')}}
                        </h5>
                    </div>
                    <div class="p-6">
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

<style>
    .select2-container {
        width: 100% !important;
    }
    .select2-dropdown {
        background-color: #18181b !important;
        border: 1px solid #27272a !important;
        border-radius: 0.5rem !important;
    }
    .select2-search__field {
        background-color: #18181b !important;
        border: 1px solid #27272a !important;
        border-radius: 0.375rem !important;
        color: white !important;
        padding: 0.5rem !important;
    }
    .select2-results__option {
        color: white !important;
        padding: 0.5rem !important;
    }
    .select2-results__option--highlighted {
        background-color: #3f3f46 !important;
    }
    .select2-selection {
        background-color: #18181b !important;
        border: 1px solid #27272a !important;
        border-radius: 0.5rem !important;
        height: 42px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-selection__rendered {
        color: white !important;
        line-height: 42px !important;
        padding-left: 1rem !important;
    }
    .select2-selection__arrow {
        height: 42px !important;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        $('#datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{ $locale_datatables }}.json'
            },
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax: "{{route('admin.ticket.blacklist.datatable')}}",
            columns: [
                {data: 'user', name: 'user.name'},
                {data: 'status', render: function(data) {
                    return `<span class="px-2 py-1 text-xs rounded-full ${
                        data === 'Active' 
                            ? 'bg-red-500/10 text-red-500' 
                            : 'bg-emerald-500/10 text-emerald-500'
                    }">${data}</span>`;
                }},
                {data: 'reason'},
                {data: 'created_at'},
                {data: 'actions', sortable: false},
            ],
            fnDrawCallback: function( oSettings ) {
                $('[data-toggle="popover"]').popover();
            }
        });

        // Simplified Select2 initialization
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
