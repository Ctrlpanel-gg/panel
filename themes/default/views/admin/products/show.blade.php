@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="w-full mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Products') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li><a href="{{ route('admin.products.index') }}" class="hover:text-white transition-colors">{{ __('Products') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Show') }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-info">
                        <i class="fas fa-pen mr-2"></i>{{ __('Edit') }}
                    </a>
                    <form class="inline" onsubmit="return submitResult();" method="post"
                          action="{{ route('admin.products.destroy', $product->id) }}">
                        {{ csrf_field() }}
                        {{ method_field('DELETE') }}
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash mr-2"></i>{{ __('Delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="w-full mx-auto">
        <div class="glass-panel p-6 mb-8">
            <h2 class="text-xl font-medium text-white mb-4">{{ __('Product Details') }}</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="mb-4">
                        <label class="block text-sm text-zinc-400">{{ __('ID') }}</label>
                        <div class="text-white">{{ $product->id }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm text-zinc-400">{{ __('Name') }}</label>
                        <div class="text-white">{{ $product->name }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm text-zinc-400">{{ __('Price') }}</label>
                        <div class="text-white"><i class="fas fa-coins mr-1"></i>{{ $product->price }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm text-zinc-400">{{ __('Minimum') }} {{ $credits_display_name }}</label>
                        <div class="text-white">
                            <i class="fas fa-coins mr-1"></i>
                            @if ($product->minimum_credits == -1)
                                {{ $minimum_credits }}
                            @else
                                {{ $product->minimum_credits }}
                            @endif
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm text-zinc-400">{{ __('Memory') }}</label>
                        <div class="text-white">{{ $product->memory }}</div>
                    </div>
                </div>

                <div>
                    <div class="mb-4">
                        <label class="block text-sm text-zinc-400">{{ __('CPU') }}</label>
                        <div class="text-white">{{ $product->cpu }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm text-zinc-400">{{ __('Swap') }}</label>
                        <div class="text-white">{{ $product->swap }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm text-zinc-400">{{ __('Disk') }}</label>
                        <div class="text-white">{{ $product->disk }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm text-zinc-400">{{ __('IO') }}</label>
                        <div class="text-white">{{ $product->io }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm text-zinc-400">{{ __('Description') }}</label>
                        <div class="text-white">{{ $product->description }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-panel p-6">
            <h2 class="text-xl font-medium text-white mb-4">{{ __('Servers') }}</h2>
            <div class="overflow-x-auto">
                @include('admin.servers.table', ['filter' => '?product=' . $product->id])
            </div>
        </div>
    </div>
</div>

<script>
    function submitResult() {
        return confirm("{{__('Are you sure you wish to delete?')}}") !== false;
    }
</script>
@endsection
