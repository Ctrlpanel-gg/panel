@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Users</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.users.index')}}">Users</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                                       href="{{route('admin.users.notifications' , $user->id)}}">Notifications</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{route('admin.users.notifications', $user->id)}}" method="POST">
                                @csrf
                                @method('POST')

                                <div class="form-group">
                                    <label>Send via</label><br>
                                    <input value="database" id="database" name="via[]"
                                           type="checkbox">
                                    <label for="database">Database</label>
                                    <br>
                                    <input value="mail" id="mail" name="via[]"
                                           type="checkbox">
                                    <label for="mail">Mail</label>
                                    @error('via')
                                        <div class="invalid-feedback d-block">
                                            {{$message}}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group" >
                                    <label for="title">Title</label>
                                    <input value="{{old('title')}}" id="title" name="title"
                                        type="text"
                                        class="form-control @error('title') is-invalid @enderror">
                                    @error('title')
                                    <div class="invalid-feedback">
                                        {{$message}}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="content">Content</label>
                                    <textarea id="content"
                                            name="content"
                                            type="content"
                                            class="form-control @error('content') is-invalid @enderror">
                                        {{old('content')}}
                                    </textarea>
                                    @error('content')
                                    <div class="text-danger">
                                        {{$message}}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </section>
    <!-- END CONTENT -->
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
        })
    </script>


@endsection
