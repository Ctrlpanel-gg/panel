@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <header class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <h1 class="text-3xl font-light text-white">{{ __('Ticket Blacklist') }}</h1>
            <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-zinc-400">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li class="text-zinc-500">{{ __('Ticket Blacklist') }}</li>
                </ol>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Blacklist Table -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-white font-medium flex items-center gap-2">
                        <i class="fas fa-user-times text-zinc-400"></i>
                        {{ __('Blacklisted Users') }}
                    </h3>
                </div>
                <div class="p-6">
                    <div class="relative overflow-x-auto">
                        <table id="datatable" class="w-full text-sm text-left text-zinc-400">
                            <thead class="text-xs uppercase bg-zinc-800/50">
                                <tr>
                                    <th class="px-6 py-3">{{__('User')}}</th>
                                    <th class="px-6 py-3">{{__('Status')}}</th>
                                    <th class="px-6 py-3">{{__('Reason')}}</th>
                                    <th class="px-6 py-3">{{__('Created At')}}</th>
                                    <th class="px-6 py-3">{{__('Actions')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add to Blacklist Form -->
        <div class="lg:col-span-1">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-white font-medium flex items-center gap-2">
                        <i class="fas fa-ban text-zinc-400"></i>
                        {{ __('Add To Blacklist') }}
                        <i data-toggle="popover"
                           data-trigger="hover"
                           data-content="{{__('please make the best of it')}}"
                           class="fas fa-info-circle text-zinc-500 text-sm cursor-help"></i>
                    </h3>
                </div>
                <div class="p-6">
                    <form action="{{route('admin.ticket.blacklist.add')}}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label class="flex items-center gap-2 text-sm font-medium text-zinc-400 mb-2">
                                {{ __('User') }}
                                <i data-toggle="popover"
                                   data-trigger="hover"
                                   data-content="{{ __('Please note, the blacklist will make the user unable to make a ticket/reply again') }}"
                                   class="fas fa-info-circle text-zinc-500 cursor-help"></i>
                            </label>
                            <select id="user_id"
                                    name="user_id"
                                    required
                                    class="w-full bg-zinc-800/50 border-zinc-700 rounded-lg text-zinc-300 focus:border-blue-500 focus:ring-blue-500">
                            </select>
                            @error('user_id')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="reason" class="block text-sm font-medium text-zinc-400 mb-2">{{ __('Reason') }}</label>
                            <input type="text"
                                   id="reason"
                                   name="reason"
                                   required
                                   placeholder="{{ __('Input Some Reason') }}"
                                   class="w-full bg-zinc-800/50 border-zinc-700 rounded-lg text-zinc-300 placeholder-zinc-500 focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <button type="submit" class="btn btn-primary w-full">
                            <i class="fas fa-ban mr-2"></i>{{ __('Add to Blacklist') }}
                        </button>
                    </form>
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
            ajax: "{{route('admin.ticket.blacklist.datatable')}}",
            columns: [
                {data: 'user', name: 'user.name'},
                {data: 'status'},
                {data: 'reason'},
                {data: 'created_at'},
                {data: 'actions', sortable: false},
            ],
            fnDrawCallback: function(oSettings) {
                $('[data-toggle="popover"]').popover();
            }
        });
    });

    function initUserIdSelect(data) {
        function escapeHtml(str) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }

        $('#user_id').select2({
            theme: 'dark',
            containerCssClass: 'bg-zinc-800 border-zinc-700 rounded-lg text-zinc-300',
            dropdownCssClass: 'bg-zinc-800 border-zinc-700 text-zinc-300',
            ajax: {
                url: '/admin/users.json',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        filter: { email: params.term },
                        page: params.page,
                    };
                },
                processResults: function (data, params) {
                    return { results: data };
                },
                cache: true,
            },
            data: data,
            escapeMarkup: function (markup) { return markup; },
            minimumInputLength: 2,
            templateResult: function (data) {
                if (data.loading) return escapeHtml(data.text);

                return `<div class="flex items-center gap-3 p-1">
                    <img class="w-8 h-8 rounded-full" src="${escapeHtml(data.avatarUrl)}?s=120" alt="User Image">
                    <div>
                        <div class="font-medium text-white">${escapeHtml(data.name)}</div>
                        <div class="text-sm text-zinc-400">${escapeHtml(data.email)}</div>
                    </div>
                </div>`;
            },
            templateSelection: function (data) {
                return `<div class="flex items-center gap-2">
                    <img class="w-6 h-6 rounded-full" src="${escapeHtml(data.avatarUrl)}?s=120" alt="User Image">
                    <span class="text-zinc-300">${escapeHtml(data.name)} (${escapeHtml(data.email)})</span>
                </div>`;
            }
        });
    }

    $(document).ready(function() {
        @if (old('user_id'))
            $.ajax({
                url: '/admin/users.json?user_id={{ old('user_id') }}',
                dataType: 'json',
            }).then(function (data) {
                initUserIdSelect([data]);
            });
        @else
            initUserIdSelect();
        @endif
    });
</script>
@endsection
