@extends('layouts.main')
@section('content')
<div class="min-h-screen bg-primary-950 p-4 sm:p-8">
    <!-- Header -->
    <div class="w-full mb-8">
        <div class="glass-panel p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <div class="relative">
                        <div class="w-16 h-16 bg-primary-500/20 flex items-center justify-center rounded-xl">
                            <i class="fas fa-bell text-2xl text-primary-400"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-light text-white">{{ __('Send Notifications') }}</h1>
                        <div class="flex items-center gap-4 mt-2">
                            <div class="text-zinc-400 text-sm flex items-center gap-2">
                                <i class="fas fa-users"></i>
                                {{ __('Notify users about updates and announcements') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Form -->
    <div class="glass-panel">
        <div class="p-6 border-b border-zinc-800/50">
            <h3 class="text-lg font-medium text-white flex items-center gap-2">
                <i class="fas fa-paper-plane text-zinc-400"></i>
                {{ __('Compose Notification') }}
            </h3>
        </div>
        <div class="p-6">
            <form action="{{route('admin.users.notifications.notify')}}" method="POST" class="space-y-6">
                @csrf
                @method('POST')

                <!-- Recipients Section -->
                <div class="space-y-4">
                    <h4 class="text-lg font-medium text-white">{{ __('Recipients') }}</h4>
                    
                    <!-- All Users Checkbox -->
                    <div class="flex items-center gap-3 p-4 bg-zinc-800/30 rounded-lg">
                        <input id="all" name="all" type="checkbox" value="1" 
                               onchange="toggleClass('users-form', 'd-none')"
                               class="w-4 h-4 text-primary-600 bg-zinc-700 border-zinc-600 rounded focus:ring-primary-500 focus:ring-2">
                        <label for="all" class="text-white font-medium">{{ __('Send to All Users') }}</label>
                    </div>

                    <!-- Specific Users/Roles -->
                    <div id="users-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-300 mb-2">{{ __('Select Users') }}</label>
                            <select id="users" name="users[]" multiple 
                                    class="w-full bg-zinc-800 border border-zinc-700 text-white rounded-lg focus:ring-primary-500 focus:border-primary-500">
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-zinc-300 mb-2">{{ __('Select Roles') }}</label>
                            <select id="roles" name="roles[]" onchange="toggleClass('users', 'd-none')" multiple
                                    class="w-full bg-zinc-800 border border-zinc-700 text-white rounded-lg focus:ring-primary-500 focus:border-primary-500">
                                @foreach($roles as $role)
                                    <option value="{{$role->id}}">{{$role->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    @error('all')
                        <div class="text-red-400 text-sm mt-1">
                            {{$message}}
                        </div>
                    @enderror
                    @error('users')
                        <div class="text-red-400 text-sm mt-1">
                            {{$message}}
                        </div>
                    @enderror
                </div>

                <!-- Delivery Method Section -->
                <div class="space-y-4">
                    <h4 class="text-lg font-medium text-white">{{ __('Delivery Method') }}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center gap-3 p-4 bg-zinc-800/30 rounded-lg">
                            <input value="database" id="database" name="via[]" type="checkbox"
                                   class="w-4 h-4 text-primary-600 bg-zinc-700 border-zinc-600 rounded focus:ring-primary-500 focus:ring-2">
                            <label for="database" class="text-white font-medium flex items-center gap-2">
                                <i class="fas fa-database text-zinc-400"></i>
                                {{ __('Database Notification') }}
                            </label>
                        </div>
                        <div class="flex items-center gap-3 p-4 bg-zinc-800/30 rounded-lg">
                            <input value="mail" id="mail" name="via[]" type="checkbox"
                                   class="w-4 h-4 text-primary-600 bg-zinc-700 border-zinc-600 rounded focus:ring-primary-500 focus:ring-2">
                            <label for="mail" class="text-white font-medium flex items-center gap-2">
                                <i class="fas fa-envelope text-zinc-400"></i>
                                {{ __('Email Notification') }}
                            </label>
                        </div>
                    </div>
                    @error('via')
                        <div class="text-red-400 text-sm mt-1">
                            {{$message}}
                        </div>
                    @enderror
                </div>

                <!-- Message Content Section -->
                <div class="space-y-4">
                    <h4 class="text-lg font-medium text-white">{{ __('Message Content') }}</h4>
                    
                    <div>
                        <label for="title" class="block text-sm font-medium text-zinc-300 mb-2">{{ __('Title') }}</label>
                        <input value="{{old('title')}}" id="title" name="title" type="text"
                               class="w-full bg-zinc-800 border border-zinc-700 text-white rounded-lg focus:ring-primary-500 focus:border-primary-500 px-4 py-3 @error('title') border-red-500 @enderror">
                        @error('title')
                        <div class="text-red-400 text-sm mt-1">
                            {{$message}}
                        </div>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="content" class="block text-sm font-medium text-zinc-300 mb-2">{{ __('Content') }}</label>
                        <textarea id="content" name="content" type="content"
                                  class="w-full bg-zinc-800 border border-zinc-700 text-white rounded-lg focus:ring-primary-500 focus:border-primary-500 @error('content') border-red-500 @enderror">{{old('content')}}</textarea>
                        @error('content')
                        <div class="text-red-400 text-sm mt-1">
                            {{$message}}
                        </div>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-6 border-t border-zinc-800/50">
                    <button type="submit" class="btn btn-primary flex items-center gap-2">
                        <i class="fas fa-paper-plane"></i>
                        {{ __('Send Notification') }}
                    </button>
                </div>

                <input type="hidden" name="_token" value="{{ csrf_token() }}">
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        // Summernote
        $('#content').summernote({
            height: 200,
            toolbar: [
                [ 'style', [ 'style' ] ],
                [ 'font', [ 'bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear'] ],
                [ 'fontname', [ 'fontname' ] ],
                [ 'fontsize', [ 'fontsize' ] ],
                [ 'color', [ 'color' ] ],
                [ 'para', [ 'ol', 'ul', 'paragraph', 'height' ] ],
                [ 'table', [ 'table' ] ],
                [ 'insert', [ 'link'] ],
                [ 'view', [ 'undo', 'redo', 'fullscreen', 'codeview', 'help' ] ]
            ]
        })

        function initUserSelect(data) {
            $('#roles').select2({
                theme: 'default',
                placeholder: 'Select roles...',
                allowClear: true
            });
            $('#users').select2({
                theme: 'default',
                placeholder: 'Search users...',
                allowClear: true,
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
                minimumInputLength: 2,
                templateResult: function (data) {
                    if (data.loading) return data.text;
                    const $container = $(
                        "<div class='clearfix select2-result-users' style='display:flex;'>" +
                            "<div class='select2-result-users__avatar' style='display:flex;align-items:center;'><img class='img-circle img-bordered-s' src='" + data.avatarUrl + "?s=40' /></div>" +
                            "<div class='select2-result-users__meta' style='margin-left:10px'>" +
                                "<div class='select2-result-users__username' style='font-size:16px;'></div>" +
                                "<div class='select2-result-users__email' style='font-size=13px;'></div>" +
                            "</div>" +
                        "</div>"
                    );

                    $container.find(".select2-result-users__username").text(data.name);
                    $container.find(".select2-result-users__email").text(data.email);

                    return $container;
                },
                templateSelection: function (data) {
                        $container = $('<div> \
                                        <span> \
                                            <img class="img-rounded img-bordered-xs" src="' + data.avatarUrl + '?s=120" style="height:24px;margin-top:-4px;" alt="User Image"> \
                                        </span> \
                                        <span class="select2-selection-users__username" style="padding-left:10px;padding-right:10px;"></span> \
                                    </div>');
                        $container.find(".select2-selection-users__username").text(data.name);
                        return $container;
                    }
                })
            }
            initUserSelect()
        })

    function toggleClass(id, className) {
        document.getElementById(id).classList.toggle(className)
    }
</script>

@endsection
