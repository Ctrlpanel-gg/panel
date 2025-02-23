@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="max-w-screen-2xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ isset($role) ? __('Edit Role') : __('Create Role') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li><a href="{{ route('admin.roles.index') }}" class="hover:text-white transition-colors">{{ __('Roles List') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ isset($role) ? __('Edit Role') : __('Create Role') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto">
        <div class="glass-panel">
            <div class="p-6 border-b border-zinc-800/50">
                <h5 class="text-lg font-medium text-white flex items-center">
                    <i class="fas fa-user-check mr-2 text-zinc-400"></i>
                    {{ isset($role) ? __('Edit Role') : __('Create Role') }}
                </h5>
            </div>
            <div class="p-6">
                <form method="post" action="{{ isset($role) ? route('admin.roles.update', $role->id) : route('admin.roles.store') }}" 
                      class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    @csrf
                    @isset($role)
                        @method('PATCH')
                    @endisset

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Name') }}</label>
                            <input type="text" name="name" class="input" value="{{ isset($role) ? $role->name : null }}" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Badge Color') }}</label>
                            <input type="color" name="color" class="h-[42px] w-full rounded-lg bg-primary-950 border border-primary-800" 
                                   value="{{ isset($role) ? $role->color : '#3b82f6' }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Power') }}</label>
                            <input type="number" name="power" class="input" min="1" max="100" step="1" 
                                   value="{{ isset($role) ? $role->power : 10 }}" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Permissions') }}</label>
                        <select name="permissions[]" id="permissions" class="input" multiple style="height: 300px">
                            @foreach($permissions as $permission)
                                <option value="{{$permission->id}}" 
                                    @if(isset($role) && $role->permissions->contains($permission)) selected @endif>
                                    {{$permission->readable_name}}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-2 flex justify-end">
                        <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    select { width: 100%; }
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        background-color: rgb(9 9 11) !important;
        border: 1px solid rgb(39 39 42) !important;
        border-radius: 0.5rem !important;
        min-height: 42px !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        padding: 4px 8px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: rgb(212 212 216) !important;
        line-height: 42px !important;
        padding-left: 16px !important;
    }
    .select2-dropdown {
        background-color: rgb(9 9 11) !important;
        border: 1px solid rgb(39 39 42) !important;
    }
    .select2-search__field {
        background-color: rgb(24 24 27) !important;
        border-color: rgb(39 39 42) !important;
        color: white !important;
        padding: 4px 8px !important;
    }
    .select2-results__option {
        padding: 8px 16px !important;
        color: rgb(212 212 216) !important;
    }
    .select2-results__option--highlighted[aria-selected] {
        background-color: rgb(59 130 246 / 0.2) !important;
        color: rgb(147 197 253) !important;
    }
    .select2-results__option[aria-selected=true] {
        background-color: rgb(29 78 216 / 0.2) !important;
        color: rgb(147 197 253) !important;
    }
    .select2-selection__choice {
        background-color: rgb(29 78 216 / 0.2) !important;
        border: none !important;
        color: rgb(147 197 253) !important;
        border-radius: 4px !important;
        padding: 4px 8px !important;
        margin: 4px !important;
    }
    .select2-selection__choice__remove {
        color: rgb(147 197 253 / 0.6) !important;
        margin-right: 6px !important;
    }
    .select2-selection__choice__remove:hover {
        color: rgb(147 197 253) !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        $('#permissions').select2({
            closeOnSelect: false,
            width: '100%',
            maximumSelectionLength: -1
        });
    });
</script>

@endsection
