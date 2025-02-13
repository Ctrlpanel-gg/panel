@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <header class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-light text-white">{{__('Create Coupon')}}</h1>
                    <div class="text-zinc-400 text-sm mt-2">{{__('Create a new discount coupon')}}</div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div>
                <div class="glass-panel">
                    <div class="border-b border-zinc-800/50 p-6">
                        <h3 class="text-white font-medium flex items-center gap-2">
                            <i class="fas fa-ticket-alt text-zinc-400"></i>
                            {{__('Coupon Details')}}
                        </h3>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('admin.coupons.store') }}" method="POST" class="space-y-6">
                            @csrf
                            
                            <div class="flex justify-end">
                                <label class="flex items-center gap-2 text-zinc-400">
                                    <input type="checkbox" id="random_codes" name="random_codes" class="form-checkbox bg-zinc-800 border-zinc-700">
                                    <span>{{ __('Random Codes') }}</span>
                                    <i class="fas fa-info-circle" data-toggle="popover" data-trigger="hover" 
                                       data-content="{{__('Replace the creation of a single code with several at once with a custom field.')}}"></i>
                                </label>
                            </div>

                            <div id="range_codes_element" style="display: none;">
                                <label class="form-label">{{ __('Range Codes') }}</label>
                                <input type="number" id="range_codes" name="range_codes" 
                                       class="form-input" step="any" min="1" max="100">
                                @error('range_codes')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div id="coupon_code_element" class="form-group">
                                <label for="code" class="form-label">{{ __('Coupon Code') }}</label>
                                <input type="text" id="code" name="code" placeholder="SUMMER" 
                                       class="form-input @error('code') is-invalid @enderror" value="{{ old('code') }}">
                                @error('code')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="type" class="form-label">{{ __('Coupon Type') }}</label>
                                <select name="type" id="type" class="form-select @error('type') is_invalid @enderror" required>
                                    <option value="percentage" @if(old('type') == 'percentage') selected @endif>{{ __('Percentage') }}</option>
                                    <option value="amount" @if(old('type') == 'amount') selected @endif>{{ __('Amount') }}</option>
                                </select>
                                @error('type')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="value" class="form-label">{{ __('Coupon Value') }}</label>
                                <div class="flex">
                                    <input name="value" id="value" type="number" step="any" min="1" max="100" 
                                           class="form-input @error('value') is-invalid @enderror" value="{{ old('value') }}">
                                    <span id="input_percentage" class="input-group-text">%</span>
                                </div>
                                @error('value')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="max_uses" class="form-label">{{ __('Max uses') }}</label>
                                <input name="max_uses" id="max_uses" type="number" step="any" min="1" max="100" 
                                       class="form-input @error('max_uses') is-invalid @enderror" value="{{ old('max_uses') }}">
                                @error('max_uses')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="expires_at" class="form-label">{{ __('Expires at') }}</label>
                                <div class="flex">
                                    <input value="{{ old('expires_at') }}" name="expires_at" placeholder="yyyy-mm-dd hh:mm:ss" 
                                           type="text" class="form-input @error('expires_at') is-invalid @enderror datetimepicker-input" data-target="#expires_at">
                                    <div class="input-group-append" data-target="#expires_at" data-toggle="datetimepicker">
                                        <div class="input-group-text">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                    </div>
                                </div>
                                @error('expires_at')
                                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="btn-primary">
                                    {{__('Create Coupon')}}
                                </button>
                            </div>

                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#expires_at').datetimepicker({
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

                if ($('#code').val()) {
                    $('#code').prop('value', null)
                }

            } else {
                $('#coupon_code_element').prop('disabled', false).show()
                $('#range_codes_element').prop('disabled', true).hide()

                if ($('#range_codes').val()) {
                    $('#range_codes').prop('value', null)
                }
            }
        })

        $('#type').change(function() {
            if ($(this).val() == 'percentage') {
                $('#input_percentage').prop('disabled', false).show()
            } else {
                $('#input_percentage').prop('disabled', true).hide()
            }
        })
    })
</script>
@endsection
