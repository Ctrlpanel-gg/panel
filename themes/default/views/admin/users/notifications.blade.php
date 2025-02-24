@extends('layouts.main')
@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="max-w-screen-2xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Send Notifications') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li><a href="{{ route('admin.users.index') }}" class="hover:text-white transition-colors">{{ __('Users') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Notifications') }}</li>
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
                    <i class="fas fa-bell mr-2 text-zinc-400"></i>
                    {{ __('Send Notification') }}
                </h5>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.users.notifications.notify') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Recipients Section -->
                        <div class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <input id="all" name="all" type="checkbox" value="1"
                                       onchange="toggleClass('users-form', 'd-none')">
                                <label for="all" class="text-zinc-300">{{ __('All Users') }}</label>
                            </div>
                            
                            <div id="users-form" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Select Users') }}</label>
                                    <select id="users" name="users[]" class="select2-users" multiple></select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Select Roles') }}</label>
                                    <select id="roles" name="roles[]" class="select2-basic" multiple>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Notification Method -->
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-2">{{ __('Send via') }}</label>
                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <input value="database" id="database" name="via[]" type="checkbox">
                                    <label for="database" class="text-zinc-300">{{ __('Database') }}</label>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input value="mail" id="mail" name="via[]" type="checkbox">
                                    <label for="mail" class="text-zinc-300">{{ __('Email') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message Content -->
                    <div class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Title') }}</label>
                            <input id="title" name="title" type="text" class="input" value="{{ old('title') }}">
                        </div>

                        <div>
                            <label for="content" class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Content') }}</label>
                            <textarea id="content" name="content" class="summernote">{{ old('content') }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary">{{ __('Send Notification') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Select2 Styling */
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
    .select2-container--default .select2-selection--multiple,
    .select2-container--default .select2-selection--single {
        background-color: #18181b !important;
        border: 1px solid #27272a !important;
        border-radius: 0.5rem !important;
        min-height: 42px !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #3b82f6 !important;
        border: none !important;
        color: white !important;
        border-radius: 0.375rem !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white !important;
        border-right: none !important;
    }
    
    /* Summernote Styling */
    .note-editor {
        background-color: #18181b !important;
    }
    .note-editing-area {
        background-color: #18181b !important;
    }
    .note-editable {
        background-color: #18181b !important;
        color: #e4e4e7 !important;
    }
    .note-toolbar {
        background-color: #27272a !important;
        border-bottom: 1px solid #3f3f46 !important;
    }
    .note-btn {
        background-color: #3f3f46 !important;
        color: #e4e4e7 !important;
        border: none !important;
    }
    .note-btn:hover {
        background-color: #52525b !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        // Initialize Select2 for users
        $('#users').select2({
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
                    return { results: data };
                },
                cache: true
            },
            placeholder: '{{ __("Search for users...") }}',
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

        // Initialize Select2 for roles
        $('#roles').select2({
            placeholder: '{{ __("Select roles...") }}',
            allowClear: true
        });

        // Initialize Summernote
        $('#content').summernote({
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview']]
            ]
        });
    });

    function toggleClass(id, className) {
        document.getElementById(id).classList.toggle(className);
    }
</script>

@endsection
