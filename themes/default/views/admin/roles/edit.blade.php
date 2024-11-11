@extends('layouts.main')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1>{{ isset($role) ?  __('Edit role') : __('Create role') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">{{ __('Roles List') }}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                href="{{ isset($role) ?  route('admin.roles.edit', $role->id) : route('admin.roles.create') }}">{{ isset($role) ?  __('Edit role') : __('Create role') }}</a>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h5 class="card-title"><i class="mr-2 fas fa-user-check"></i>{{ isset($role) ?  __('Edit role') : __('Create role') }}</h5>
                    </div>
                </div>
                <div class="card-body">
                        <div class="p-0 col-12">
                            <form method="post"
                  action="{{isset($role) ? route('admin.roles.update', $role->id) : route('admin.roles.store')}}">
                @csrf
                @isset($role)
                    @method('PATCH')
                @endisset

                <div class="row">
                    <div class="col-lg-6">

                        <x-input.text label="{{(__('Name'))}}"
                                      name="name"
                                      value="{{ isset($role) ? $role->name : null}}"/>

                        <x-input.text label="{{(__('Badge color'))}}"
                                      type="color"
                                      name="color"
                                      value="{{ isset($role) ? $role->color : null}}"/>

                        <x-input.number label="{{(__('Power'))}}"
                                      name="power"
                                        min="1"
                                        max="100"
                                        step="1"
                                      value="{{ isset($role) ? $role->power : 10}}"/>

                    </div>

                    <div class="col-lg-6">

                        <x-input.select
                            label="{{(__('Permissions'))}}"
                            name="permissions"
                            style="height: 200px"
                            multiple>
                            @foreach($permissions as $permission)
                                <option @if(isset($role) && $role->permissions->contains($permission)) selected
                                        @endif value="{{$permission->id}}">{{$permission->readable_name}}</option>
                            @endforeach
                        </x-input.select>

                    </div>
                </div>

                <div class="mt-3 form-group d-flex justify-content-end">
                    <button name="submit" type="submit" class="btn btn-primary">{{__('Submit')}}</button>
                </div>

                <input type="hidden" name="_token" value="{{ csrf_token() }}">
            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            $('#permissions').select2({
              closeOnSelect: false
            });
        })
    </script>
@endsection
