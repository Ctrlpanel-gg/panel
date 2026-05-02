@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('Application API')}}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">{{__('Dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.api.index')}}">{{__('Application API')}}</a>
                        </li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                                       href="{{route('admin.api.create')}}">{{__('Create')}}</a>
                        </li>
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
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{route('admin.api.store')}}" method="POST">
                                @csrf

                                <div class="form-group">
                                    <label for="memo">{{__('Memo')}}</label>
                                    <input value="{{old('memo')}}" id="memo" name="memo" type="text"
                                           class="form-control @error('memo') is-invalid @enderror">
                                    @error('memo')
                                    <div class="invalid-feedback">
                                        {{$message}}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="expires_at">{{__('Expires At (Optional)')}}</label>
                                    <input value="{{old('expires_at')}}" id="expires_at" name="expires_at" type="datetime-local"
                                           class="form-control @error('expires_at') is-invalid @enderror">
                                    @error('expires_at')
                                    <div class="invalid-feedback">
                                        {{$message}}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{__('Permissions (Optional - leave empty for full access)')}}</label>
                                    <div class="row">
                                        @foreach(\Spatie\Permission\Models\Permission::all() as $permission)
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]"
                                                           value="{{$permission->name}}" id="perm_{{$permission->id}}">
                                                    <label class="form-check-label" for="perm_{{$permission->id}}">
                                                        {{$permission->readable_name ?? $permission->name}}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('permissions')
                                    <div class="invalid-feedback d-block">
                                        {{$message}}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-primary">
                                        {{__('Submit')}}
                                    </button>
                                </div>

                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            </form>

                            @if(session('new_token'))
                                <div class="alert alert-success mt-3">
                                    <h5>{{__('API Token Created!')}}</h5>
                                    <p>{{__('Copy this token now. It will not be shown again!')}}</p>
                                    <code class="d-block p-2 bg-light">{{session('new_token')}}</code>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <!-- END CONTENT -->


@endsection
