@extends('layouts.main')

@section('content')
    <div class="py-4 main">

        <div class="border-0 shadow card card-body table-wrapper table-responsive">
            <h2 class="mb-4 h5">{{ isset($role) ?  __('Edit role') : __('Create role') }}</h2>

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
            </form>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            $('#permissions').select2({
              closeOnSelect: false
            });
        })
    </script>
@endsection

