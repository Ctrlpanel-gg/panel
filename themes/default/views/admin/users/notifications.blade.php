@extends('layouts.main')
@section('content')
    <!-- CONTENT HEADER -->
    <div class="min-h-screen bg-primary-950 p-4 sm:p-8">
        <!-- Header -->
        <header class="w-full mb-6 sm:mb-8">
            <div class="glass-panel p-4 sm:p-6">
                <h1 class="text-2xl sm:text-3xl font-light text-white">{{__('Users')}}</h1>
                <div class="text-zinc-400 text-sm mt-2">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{route('home')}}" class="inline-flex items-center text-sm font-medium text-zinc-400 hover:text-white">
                                    <i class="fas fa-home mr-2"></i>
                                    {{__('Dashboard')}}
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="fas fa-chevron-right text-zinc-600 text-xs mx-1"></i>
                                    <a href="{{route('admin.users.index')}}" class="ml-1 text-sm font-medium text-zinc-400 hover:text-white">
                                        {{__('Users')}}
                                    </a>
                                </div>
                            </li>
                            <li aria-current="page">
                                <div class="flex items-center">
                                    <i class="fas fa-chevron-right text-zinc-600 text-xs mx-1"></i>
                                    <span class="ml-1 text-sm font-medium text-zinc-500">
                                        {{__('Notifications')}}
                                    </span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="w-full">
            <div class="card glass-morphism">
                <div class="p-6 border-b border-zinc-800/50">
                    <h3 class="text-white font-medium flex items-center gap-2">
                        <i class="fas fa-bell text-zinc-400"></i>
                        {{__('Send Notifications')}}
                    </h3>
                </div>
                <div class="p-6">
                    <form action="{{route('admin.users.notifications.notify')}}" method="POST">
                        @csrf
                        @method('POST')

                        <div class="mb-6">
                            <div class="flex items-center mb-4">
                                <input id="all" name="all" type="checkbox" value="1" 
                                       onchange="toggleClass('users-form', 'd-none')"
                                       class="form-checkbox">
                                <label for="all" class="ml-2 text-zinc-300">{{__('All')}}</label>
                            </div>
                            
                            <div id="users-form" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-zinc-400 mb-2">{{__('Users')}}</label>
                                    <select id="users" name="users[]" class="form-select" multiple></select>
                                    @error('users')
                                        <div class="text-red-500 text-sm mt-1">
                                            {{$message}}
                                        </div>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-zinc-400 mb-2">{{__('Roles')}}</label>
                                    <select id="roles" name="roles[]" onchange="toggleClass('users', 'd-none')" class="form-select" multiple>
                                        @foreach($roles as $role)
                                            <option value="{{$role->id}}">{{$role->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @error('all')
                                <div class="text-red-500 text-sm mt-1">
                                    {{$message}}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-zinc-400 mb-2">{{__('Send via')}}</label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input value="database" id="database" name="via[]" type="checkbox" class="form-checkbox">
                                    <label for="database" class="ml-2 text-zinc-300">{{__('Database')}}</label>
                                </div>
                                <div class="flex items-center">
                                    <input value="mail" id="mail" name="via[]" type="checkbox" class="form-checkbox">
                                    <label for="mail" class="ml-2 text-zinc-300">{{__('Email')}}</label>
                                </div>
                            </div>
                            @error('via')
                                <div class="text-red-500 text-sm mt-1">
                                    {{$message}}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-zinc-400 mb-2">{{__('Title')}}</label>
                            <input value="{{old('title')}}" id="title" name="title" type="text" 
                                   class="form-input @error('title') border-red-500 @enderror">
                            @error('title')
                                <div class="text-red-500 text-sm mt-1">
                                    {{$message}}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="content" class="block text-sm font-medium text-zinc-400 mb-2">{{__('Content')}}</label>
                            <textarea id="content" name="content" class="form-textarea @error('content') border-red-500 @enderror">{{old('content')}}</textarea>
                            @error('content')
                                <div class="text-red-500 text-sm mt-1">
                                    {{$message}}
                                </div>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary">{{__('Submit')}}</button>
                        </div>

                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            // Summernote
            $('#content').summernote({
                height: 100,
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
                $('#roles').select2();
                $('#users').select2({
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
