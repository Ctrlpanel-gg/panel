@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-4 sm:p-8">
    <!-- Header -->
    <header class="w-full mb-6 sm:mb-8">
        <div class="glass-panel p-4 sm:p-6">
            <h1 class="text-2xl sm:text-3xl font-light text-white font-oxanium">{{ __('Server Settings') }}</h1>
            <nav class="flex mt-2 text-xs sm:text-sm" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-zinc-400">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li><a href="{{ route('servers.index') }}" class="hover:text-white transition-colors">{{ __('Servers') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li class="text-zinc-500">{{ __('Settings') }}</li>
                </ol>
            </nav>
        </div>
    </header>

    <!-- Main Content Grid -->
    <div class="w-full grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
        <!-- Left Column - Stats -->
        <div class="lg:col-span-1">
            <div class="grid grid-cols-1 gap-2">
                <!-- Server Name -->
                <div class="group relative flex items-center gap-3 rounded-lg bg-zinc-900/40 p-2 transition-all hover:bg-zinc-800/40 hover:shadow-lg">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-blue-500/10 text-blue-400 transition-colors group-hover:bg-blue-500/20">
                        <i class="fas fa-server text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs text-zinc-400 font-medium">{{ __('Server Name') }}</div>
                        <div class="text-sm text-white truncate">{{ $server->name }}</div>
                    </div>
                </div>

                <!-- CPU -->
                <div class="group relative flex items-center gap-3 rounded-lg bg-zinc-900/40 p-2 transition-all hover:bg-zinc-800/40 hover:shadow-lg">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-emerald-500/10 text-emerald-400 transition-colors group-hover:bg-emerald-500/20">
                        <i class="fas fa-microchip text-sm"></i>
                    </div>
                    <div>
                        <div class="text-xs text-zinc-400 font-medium">{{ __('CPU') }}</div>
                        <div class="text-sm text-white">
                            @if($server->product->cpu == 0)
                                {{ __('Unlimited') }}
                            @else
                                {{ $server->product->cpu / 100 }} {{ __('vCores') }}
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Memory -->
                <div class="group relative flex items-center gap-3 rounded-lg bg-zinc-900/40 p-2 transition-all hover:bg-zinc-800/40 hover:shadow-lg">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-amber-500/10 text-amber-400 transition-colors group-hover:bg-amber-500/20">
                        <i class="fas fa-memory text-sm"></i>
                    </div>
                    <div>
                        <div class="text-xs text-zinc-400 font-medium">{{ __('Memory') }}</div>
                        <div class="text-sm text-white">
                            @if($server->product->memory == 0)
                                {{ __('Unlimited') }}
                            @else
                                {{ $server->product->memory }}
                                <span class="text-xs text-zinc-400">MB</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Storage -->
                <div class="group relative flex items-center gap-3 rounded-lg bg-zinc-900/40 p-2 transition-all hover:bg-zinc-800/40 hover:shadow-lg">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-red-500/10 text-red-400 transition-colors group-hover:bg-red-500/20">
                        <i class="fas fa-hdd text-sm"></i>
                    </div>
                    <div>
                        <div class="text-xs text-zinc-400 font-medium">{{ __('Storage') }}</div>
                        <div class="text-sm text-white">
                            @if($server->product->disk == 0)
                                {{ __('Unlimited') }}
                            @else
                                {{ $server->product->disk }}
                                <span class="text-xs text-zinc-400">MB</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Server Information -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="p-4 border-b border-zinc-800/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <h3 class="text-white font-medium flex items-center gap-2 text-sm font-oxanium">
                        <i class="fas fa-info-circle text-zinc-400"></i>
                        {{ __('Server Information') }}
                    </h3>
                    <span class="text-zinc-500 text-xs">
                        <i class="fas fa-calendar-alt mr-1"></i>
                        {{ $server->created_at->isoFormat('LL') }}
                    </span>
                </div>

                <div class="p-4">
                    <div class="flex flex-col lg:flex-row gap-4">
                        <!-- Left Column - Compact Server Info -->
                        <div class="w-full lg:w-1/3 space-y-2">
                            <div class="bg-zinc-800/20 p-2 rounded text-xs">
                                <span class="text-zinc-400">{{__('Server ID')}}:</span>
                                <span class="text-white ml-2">{{ $server->id }}</span>
                            </div>
                            <div class="bg-zinc-800/20 p-2 rounded text-xs">
                                <span class="text-zinc-400">{{__('Pterodactyl ID')}}:</span>
                                <span class="text-white ml-2">{{ $server->identifier }}</span>
                            </div>
                            <div class="bg-zinc-800/20 p-2 rounded text-xs">
                                <span class="text-zinc-400">{{__('Location')}}:</span>
                                <span class="text-white ml-2">{{ $serverAttributes["relationships"]["location"]["attributes"]["short"] }}</span>
                            </div>
                            <div class="bg-zinc-800/20 p-2 rounded text-xs">
                                <span class="text-zinc-400">{{__('Node')}}:</span>
                                <span class="text-white ml-2">{{ $serverAttributes["relationships"]["node"]["attributes"]["name"] }}</span>
                            </div>
                        </div>

                        <!-- Right Column - Compact Pricing & Limits -->
                        <div class="w-full lg:w-2/3 flex flex-col">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                                <!-- Pricing -->
                                <div class="bg-zinc-800/20 p-3 rounded">
                                    <h4 class="text-zinc-300 text-sm mb-2 font-oxanium">{{__('Pricing')}}</h4>
                                    <div class="space-y-1 text-xs">
                                        <div class="flex justify-between">
                                            <span class="text-zinc-400">{{__('Hourly')}}:</span>
                                            <span class="text-white">{{ number_format($server->product->getHourlyPrice(), 2, '.', '') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-zinc-400">{{__('Monthly')}}:</span>
                                            <span class="text-white">{{ $server->product->getHourlyPrice() * 24 * 30 }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Limits -->
                                <div class="bg-zinc-800/20 p-3 rounded">
                                    <h4 class="text-zinc-300 text-sm mb-2 font-oxanium">{{__('Limits')}}</h4>
                                    <div class="space-y-1 text-xs">
                                        <div class="flex justify-between">
                                            <span class="text-zinc-400">{{__('Backups')}}:</span>
                                            <span class="text-white">{{ $server->product->backups }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-zinc-400">{{__('Databases')}}:</span>
                                            <span class="text-white">{{ $server->product->databases }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-zinc-400">{{__('OOM Killer')}}:</span>
                                            <span class="text-white">{{ $server->product->oom_killer ? __("enabled") : __("disabled") }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Compact Action Buttons -->
                            <div class="flex gap-2 mt-auto">
                                <button type="button" data-toggle="modal" data-target="#UpgradeModal{{ $server->id }}"
                                    class="btn btn-primary flex-1 py-2 text-sm font-oxanium">
                                    <i class="fas fa-upload mr-1"></i>
                                    {{ __('Upgrade') }}
                                </button>
                                <button type="button" data-toggle="modal" data-target="#DeleteModal"
                                    class="btn bg-red-500/10 text-red-400 hover:bg-red-500/20 flex-1 py-2 text-sm font-oxanium">
                                    <i class="fas fa-trash mr-1"></i>
                                    {{ __('Delete') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Upgrade Modal -->
    <div class="modal fade" id="UpgradeModal{{ $server->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div x-data class="modal-content bg-primary-950/95 backdrop-blur-sm text-white w-full max-w-lg mx-auto border border-zinc-800/50 rounded-xl shadow-2xl">
                <div class="relative p-5 border-b border-zinc-800/50">
                    <h5 class="modal-title text-lg font-medium tracking-tight">{{__("Upgrade/Downgrade Server")}}</h5>
                    <button type="button" class="close absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full text-zinc-400 hover:text-white hover:bg-zinc-800/50 transition-colors" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true" class="text-lg">&times;</span>
                    </button>
                </div>
                <div class="p-5">
                    <div class="mb-5 bg-zinc-800/30 rounded-lg p-4 border border-zinc-700/30 backdrop-blur-sm">
                        <div class="text-sm text-zinc-400 mb-1.5 font-medium">{{__("Current Product")}}</div>
                        <div class="text-white font-semibold tracking-tight">{{ $server->product->name }}</div>
                    </div>

                    <form action="{{ route('servers.upgrade', ['server' => $server->id]) }}" method="POST" class="upgrade-form space-y-5">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-zinc-300 mb-2">{{__("Select New Product")}}</label>
                            <select x-on:change="$el.value ? $refs.upgradeSubmit.disabled = false : $refs.upgradeSubmit.disabled = true" 
                                name="product_upgrade" 
                                id="product_upgrade" 
                                class="w-full rounded-lg bg-zinc-800/30 border border-zinc-700/30 p-3 text-white transition-all focus:border-blue-500/50 focus:ring-1 focus:ring-blue-500/20 hover:bg-zinc-800/50 select2">
                                <option value="" class="bg-zinc-900">{{__("Select the product")}}</option>
                                @foreach($products as $product)
                                    @if($product->id != $server->product->id && $product->disabled == false)
                                        <option value="{{ $product->id }}" @if($product->doesNotFit)disabled @endif>{{ $product->name }} [ {{ $credits_display_name }} {{ $product->price }} @if($product->doesNotFit)] {{__('Server can´t fit on this node')}} @else @if($product->minimum_credits!=-1) /
                                            {{__("Required")}}: {{$product->minimum_credits}} {{ $credits_display_name }}@endif ] @endif</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="bg-gradient-to-r from-amber-500/5 to-amber-500/10 border border-amber-500/20 text-amber-400 p-4 rounded-lg mb-5">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-amber-500/10 flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle text-sm"></i>
                                </div>
                                <span class="font-semibold tracking-tight">{{__("Caution")}}</span>
                            </div>
                            <p class="text-sm leading-relaxed text-amber-300/90">{{__("Upgrading/Downgrading your server will reset your billing cycle to now. Your overpayed Credits will be refunded. The price for the new billing cycle will be withdrawed")}}.</p>
                        </div>
                        
                        <div class="bg-gradient-to-r from-blue-500/5 to-blue-500/10 border border-blue-500/20 text-blue-400 p-4 rounded-lg mb-5">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-500/10 flex items-center justify-center">
                                    <i class="fas fa-info-circle text-sm"></i>
                                </div>
                                <span class="text-sm leading-relaxed text-blue-300/90">{{__("Server will be automatically restarted once upgraded")}}</span>
                            </div>
                        </div>
                
                        <button x-ref="upgradeSubmit" type="submit" class="btn bg-gradient-to-r from-blue-500 to-blue-600 text-white w-full py-3 px-4 rounded-lg font-medium tracking-tight hover:from-blue-600 hover:to-blue-700 transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-blue-500/20" disabled>
                            <i class="fas fa-upload mr-2"></i>
                            {{__("Change Product")}}
                        </button>
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('.select2').select2({
                theme: 'default'
            });
        });
    </script>

    <!-- Delete Modal -->
    <div class="modal fade" id="DeleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-primary-950/95 backdrop-blur-sm text-white w-full max-w-lg mx-auto border border-zinc-800/50 rounded-xl shadow-2xl">
                <div class="relative p-5 border-b border-zinc-800/50">
                    <h5 class="modal-title text-lg font-medium tracking-tight" id="DeleteModalLabel">{{__("Delete Server")}}</h5>
                    <button type="button" class="close absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full text-zinc-400 hover:text-white hover:bg-zinc-800/50 transition-colors" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true" class="text-lg">&times;</span>
                    </button>
                </div>
                <div class="p-5">
                    <div class="bg-gradient-to-r from-red-500/5 to-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg mb-5">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-red-500/10 flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-sm"></i>
                            </div>
                            <span class="text-sm leading-relaxed text-red-300/90">{{__("This is an irreversible action, all files of this server will be removed!")}}</span>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-3 justify-end">
                        <button type="button" class="btn bg-zinc-800/30 hover:bg-zinc-800/50 text-zinc-400 py-2.5 px-5 rounded-lg font-medium tracking-tight transition-colors" data-dismiss="modal">
                            {{ __('Cancel') }}
                        </button>
                        <form class="d-inline" method="post" action="{{ route('servers.destroy', ['server' => $server->id]) }}">
                            @csrf
                            @method('DELETE')
                            <button data-toggle="popover" data-trigger="hover" data-placement="top" class="btn bg-gradient-to-r from-red-500 to-red-600 text-white py-2.5 px-5 rounded-lg font-medium tracking-tight hover:from-red-600 hover:to-red-700 transition-all duration-150 shadow-lg shadow-red-500/20">
                                <i class="fas fa-trash mr-2"></i>{{__("Delete Server")}}
                            </button>
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Core utilities */
    @layer utilities {
        .glass {
            @apply bg-zinc-900/40 backdrop-blur-[2px];
        }
        
        .card {
            @apply bg-zinc-900/40 rounded-lg border border-zinc-800/40 transition-shadow hover:shadow-lg;
        }
        
        .input-field {
            @apply bg-zinc-900/40 border border-zinc-800/40 rounded-lg p-2.5 text-white transition-colors focus:border-blue-500/50 focus:ring-0;
        }
    }

    /* Minimal animations */
    .fade-in {
        @apply transition-opacity duration-150;
    }

    @keyframes enter {
        from { opacity: 0; transform: translateY(2px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animate-in {
        animation: enter 150ms ease-out forwards;
    }
</style>
@endsection
