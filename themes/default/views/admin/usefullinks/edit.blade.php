@extends('layouts.main')

@section('content')
    <div class="min-h-screen bg-primary-950 p-8">
        <!-- Header -->
        <div class="max-w-screen-xl mx-auto mb-8">
            <div class="glass-panel p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-light text-white">{{__('Edit Useful Link')}}</h1>
                        <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 text-zinc-400">
                                <li><a href="{{route('home')}}" class="hover:text-white transition-colors">{{__('Dashboard')}}</a></li>
                                <li class="text-zinc-600">/</li>
                                <li><a href="{{route('admin.usefullinks.index')}}" class="hover:text-white transition-colors">{{__('Useful Links')}}</a></li>
                                <li class="text-zinc-600">/</li>
                                <li class="text-zinc-500">{{__('Edit')}}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-screen-xl mx-auto">
            <div class="glass-panel p-6">
                <form action="{{route('admin.usefullinks.update', $link->id)}}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-6 mb-6">
                        <div class="form-group">
                            <label class="block text-sm font-medium text-zinc-400 mb-2" for="icon">{{__('Icon class name')}}</label>
                            <input value="{{$link->icon}}" id="icon" name="icon"
                                   type="text"
                                   placeholder="fas fa-user"
                                   class="form-input @error('icon') is-invalid @enderror"
                                   required>
                            <p class="text-sm text-zinc-500 mt-1">
                                {{__('You can find available free icons')}} <a target="_blank" class="text-primary-400 hover:text-primary-300" href="https://fontawesome.com/v5.15/icons?d=gallery&p=2">here</a>
                            </p>
                            @error('icon')
                            <p class="text-red-500 text-xs mt-1">{{$message}}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-zinc-400 mb-2" for="title">{{__('Title')}}</label>
                            <input value="{{$link->title}}" id="title" name="title"
                                   type="text"
                                   class="form-input @error('title') is-invalid @enderror"
                                   required>
                            @error('title')
                            <p class="text-red-500 text-xs mt-1">{{$message}}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-zinc-400 mb-2" for="link">{{__('Link')}}</label>
                            <input value="{{$link->link}}" id="link" name="link"
                                   type="text"
                                   class="form-input @error('link') is-invalid @enderror"
                                   required>
                            @error('link')
                            <p class="text-red-500 text-xs mt-1">{{$message}}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-zinc-400 mb-2" for="description">{{__('Description')}}</label>
                            <textarea id="description"
                                      name="description"
                                      class="form-input @error('description') is-invalid @enderror">{{$link->description}}</textarea>
                            @error('description')
                            <p class="text-red-500 text-xs mt-1">{{$message}}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="block text-sm font-medium text-zinc-400 mb-2" for="position">{{__('Position')}}</label>
                            <select id="position" style="width:100%" class="custom-select" name="position[]"
                                    required multiple autocomplete="off" @error('position') is-invalid @enderror>
                                @foreach ($positions as $position)
                                    <option id="{{$position->value}}" value="{{ $position->value }}" 
                                            @if (strpos($link->position, $position->value) !== false) selected @endif>
                                        {{ __($position->value) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('position')
                            <p class="text-red-500 text-xs mt-1">{{$message}}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end mt-6">
                            <button type="submit" class="btn btn-primary">
                                {{__('Submit')}}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            $('.custom-select').select2();
            $('#description').summernote({
                height: 100,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
                    ['fontname', ['fontname']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ol', 'ul', 'paragraph', 'height']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['undo', 'redo', 'fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
@endsection
