@extends('layouts.main')

@section('content')
    <div class="min-h-screen bg-primary-950 p-8">
        <!-- Header -->
        <div class="w-full mb-8">
            <div class="glass-panel p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-light text-white">{{__('Vouchers')}}</h1>
                        <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 text-zinc-400">
                                <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{__('Dashboard')}}</a></li>
                                <li class="text-zinc-600">/</li>
                                <li><a href="{{ route('admin.vouchers.index') }}" class="hover:text-white transition-colors">{{__('Vouchers')}}</a></li>
                                <li class="text-zinc-600">/</li>
                                <li class="text-zinc-500">{{__('Users')}}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="w-full">
            <div class="glass-panel p-6">
                <h2 class="text-xl font-medium text-white mb-6">
                    <i class="fas fa-users mr-2"></i>{{__('Users')}}
                </h2>
                
                <div class="overflow-x-auto">
                    <table id="datatable" class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-zinc-400 bg-zinc-800/50">
                            <tr>
                                <th class="px-4 py-3">{{__('ID')}}</th>
                                <th class="px-4 py-3">{{__('Name')}}</th>
                                <th class="px-4 py-3">{{__('Email')}}</th>
                                <th class="px-4 py-3">{{ $credits_display_name }}</th>
                                <th class="px-4 py-3">{{__('Last seen')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            $('#datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{ $locale_datatables }}.json'
                },
                processing: true,
                serverSide: true,
                stateSave: true,
                ajax: "{{ route('admin.vouchers.usersdatatable', $voucher->id) }}",
                columns: [{
                        data: 'id'
                    }, {
                        data: 'name'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'credits'
                    },
                    {
                        data: 'last_seen'
                    },
                ],
                fnDrawCallback: function(oSettings) {
                    $('[data-toggle="popover"]').popover();
                }
            });
        });
    </script>
@endsection
