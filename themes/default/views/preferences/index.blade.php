@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1>{{__('Preferences')}}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">{{__('Dashboard')}}</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">
            <!-- CUSTOM CONTENT -->
            <form method="post" action="{{ route('preferences.update') }}">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">{{__('Language')}}</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="locale">{{__('Language')}}</label>
                                    <select name="locale" id="locale" @if(!$localeSettings->clients_can_change) disabled @endif class="custom-select w-100">
                                        @foreach (explode(',', $localeSettings->available) as $key)
                                            <option value="{{ $key }}" @if(session('locale') == $key) selected @endif>{{ ucfirst($key) }}</option>
                                        @endforeach
                                    </select>
                                  @if(!$localeSettings->clients_can_change)
                                    <small><i class="fas fa-info-circle"></i> {{__("Changing the locale has been disabled by the System-Admins")}}</small>
                                    @endif
                                </div>
                                <div class="row">
                                    <div class="col d-flex justify-content-end">
                                        <button class="btn btn-primary" type="submit">
                                            {{ __('Save Changes') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <!-- END CUSTOM CONTENT -->
        </div>
    </section>
    <!-- END CONTENT -->

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            $('.custom-select').select2();
        })
    </script>
@endsection
