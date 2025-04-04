@extends('layouts.main')

@section('content')
    <div class="min-h-screen bg-primary-950 p-8">
        <!-- Header -->
        <div class="max-w-screen-xl mx-auto mb-8">
            <div class="glass-panel p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-light text-white">{{__('Useful Links')}}</h1>
                        <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 text-zinc-400">
                                <li><a href="{{route('home')}}" class="hover:text-white transition-colors">{{__('Dashboard')}}</a></li>
                                <li class="text-zinc-600">/</li>
                                <li class="text-zinc-500">{{__('Useful Links')}}</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="{{route('admin.usefullinks.create')}}" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>{{__('Create new')}}
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
                                <th class="px-4 py-3">{{__('Description')}}</th>
                                <th class="px-4 py-3">{{__('Icon')}}</th>
                                <th class="px-4 py-3">{{__('Title')}}</th>
                                <th class="px-4 py-3">{{__('Link')}}</th>
                                <th class="px-4 py-3">{{__('Position')}}</th>
                                <th class="px-4 py-3">{{__('Created at')}}</th>
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
        document.addEventListener("DOMContentLoaded", function () {
            $('#datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{ $locale_datatables }}.json'
                },
                processing: true,
                serverSide: true,
                stateSave: true,
                ajax: "{{route('admin.usefullinks.datatable')}}",
                order: [[ 1, "asc" ]],
                columns: [
                    {data: 'description' ,visible: false},
                    {data: 'icon'},
                    {data: 'title'},
                    {data: 'link'},
                    {data: 'position'},
                    {data: 'created_at'},
                    {data: 'actions', sortable: false},
                ],
                fnDrawCallback: function( oSettings ) {
                    $('[data-toggle="popover"]').popover();
                }
            });
        });
    </script>
@endsection
