@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('Vouchers')}}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">{{__('Dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.partners.index')}}">{{__('Vouchers')}}</a>
                        </li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                                       href="{{route('admin.partners.create')}}">{{__('Create')}}</a>
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
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-handshake mr-2"></i>{{__('Partner details')}}
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{route('admin.partners.store')}}" method="POST">
                                @csrf

                                <div class="form-group">
                                    <label for="user_id">{{__('User')}}</label>
                                    <select id="user_id" style="width:100%"
                                            class="custom-select @error('user') is-invalid @enderror" name="user_id" autocomplete="off">
                                        @foreach($users as $user)
                                            <option @if($partners->contains('user_id' , $user->id)) disabled @endif
                                                value="{{$user->id}}">{{$user->name}} ({{$user->email}})</option>
                                        @endforeach
                                    </select>
                                    @error('user')
                                    <div class="text-danger">
                                        {{$message}}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="partner_discount">{{__('Partner discount')}}</label>
                                    <input value="{{old('partner_discount')}}" placeholder="{{__('Discount in percent')}}" id="partner_discount" name="partner_discount"
                                           type="number" step="any" min="0" max="100"
                                           class="form-control @error('partner_discount') is-invalid @enderror">
                                    @error('partner_discount')
                                    <div class="text-danger">
                                        {{$message}}
                                    </div>
                                    @enderror
                                </div>


                                <div class="form-group">
                                    <label for="registered_user_discount">{{__('Registered user discount')}}</label>
                                    <div class="input-group">
                                        <input value="{{old('registered_user_discount')}}" placeholder="Discount in percent" id="registered_user_discount" name="registered_user_discount"
                                               type="number" class="form-control @error('registered_user_discount') is-invalid @enderror"
                                               required="required">
                                    </div>
                                @error('registered_user_discount')
                                    <div class="text-danger">
                                        {{$message}}
                                    </div>
                                @enderror
                                </div>

                                <div class="form-group">
                                    <label for="referral_system_commission">{{__('Referral system commission')}}
                                        <i data-toggle="popover" data-trigger="hover"
                                        data-content="{{__('Override value for referral system commission. You can set it to -1 to get the default commission from settings.')}}"
                                        class="fas fa-info-circle"></i>
                                    </label>
                                    <input value="{{old('referral_system_commission')}}" placeholder="{{__('Commission in percent')}}" id="referral_system_commission" name="referral_system_commission"
                                           type="number" step="any" min="-1" max="100"
                                           class="form-control @error('referral_system_commission') is-invalid @enderror">
                                    @error('referral_system_commission')
                                    <div class="text-danger">
                                        {{$message}}
                                    </div>
                                    @enderror
                                </div>

                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-primary">
                                        {{__('Submit')}}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <i class="fas"></i>

        </div>
    </section>
    <!-- END CONTENT -->


    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            $('#expires_at').datetimepicker({
                format: 'DD-MM-yyyy HH:mm:ss',
                icons: {
                    time: 'far fa-clock',
                    date: 'far fa-calendar',
                    up: 'fas fa-arrow-up',
                    down: 'fas fa-arrow-down',
                    previous: 'fas fa-chevron-left',
                    next: 'fas fa-chevron-right',
                    today: 'fas fa-calendar-check',
                    clear: 'far fa-trash-alt',
                    close: 'far fa-times-circle'
                }
            });
        })
    </script>


@endsection