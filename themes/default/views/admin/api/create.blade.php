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
                                    <label for="expires_at">{{ __('Expires At') }}</label>
                                    <input value="{{ old('expires_at') }}" id="expires_at" name="expires_at" type="datetime-local"
                                           class="form-control @error('expires_at') is-invalid @enderror">
                                    @error('expires_at')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ __('Scopes') }}</label>
                                    @foreach($availableAbilities as $group => $abilities)
                                        <div class="mb-2 card card-body">
                                            <strong class="mb-2">{{ __($group) }}</strong>
                                            @foreach($abilities as $ability => $label)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="abilities[]" value="{{ $ability }}"
                                                           id="ability_{{ str_replace('.', '_', $ability) }}"
                                                           {{ in_array($ability, old('abilities', \App\Models\ApplicationApi::availableAbilities()), true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="ability_{{ str_replace('.', '_', $ability) }}">
                                                        {{ __($label) }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                    @error('abilities')
                                    <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                    @error('abilities.*')
                                    <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-primary">
                                        {{__('Submit')}}
                                    </button>
                                </div>

                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <!-- END CONTENT -->



@endsection
