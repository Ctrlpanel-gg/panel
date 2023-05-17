@extends('layouts.main')

@section('content')
   <!-- CONTENT HEADER -->
   <section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{__('Coupon')}}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{route('home')}}">{{__('Dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a href="{{route('admin.coupons.index')}}">{{__('Coupons')}}</a>
                    </li>
                    <li class="breadcrumb-item"><a class="text-muted"
                                                   href="{{route('admin.coupons.create')}}">{{__('Create')}}</a>
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
                <i class="nav-icon fas fa-ticket-alt"></i>
                {{__('Coupon Details')}}
              </h5>
            </div>
            <div class="card-body">
              <form action="{{ route('admin.coupons.store') }}" method="POST">
                @csrf

                <div class="d-flex flex-row-reverse">
                  <div class="custom-control custom-switch">
                    <input
                      type="checkbox"
                      id="random_codes"
                      name="random_codes"
                      class="custom-control-input"
                    >
                    <label for="random_codes" class="custom-control-label">
                      {{ __('Random Codes') }}
                      <i
                        data-toggle="popover"
                        data-trigger="hover"
                        data-content="{{__('Replace the creation of a single code with several at once with a custom field.')}}"
                        class="fas fa-info-circle">
                      </i>
                    </label>
                  </div>
                </div>
                <div id="range_codes_element" style="display: none;" class="form-group">
                  <label for="range_codes">
                    {{ __('Range Codes') }}
                    <i
                      data-toggle="popover"
                      data-trigger="hover"
                      data-content="{{__('Generate a number of random codes.')}}"
                      class="fas fa-info-circle">
                    </i>
                  </label>
                  <input
                    type="number"
                    id="range_codes"
                    name="range_codes"
                    step="any"
                    min="1"
                    max="100"
                    class="form-control @error('range_codes') is-invalid @enderror"
                  >
                  @error('range_codes')
                    <div class="text-danger">
                      {{ $message }}
                    </div>
                  @enderror
                </div>
                <div id="coupon_code_element" class="form-group">
                  <label for="coupon_code">
                    {{ __('Coupon Code') }}
                    <i
                      data-toggle="popover"
                      data-trigger="hover"
                      data-content="{{__('The coupon code to be registered.')}}"
                      class="fas fa-info-circle">
                    </i>
                  </label>
                  <input
                    type="text"
                    id="coupon_code"
                    name="coupon_code"
                    placeholder="SUMMER"
                    class="form-control @error('coupon_code') is-invalid @enderror"
                    value="{{ old('coupon_code') }}"
                  >
                  @error('coupon_code')
                    <div class="text-danger">
                      {{ $message }}
                    </div>
                  @enderror
                </div>
                <div class="form-group">
                  <div class="custom-control mb-3 p-0">
                    <label for="coupon_type">
                      {{ __('Coupon Type') }}
                      <i
                        data-toggle="popover"
                        data-trigger="hover"
                        data-content="{{__('The way the coupon should discount.')}}"
                        class="fas fa-info-circle">
                      </i>
                    </label>
                    <select
                      name="coupon_type"
                      id="coupon_type"
                      class="custom-select @error('coupon_type') is_invalid @enderror"
                      style="width: 100%; cursor: pointer;"
                      autocomplete="off"
                      required
                    >
                      <option value="percentage" @if(old('coupon_type') == 'percentage') selected @endif>{{ __('Percentage') }}</option>
                      <option value="amount" @if(old('coupon_type') == 'amount') selected @endif>{{ __('Amount') }}</option>
                    </select>
                    @error('coupon_type')
                      <div class="text-danger">
                        {{ $message }}
                      </div>
                    @enderror
                  </div>
                </div>
                <div class="form-group">
                  <div class="input-group d-flex flex-column">
                    <label for="coupon_value">
                      {{ __('Coupon Value') }}
                      <i
                        data-toggle="popover"
                        data-trigger="hover"
                        data-content="{{__('The value that the coupon will represent.')}}"
                        class="fas fa-info-circle">
                      </i>
                    </label>
                    <div class="d-flex">
                      <input
                        name="coupon_value"
                        id="coupon_value"
                        type="number"
                        step="any"
                        min="1"
                        max="100"
                        class="form-control @error('coupon_value') is-invalid @enderror"
                        value="{{ old('coupon_value') }}"
                      >
                      <span id="input_percentage" class="input-group-text">%</span>
                    </div>
                    @error('coupon_value')
                      <div class="text-danger">
                        {{ $message }}
                      </div>
                    @enderror
                  </div>
                </div>
                <div class="form-group">
                  <label for="coupon_uses">
                    {{ __('Max uses') }}
                    <i
                      data-toggle="popover"
                      data-trigger="hover"
                      data-content="{{__('The maximum number of times the coupon can be used.')}}"
                      class="fas fa-info-circle">
                    </i>
                  </label>
                  <input
                    name="coupon_uses"
                    id="coupon_uses"
                    type="number"
                    step="any"
                    min="1"
                    max="100"
                    class="form-control @error('coupon_uses') is-invalid @enderror"
                    value="{{ old('coupon_uses') }}"
                  >
                  @error('coupon_uses')
                    <div class="text-danger">
                      {{ $message }}
                    </div>
                  @enderror
                </div>
                <div class="d-flex flex-column input-group form-group date" id="datepicker" data-target-input="nearest">
                  <label for="datepicker">
                    {{ __('Expires at') }}
                    <i
                      data-toggle="popover"
                      data-trigger="hover"
                      data-content="{{__('The date when the coupon will expire.')}}"
                      class="fas fa-info-circle">
                    </i>
                  </label>
                  <div class="d-flex">
                    <input
                      value="{{old('datepicker')}}"
                      name="datepicker"
                      placeholder="yyyy-mm-dd hh:mm:ss"
                      type="text"
                      class="form-control @error('datepicker') is-invalid @enderror datetimepicker-input"
                      data-target="#datepicker"
                    />
                    <div
                      class="input-group-append"
                      data-target="#datepicker"
                      data-toggle="datetimepicker"
                    >
                      <div class="input-group-text">
                        <i class="fa fa-calendar"></i>
                      </div>
                    </div>
                  </div>
                  @error('datepicker')
                    <div class="text-danger">
                      {{ $message }}
                    </div>
                  @enderror
                </div>
                <div class="form-group text-right mb-0">
                  <button type="submit" class="btn btn-primary">
                    {{__('Submit')}}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
   <!-- END CONTENT -->

  <script>
    $(document).ready(function() {
      $('#datepicker').datetimepicker({
        format: 'Y-MM-DD HH:mm:ss',
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
      $('#random_codes').change(function() {
        if ($(this).is(':checked')) {
          $('#coupon_code_element').prop('disabled', true).hide()
          $('#range_codes_element').prop('disabled', false).show()

          if ($('#coupon_code').val()) {
            $('#coupon_code').prop('value', null)
          }

        } else {
          $('#coupon_code_element').prop('disabled', false).show()
          $('#range_codes_element').prop('disabled', true).hide()

          if ($('#range_codes').val()) {
            $('#range_codes').prop('value', null)
          }
        }
      })

      $('#coupon_type').change(function() {
        if ($(this).val() == 'percentage') {
          $('#input_percentage').prop('disabled', false).show()
        } else {
          $('#input_percentage').prop('disabled', true).hide()
        }
      })
    })
  </script>
@endsection
