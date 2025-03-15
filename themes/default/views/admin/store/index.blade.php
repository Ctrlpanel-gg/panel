@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <div class="min-h-screen bg-primary-950 p-8">
        <!-- Header -->
        <div class="max-w-screen-xl mx-auto mb-8">
            <div class="glass-panel p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-light text-white">{{ __('Store') }}</h1>
                        <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 text-zinc-400">
                                <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                                <li class="text-zinc-600">/</li>
                                <li class="text-zinc-500">{{ __('Store') }}</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="{{ route('admin.store.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>{{ __('Create new') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-screen-xl mx-auto">
            <div class="glass-panel p-6">
                
                <div class="overflow-x-auto">
                    <table id="datatable" class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-zinc-400 bg-zinc-800/50">
                            <tr>
                                <th class="px-4 py-3">{{ __('Active') }}</th>
                                <th class="px-4 py-3">{{ __('Type') }}</th>
                                <th class="px-4 py-3">{{ __('Price') }}</th>
                                <th class="px-4 py-3">{{ __('Display') }}</th>
                                <th class="px-4 py-3">{{ __('Description') }}</th>
                                <th class="px-4 py-3">{{ __('Created at') }}</th>
                                <th class="px-4 py-3"></th>
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
        function submitResult() {
            return confirm("Are you sure you wish to delete?") !== false;
        }

        document.addEventListener("DOMContentLoaded", function() {
            $('#datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{ $locale_datatables }}.json'
                },
                processing: true,
                serverSide: true,
                stateSave: true,
                ajax: "{{ route('admin.store.datatable') }}",
                order: [
                    [2, "desc"]
                ],
                columns: [{
                        data: 'disabled'
                    },
                    {
                        data: 'type'
                    },
                    {
                        data: 'price'
                    },
                    {
                        data: 'display',
                        sortable: false
                    },
                    {
                        data: 'description',
                        sortable: false
                    },
                    {
                        data: 'created_at'
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
