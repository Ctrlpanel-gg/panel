@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <header class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <h1 class="text-3xl font-light text-white">{{ __('Ticket Categories') }}</h1>
            <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-zinc-400">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li class="text-zinc-500">{{ __('Categories') }}</li>
                </ol>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Categories Table -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-white font-medium flex items-center gap-2">
                        <i class="fas fa-list text-zinc-400"></i>
                        {{ __('Categories') }}
                    </h3>
                </div>
                <div class="p-6">
                    <div class="relative overflow-x-auto">
                        <table id="datatable" class="w-full text-sm text-left text-zinc-400">
                            <thead class="text-xs uppercase bg-zinc-800/50">
                                <tr>
                                    <th class="px-6 py-3">{{__('ID')}}</th>
                                    <th class="px-6 py-3">{{__('Name')}}</th>
                                    <th class="px-6 py-3">{{__('Tickets')}}</th>
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

        <!-- Forms Column -->
        <div class="space-y-8">
            <!-- Add Category Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-white font-medium flex items-center gap-2">
                        <i class="fas fa-plus text-zinc-400"></i>
                        {{ __('Add Category') }}
                    </h3>
                </div>
                <div class="p-6">
                    <form action="{{route('admin.ticket.category.store')}}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="name" class="block text-sm font-medium text-zinc-400 mb-2">{{ __('Name') }}</label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   required
                                   class="w-full bg-zinc-800/50 border-zinc-700 rounded-lg text-zinc-300 placeholder-zinc-500 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <button type="submit" class="btn btn-primary w-full">
                            <i class="fas fa-plus mr-2"></i>{{ __('Add Category') }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Edit Category Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-white font-medium flex items-center gap-2">
                        <i class="fas fa-edit text-zinc-400"></i>
                        {{ __('Edit Category') }}
                    </h3>
                </div>
                <div class="p-6">
                    <form action="{{route('admin.ticket.category.update', '1')}}" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')
                        
                        <div>
                            <label for="category" class="block text-sm font-medium text-zinc-400 mb-2">{{ __('Select Category') }}</label>
                            <select id="category" 
                                    name="category" 
                                    required
                                    class="w-full bg-zinc-800/50 border-zinc-700 rounded-lg text-zinc-300 focus:border-blue-500 focus:ring-blue-500">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ __($category->name) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-zinc-400 mb-2">{{ __('New Name') }}</label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   required
                                   class="w-full bg-zinc-800/50 border-zinc-700 rounded-lg text-zinc-300 placeholder-zinc-500 focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <button type="submit" class="btn btn-primary w-full">
                            <i class="fas fa-save mr-2"></i>{{ __('Update Category') }}
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
            order: [[0, 'desc']],
            fnDrawCallback: function(oSettings) {
                $('[data-toggle="popover"]').popover();
            }
        });

        $('.custom-select').select2({
            theme: 'dark',
            containerCssClass: 'bg-zinc-800 border-zinc-700 rounded-lg text-zinc-300',
            dropdownCssClass: 'bg-zinc-800 border-zinc-700 text-zinc-300',
        });
    });
</script>
@endsection
