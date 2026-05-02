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
                                                        href="{{route('admin.api.edit', $applicationApi->token)}}">{{__('Edit')}}</a>
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
                            <form action="{{ route('admin.api.update', $applicationApi->token) }}" method="POST">
                                @csrf
                                @method('PATCH')

                                <div class="form-group">
                                    <label for="memo">{{__('Memo')}}</label>
                                    <input value="{{$applicationApi->memo}}" id="memo" name="memo" type="text"
                                           class="form-control @error('memo') is-invalid @enderror">
                                    @error('memo')
                                    <div class="invalid-feedback">
                                        {{$message}}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="is_active">{{__('Active')}}</label>
                                    <select id="is_active" name="is_active" class="form-control">
                                        <option value="1" {{$applicationApi->is_active ? 'selected' : ''}}>{{__('Yes')}}</option>
                                        <option value="0" {{!$applicationApi->is_active ? 'selected' : ''}}>{{__('No')}}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="expires_at">{{__('Expires At (Optional)')}}</label>
                                    <input value="{{$applicationApi->expires_at ? $applicationApi->expires_at->format('Y-m-d\TH:i') : ''}}"
                                           id="expires_at" name="expires_at" type="datetime-local"
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
                                        @foreach($permissions as $permission)
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]"
                                                           value="{{$permission->name}}" id="perm_{{$permission->id}}"
                                                           {{$applicationApi->permissions && in_array($permission->name, $applicationApi->permissions) ? 'checked' : ''}}>
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

                            <hr>

                            <form action="{{ route('admin.api.regenerate', $applicationApi->token) }}" method="POST"
                                  onsubmit="return confirm('{{__("Are you sure you want to regenerate this token? The old token will stop working immediately.")}}')">
                                @csrf
                                @method('PATCH')
                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-redo"></i> {{__('Regenerate Token')}}
                                    </button>
                                </div>
                            </form>

                            @if(session('new_token'))
                                <div class="alert alert-success mt-3">
                                    <h5>{{__('API Token Regenerated!')}}</h5>
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
