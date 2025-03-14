@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="max-w-screen-2xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Users') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Users') }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.users.notifications.index') }}" class="btn btn-primary">
                    <i class="fas fa-paper-plane mr-2"></i>{{ __('Notify') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto">
        <div class="glass-panel">
            <div class="p-6 border-b border-zinc-800/50">
                <div class="flex justify-between items-center">
                    <h5 class="text-lg font-medium text-white flex items-center">
                        <i class="fas fa-users mr-2 text-zinc-400"></i>
                        {{__('User List')}}
                    </h5>
                </div>
            </div>
            <div class="p-6">
                <table id="datatable" class="w-full">
                    <thead>
                        <tr class="text-left text-zinc-400">
                            <th class="px-2 py-3">discordId</th>
                            <th class="px-2 py-3">ip</th>
                            <th class="px-2 py-3">pterodactyl_id</th>
                            <th class="px-2 py-3">{{__('Avatar')}}</th>
                            <th class="px-2 py-3">{{__('Name')}}</th>
                            <th class="px-2 py-3">{{__('Role')}}</th>
                            <th class="px-2 py-3">{{__('Email')}}</th>
                            <th class="px-2 py-3">{{ $credits_display_name }}</th>
                            <th class="px-2 py-3">{{__('Servers')}}</th>
                            <th class="px-2 py-3">{{__('Referrals')}}</th>
                            <th class="px-2 py-3">{{__('Verified')}}</th>
                            <th class="px-2 py-3">{{__('Last seen')}}</th>
                            <th class="px-2 py-3"></th>
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

    document.addEventListener("DOMContentLoaded", function() {
        $('#datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{ $locale_datatables }}.json'
            },
            processing: true,
            serverSide: true, //why was this set to false before? increased loadingtimes by 10 seconds
            stateSave: true,
            ajax: "{{ route('admin.users.datatable') }}{{ $filter ?? '' }}",
            order: [
                [11, "desc"]
            ],
            columns: [
                {
                    data: 'discordId',
                    visible: false,
                    name: 'discordUser.id'
                },
                {
                    data: 'pterodactyl_id',
                    visible: false
                },
                {
                    data: 'ip',
                    visible: false
                },
                {
                    data: 'avatar',
                    sortable: false
                },
                {
                    data: 'name'
                },
                {
                    data: 'role'
                },
                {
                    data: 'email',
                    name: 'users.email'
                },
                {
                    data: 'credits',
                    name: 'users.credits'
                },
                {
                    data: 'servers_count',
                    searchable: false
                },
                {
                    data: 'referrals_count',
                    searchable: false
                },
                {
                    data: 'verified',
                    sortable: false
                },
                {
                    data: 'last_seen',
                },
                {
                    data: 'actions',
                    sortable: false
                },
            ],
            fnDrawCallback: function(oSettings) {
                $('[data-toggle="popover"]').popover();
            }
        });
    });
</script>
@endsection
