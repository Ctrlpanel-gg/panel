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
                                                       href="{{route('admin.users.notifications')}}">Notifications</a></li>
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
                            <form action="{{route('admin.users.notifications')}}" method="POST">
                                @csrf
                                @method('POST')

                                <div class="form-group">
                                    <label>Users</label><br>
                                    <input id="all" name="all"
                                           type="checkbox"
                                           onchange="toggleClass('users-form', 'd-none')">
                                    <label for="all">All</label>
                                    <div id="users-form">
                                        <select id="users" name="users[]" class="form-control" multiple></select>
                                    </div>
                                    @error('all')
                                        <div class="invalid-feedback d-block">
                                            {{$message}}
                                        </div>
                                    @enderror
                                    @error('users')
                                        <div class="invalid-feedback d-block">
                                            {{$message}}
                                        </div>
                                    @enderror
                                </div>

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
                minimumInputLength: 2,
                templateResult: function (data) {
                    if (data.loading) return escapeHtml(data.text);
                    const $container = $(
                        "<div class='select2-result-repository clearfix' style='display:flex;'>" +
                            "<div class='select2-result-repository__avatar' style='display:flex;align-items:center;'><img class='img-circle img-bordered-s' src='" + data.avatarUrl + "?s=40' /></div>" +
                            "<div class='select2-result-repository__meta' style='margin-left:10px'>" +
                                "<div class='select2-result-repository__username' style='font-size:16px;'></div>" +
                                "<div class='select2-result-repository__email' style='font-size=13px;'></div>" +
                            "</div>" +
                        "</div>"
                    );

                    $container.find(".select2-result-repository__username").text(data.name);
                    $container.find(".select2-result-repository__email").text(data.email);

                    return $container;    
                    {{-- return $('<div class="user-block"> \
                        <img class="img-circle img-bordered-xs" src="' + escapeHtml(data.avatarUrl) + '?s=120" alt="User Image"> \
                        <span class="username"> \
                            <a href="#">' + escapeHtml(data.name) +'</a> \
                        </span> \
                        <span class="description"><strong>' + escapeHtml(data.email) + '</strong></span> \
                    </div>'); --}}
                },
                templateSelection: function (data) {
                        return $('<div> \
                                    <span> \
                                        <img class="img-rounded img-bordered-xs" src="' + escapeHtml(data.avatarUrl) + '?s=120" style="height:24px;margin-top:-4px;" alt="User Image"> \
                                    </span> \
                                    <span style="padding-left:10px;padding-right:10px;"> \
                                        ' + escapeHtml(data.name) +' \
                                    </span> \
                                </div>');
                    }
                })
            })

        function toggleClass(id, className) {
            document.getElementById(id).classList.toggle(className)
        }
        function escapeHtml(str) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }
    </script>


@endsection
