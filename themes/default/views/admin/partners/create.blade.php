@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-4 sm:p-8">
    <div class="max-w-screen-2xl mx-auto">
        <!-- Header -->
        <div class="glass-panel p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-light text-white mb-2">{{ __('Create Partner') }}</h1>
                    <nav class="text-zinc-400 text-sm" aria-label="Breadcrumb">
                        <ol class="list-none p-0 inline-flex">
                            <li class="flex items-center">
                                <a href="{{ route('home') }}">{{ __('Dashboard') }}</a>
                                <span class="mx-2">/</span>
                            </li>
                            <li class="flex items-center">
                                <a href="{{ route('admin.partners.index') }}">{{ __('Partners') }}</a>
                                <span class="mx-2">/</span>
                            </li>
                            <li class="flex items-center text-zinc-500">
                                {{ __('Create') }}
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Create Form -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="text-white flex items-center gap-2">
                        <i class="fas fa-handshake"></i>{{ __('Partner details') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.partners.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="user_id" class="block text-sm font-medium text-zinc-400 mb-2">
                                {{ __('User') }}
                            </label>
                            <select id="user_id" name="user_id" required class="form-select">
                            </select>
                            @error('user_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="partner_discount" class="block text-sm font-medium text-zinc-400 mb-2">
                                {{ __('Partner discount') }}
                                <i class="fas fa-info-circle ml-1" data-toggle="popover" data-trigger="hover" 
                                   data-content="{{ __('The discount in percent given to the partner when purchasing credits.') }}"></i>
                            </label>
                            <input type="number" step="any" min="0" max="100" id="partner_discount" name="partner_discount" 
                                   class="form-input" value="{{ old('partner_discount') }}" 
                                   placeholder="{{ __('Discount in percent') }}">
                            @error('partner_discount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="registered_user_discount" class="block text-sm font-medium text-zinc-400 mb-2">
                                {{ __('Registered user discount') }}
                                <i class="fas fa-info-circle ml-1" data-toggle="popover" data-trigger="hover"
                                   data-content="{{ __('The discount in percent given to all users registered using the partners referral link when purchasing credits.') }}"></i>
                            </label>
                            <input type="number" id="registered_user_discount" name="registered_user_discount" 
                                   class="form-input" value="{{ old('registered_user_discount') }}" 
                                   placeholder="{{ __('Discount in percent') }}" required>
                            @error('registered_user_discount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="referral_system_commission" class="block text-sm font-medium text-zinc-400 mb-2">
                                {{ __('Referral system commission') }}
                                <i class="fas fa-info-circle ml-1" data-toggle="popover" data-trigger="hover"
                                   data-content="{{ __('Override value for referral system commission. You can set it to -1 to get the default commission from settings.') }}"></i>
                            </label>
                            <input type="number" step="any" min="-1" max="100" id="referral_system_commission" 
                                   name="referral_system_commission" class="form-input" 
                                   value="{{ old('referral_system_commission') }}" 
                                   placeholder="{{ __('Commission in percent') }}">
                            @error('referral_system_commission')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Submit') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

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
});

function initUserIdSelect(data) {
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    $('#user_id').select2({
        ajax: {
            url: '/admin/users.json',
            dataType: 'json',
            delay: 250,

            data: function (params) {
                return {
                    filter: { name: params.term },
                    page: params.page,
                };
            },

            processResults: function (data, params) {
                return { results: data };
            },

            cache: true,
        },

        data: data,
        escapeMarkup: function (markup) { return markup; },
        minimumInputLength: 2,
        templateResult: function (data) {
            if (data.loading) return escapeHtml(data.text);

            return '<div class="user-block"> \
                <img class="img-circle img-bordered-xs" src="' + escapeHtml(data.avatarUrl) + '?s=120" alt="User Image"> \
                <span class="username"> \
                    <a href="#">' + escapeHtml(data.name) +'</a> \
                </span> \
                <span class="description"><strong>' + escapeHtml(data.email) + '</strong>' + '</span> \
            </div>';
        },
        templateSelection: function (data) {
            return '<div> \
                <span> \
                    <img class="img-rounded img-bordered-xs" src="' + escapeHtml(data.avatarUrl) + '?s=120" style="height:28px;margin-top:-4px;" alt="User Image"> \
                </span> \
                <span style="padding-left:5px;"> \
                    ' + escapeHtml(data.name) + ' (<strong>' + escapeHtml(data.email) + '</strong>) \
                </span> \
            </div>';
        }

    });
}

$(document).ready(function() {
    @if (old('user_id'))
    $.ajax({
        url: '/admin/users.json?user_id={{ old('user_id') }}',
        dataType: 'json',
    }).then(function (data) {
        initUserIdSelect([ data ]);
    });
    @else
    initUserIdSelect();
    @endif
});
</script>
@endsection
