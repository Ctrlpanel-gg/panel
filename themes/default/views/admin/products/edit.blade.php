@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="w-full mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Edit Product') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li><a href="{{ route('admin.products.index') }}" class="hover:text-white transition-colors">{{ __('Products') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Edit') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-full mx-auto">
        <form action="{{ route('admin.products.update', $product->id) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div>
                    @if ($product->servers()->count() > 0)
                        <div class="glass-panel bg-red-500/5 text-red-400 mb-8">
                            <div class="flex items-center gap-3 p-6">
                                <i class="fas fa-exclamation-triangle"></i>
                                <h4 class="font-medium">
                                    {{ __('Editing the resource options will not automatically update the servers on pterodactyls side!') }}
                                </h4>
                            </div>
                        </div>
                    @endif

                    <div class="glass-panel p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-medium text-white">{{ __('Product Details') }}</h2>
                            
                            <div class="custom-control custom-switch">
                                <input type="checkbox" @if ($product->disabled) checked @endif
                                    name="disabled" class="custom-control-input custom-control-input-danger"
                                    id="switch1">
                                <label class="custom-control-label text-white" for="switch1">
                                    {{ __('Disabled') }}
                                    <i data-toggle="popover" data-trigger="hover"
                                       data-content="{{ __('Will hide this option from being selected') }}"
                                       class="fas fa-info-circle"></i>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Left column -->
                            <div>
                                <!-- Name field -->
                                <div class="form-group mb-4">
                                    <label for="name" class="block text-sm text-zinc-400 mb-1">{{ __('Name') }}</label>
                                    <input value="{{ $product->name }}" id="name" name="name"
                                        type="text" class="form-input w-full rounded-lg bg-zinc-800/50 border-zinc-700 text-white @error('name') border-red-500 @enderror"
                                        required>
                                    @error('name')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label for="price" class="block text-sm text-zinc-400 mb-1">{{ __('Price in') }} {{ $credits_display_name }}</label>
                                    <input value="{{ $product->price }}" id="price" name="price"
                                        type="number" step=".0001"
                                        class="form-input w-full rounded-lg bg-zinc-800/50 border-zinc-700 text-white @error('price') border-red-500 @enderror"
                                        required>
                                    @error('price')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label for="memory" class="block text-sm text-zinc-400 mb-1">{{ __('Memory') }}</label>
                                    <input value="{{ $product->memory }}" id="memory" name="memory"
                                        type="number" class="form-input w-full rounded-lg bg-zinc-800/50 border-zinc-700 text-white @error('memory') border-red-500 @enderror"
                                        required>
                                    @error('memory')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label for="cpu" class="block text-sm text-zinc-400 mb-1">{{ __('Cpu') }}</label>
                                    <input value="{{ $product->cpu }}" id="cpu" name="cpu" type="number"
                                        class="form-input w-full rounded-lg bg-zinc-800/50 border-zinc-700 text-white @error('cpu') border-red-500 @enderror"
                                        required>
                                    @error('cpu')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label for="swap" class="block text-sm text-zinc-400 mb-1">{{ __('Swap') }}</label>
                                    <input value="{{ $product->swap }}" id="swap" name="swap"
                                        type="number" class="form-input w-full rounded-lg bg-zinc-800/50 border-zinc-700 text-white @error('swap') border-red-500 @enderror"
                                        required>
                                    @error('swap')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label for="allocations" class="block text-sm text-zinc-400 mb-1">{{ __('Allocations') }}</label>
                                    <input value="{{ $product->allocations }}" id="allocations" name="allocations"
                                        type="number"
                                        class="form-input w-full rounded-lg bg-zinc-800/50 border-zinc-700 text-white @error('allocations') border-red-500 @enderror"
                                        required>
                                    @error('allocations')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label for="description" class="block text-sm text-zinc-400 mb-1">{{ __('Description') }} <i data-toggle="popover"
                                            data-trigger="hover"
                                            data-content="{{ __('This is what the users sees') }}"
                                            class="fas fa-info-circle"></i></label>
                                    <textarea id="description" name="description" type="text"
                                        class="form-input w-full rounded-lg bg-zinc-800/50 border-zinc-700 text-white @error('description') border-red-500 @enderror"
                                        required>{{ $product->description }}</textarea>
                                    @error('description')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <input type="checkbox" @if ($product->oom_killer) checked @endif
                                        value="1" id="oom" name="oom_killer" class="">

                                    <label for="oom_killer" class="block text-sm text-zinc-400 mb-1">{{ __('OOM Killer') }} <i data-toggle="popover"
                                            data-trigger="hover"
                                            data-content="{{ __('Enable or Disable the OOM Killer for this Product.') }}"
                                            class="fas fa-info-circle"></i></label>
                                </div>
                            </div>

                            <!-- Right column -->
                            <div>
                                <!-- Disk field -->
                                <div class="form-group mb-4">
                                    <label for="disk" class="block text-sm text-zinc-400 mb-1">{{ __('Disk') }}</label>
                                    <input value="{{ $product->disk }}" id="disk" name="disk"
                                        type="number" class="form-input w-full rounded-lg bg-zinc-800/50 border-zinc-700 text-white @error('disk') border-red-500 @enderror"
                                        required>
                                    @error('disk')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label for="billing_period" class="block text-sm text-zinc-400 mb-1">{{ __('Billing Period') }} <i
                                            data-toggle="popover" data-trigger="hover"
                                            data-content="{{ __('Period when the user will be charged for the given price') }}"
                                            class="fas fa-info-circle"></i></label>

                                    <select id="billing_period" style="width:100%" class="form-select w-full rounded-lg bg-zinc-800/50 border-zinc-700 text-white @error('billing_period') border-red-500 @enderror"
                                        name="billing_period" required autocomplete="off">
                                        <option value="hourly" @if ($product->billing_period == 'hourly') selected @endif>
                                            {{ __('Hourly') }}
                                        </option>
                                        <option value="daily" @if ($product->billing_period == 'daily') selected @endif>
                                            {{ __('Daily') }}
                                        </option>
                                        <option value="weekly" @if ($product->billing_period == 'weekly') selected @endif>
                                            {{ __('Weekly') }}
                                        </option>
                                        <option value="monthly" @if ($product->billing_period == 'monthly') selected @endif>
                                            {{ __('Monthly') }}
                                        </option>
                                        <option value="quarterly"
                                            @if ($product->billing_period == 'quarterly') selected @endif>
                                            {{ __('Quarterly') }}
                                        </option>
                                        <option value="half-annually"
                                            @if ($product->billing_period == 'half-annually') selected @endif>
                                            {{ __('Half Annually') }}
                                        </option>
                                        <option value="annually"
                                            @if ($product->billing_period == 'annually') selected @endif>
                                            {{ __('Annually') }}
                                        </option>
                                    </select>
                                    @error('billing_period')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label for="minimum_credits" class="block text-sm text-zinc-400 mb-1">{{ __('Minimum') }} {{ $credits_display_name }}
                                        <i data-toggle="popover" data-trigger="hover"
                                            data-content="{{ __('Setting to -1 will use the value from configuration.') }}"
                                            class="fas fa-info-circle"></i></label>
                                    <input value="{{ $product->minimum_credits }}" id="minimum_credits"
                                        name="minimum_credits" type="number"
                                        class="form-input w-full rounded-lg bg-zinc-800/50 border-zinc-700 text-white @error('minimum_credits') border-red-500 @enderror"
                                        required>
                                    @error('minimum_credits')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label for="io" class="block text-sm text-zinc-400 mb-1">{{ __('IO') }}</label>
                                    <input value="{{ $product->io }}" id="io" name="io"
                                        type="number" class="form-input w-full rounded-lg bg-zinc-800/50 border-zinc-700 text-white @error('io') border-red-500 @enderror"
                                        required>
                                    @error('io')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label for="databases" class="block text-sm text-zinc-400 mb-1">{{ __('Databases') }}</label>
                                    <input value="{{ $product->databases }}" id="databases" name="databases"
                                        type="number"
                                        class="form-input w-full rounded-lg bg-zinc-800/50 border-zinc-700 text-white @error('databases') border-red-500 @enderror"
                                        required>
                                    @error('databases')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label for="backups" class="block text-sm text-zinc-400 mb-1">{{ __('Backups') }}</label>
                                    <input value="{{ $product->backups }}" id="backups" name="backups"
                                        type="number"
                                        class="form-input w-full rounded-lg bg-zinc-800/50 border-zinc-700 text-white @error('backups') border-red-500 @enderror"
                                        required>
                                    @error('backups')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label for="serverlimit" class="block text-sm text-zinc-400 mb-1">{{ __('Serverlimit') }}</label>
                                    <i data-toggle="popover" data-trigger="hover"
                                        data-content="{{ __('The maximum amount of Servers that can be created with this Product per User. 0 = unlimited') }}"
                                        class="fas fa-info-circle"></i>
                                    <input value="{{ $product->serverlimit ??  0 }}"
                                        id="serverlimit" name="serverlimit" type="number"
                                        class="form-input w-full rounded-lg bg-zinc-800/50 border-zinc-700 text-white @error('serverlimit') border-red-500 @enderror"
                                        required>
                                    @error('serverlimit')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 text-right">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Submit') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="glass-panel p-6">
                        <h2 class="text-xl font-medium text-white mb-6">
                            {{ __('Product Linking') }}
                            <i data-toggle="popover" data-trigger="hover"
                              data-content="{{ __('Link your products to nodes and eggs to create dynamic pricing for each option') }}"
                              class="fas fa-info-circle"></i>
                        </h2>

                        <!-- Nodes selection -->
                        <div class="form-group mb-6">
                            <label for="nodes" class="block text-sm text-zinc-400 mb-1">{{ __('Nodes') }}</label>
                            <select id="nodes" class="form-select w-full rounded-lg bg-zinc-800/50 border-zinc-700 text-white @error('nodes') border-red-500 @enderror"
                                    name="nodes[]" multiple="multiple" autocomplete="off">
                                @foreach ($locations as $location)
                                    <optgroup label="{{ $location->name }}">
                                        @foreach ($location->nodes as $node)
                                            <option @if ($product->nodes->contains('id', $node->id)) selected @endif
                                                value="{{ $node->id }}">{{ $node->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('nodes')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                            <div class="text-zinc-400 text-sm mt-1">
                                {{ __('This product will only be available for these nodes') }}
                            </div>
                        </div>

                        <!-- Eggs selection -->
                        <div class="form-group">
                            <div class="flex justify-between items-center mb-1">
                                <label for="eggs" class="text-sm text-zinc-400">{{ __('Eggs') }}</label>
                                <div>
                                    <button type="button" id="select-all-eggs" class="btn btn-sm btn-secondary">{{ __('Select All') }}</button>
                                    <button type="button" id="deselect-all-eggs" class="btn btn-sm btn-secondary ml-2">{{ __('Deselect All') }}</button>
                                </div>
                            </div>
                            <select id="eggs" class="form-select w-full rounded-lg bg-zinc-800/50 border-zinc-700 text-white @error('eggs') border-red-500 @enderror"
                                    name="eggs[]" multiple="multiple" autocomplete="off">
                                @foreach ($nests as $nest)
                                    <optgroup label="{{ $nest->name }}">
                                        @foreach ($nest->eggs as $egg)
                                            <option @if ($product->eggs->contains('id', $egg->id)) selected @endif
                                                value="{{ $egg->id }}">{{ $egg->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('eggs')
                                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                            @enderror
                            <div class="text-zinc-400 text-sm mt-1">
                                {{ __('This product will only be available for these eggs') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        // Initialize Select2 with improved settings
        $('.form-select').select2({
            dropdownParent: $('body'),
            width: '100%',
            minimumResultsForSearch: 10,
            templateResult: formatOption,
            templateSelection: formatSelection,
            escapeMarkup: function(m) { return m; },
            theme: 'default'
        });

        // Format option in dropdown
        function formatOption(option) {
            if (!option.id) return option.text;
            return '<span class="select2-option">' + option.text + '</span>';
        }

        // Format selected option
        function formatSelection(option) {
            if (!option.id) return option.text;
            return '<span class="select2-selection-text">' + option.text + '</span>';
        }

        // Select/Deselect all eggs
        document.getElementById('select-all-eggs').addEventListener('click', function() {
            $('#eggs option').prop('selected', true);
            $('#eggs').trigger('change');
        });

        document.getElementById('deselect-all-eggs').addEventListener('click', function() {
            $('#eggs option').prop('selected', false);
            $('#eggs').trigger('change');
        });

        // Initialize popovers
        $('[data-toggle="popover"]').popover();
    });
</script>
@endsection
