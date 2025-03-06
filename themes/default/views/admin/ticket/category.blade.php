@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="max-w-screen-2xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Ticket Categories') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Ticket Categories') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Categories Table -->
            <div class="lg:col-span-8">
                <div class="glass-panel">
                    <div class="p-6 border-b border-zinc-800/50">
                        <h5 class="text-lg font-medium text-white flex items-center">
                            <i class="fas fa-list mr-2 text-zinc-400"></i>
                            {{__('Categories')}}
                        </h5>
                    </div>
                    <div class="p-6">
                        <table id="datatable" class="w-full">
                            <thead>
                                <tr class="text-left text-zinc-400">
                                    <th class="px-2 py-3">{{__('ID')}}</th>
                                    <th class="px-2 py-3">{{__('Name')}}</th>
                                    <th class="px-2 py-3">{{__('Tickets')}}</th>
                                    <th class="px-2 py-3">{{__('Created At')}}</th>
                                    <th class="px-2 py-3">{{__('Actions')}}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Category Forms -->
            <div class="lg:col-span-4 space-y-8">
                <!-- Add Category Form -->
                <div class="glass-panel">
                    <div class="p-6 border-b border-zinc-800/50">
                        <h5 class="text-lg font-medium text-white flex items-center">
                            <i class="fas fa-plus mr-2 text-zinc-400"></i>
                            {{__('Add Category')}}
                        </h5>
                    </div>
                    <div class="p-6">
                        <form action="{{route('admin.ticket.category.store')}}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label for="name" class="block text-sm font-medium text-zinc-400 mb-1">{{__("Name")}}</label>
                                <input id="name" type="text" class="input" name="name" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-full">
                                {{__('Submit')}}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Edit Category Form -->
                <div class="glass-panel">
                    <div class="p-6 border-b border-zinc-800/50">
                        <h5 class="text-lg font-medium text-white flex items-center">
                            <i class="fas fa-edit mr-2 text-zinc-400"></i>
                            {{__('Edit Category')}}
                        </h5>
                    </div>
                    <div class="p-6">
                        <form action="{{route('admin.ticket.category.update', '1')}}" method="POST" class="space-y-4">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label for="category" class="block text-sm font-medium text-zinc-400 mb-1">{{__("Select Category")}}</label>
                                <select id="category" class="input" name="category" required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ __($category->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="name" class="block text-sm font-medium text-zinc-400 mb-1">{{__("New Name")}}</label>
                                <input id="name" type="text" class="input" name="name" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-full">
                                {{__('Update')}}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        $('#datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{config("SETTINGS::LOCALE:DATATABLES")}}.json'
            },
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax: "{{route('admin.ticket.category.datatable')}}",
            columns: [
                {data: 'id'},
                {data: 'name'},
                {data: 'tickets'},
                {data: 'created_at', sortable: false},
                {data: 'actions', sortable: false},
            ],
            fnDrawCallback: function( oSettings ) {
                $('[data-toggle="popover"]').popover();
            }
        });

        // Initialize Select2
        $('.input[name="category"]').select2({
            theme: 'default select2-dark',
            containerCssClass: 'select2-dark',
            dropdownCssClass: 'select2-dark',
        });
    });
</script>

<style>
    /* Dark theme for Select2 */
    .select2-dark {
        @apply bg-primary-950 border-primary-800 text-white;
    }
    .select2-container--default .select2-dark .select2-selection--single {
        @apply bg-primary-950 border-primary-800 text-white;
    }
    .select2-container--default .select2-dark .select2-selection__rendered {
        @apply text-white;
    }
    .select2-dropdown {
        @apply bg-primary-900 border-primary-800;
    }
    .select2-container--default .select2-results__option[aria-selected=true] {
        @apply bg-primary-800;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        @apply bg-primary-700;
    }
</style>
@endsection
