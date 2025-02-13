@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <header class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <h1 class="text-3xl font-light text-white">{{ __('Server Settings') }}</h1>
            <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
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

    <!-- Stats Grid -->
    <div class="max-w-screen-xl mx-auto mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Server Name -->
            <div class="stats-card glass-morphism">
                <div class="stats-icon blue">
                    <i class="fas fa-server text-xl"></i>
                </div>
                <div>
                    <div class="stats-text-label">{{ __('Server Name') }}</div>
                    <div class="stats-text-value">{{ $server->name }}</div>
                </div>
            </div>

            <!-- CPU -->
            <div class="stats-card glass-morphism">
                <div class="stats-icon emerald">
                    <i class="fas fa-microchip text-xl"></i>
                </div>
                <div>
                    <div class="stats-text-label">{{ __('CPU') }}</div>
                    <div class="stats-text-value">
                        @if($server->product->cpu == 0)
                            {{ __('Unlimited') }}
                        @else
                            {{ $server->product->cpu / 100 }} {{ __('vCores') }}
                        @endif
                    </div>
                </div>
            </div>

            <!-- Memory -->
            <div class="stats-card glass-morphism">
                <div class="stats-icon amber">
                    <i class="fas fa-memory text-xl"></i>
                </div>
                <div>
                    <div class="stats-text-label">{{ __('Memory') }}</div>
                    <div class="stats-text-value">
                        @if($server->product->memory == 0)
                            {{ __('Unlimited') }}
                        @else
                            {{ $server->product->memory }}
                            <span class="stats-text-subtitle">MB</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Storage -->
            <div class="stats-card glass-morphism">
                <div class="stats-icon red">
                    <i class="fas fa-hdd text-xl"></i>
                </div>
                <div>
                    <div class="stats-text-label">{{ __('Storage') }}</div>
                    <div class="stats-text-value">
                        @if($server->product->disk == 0)
                            {{ __('Unlimited') }}
                        @else
                            {{ $server->product->disk }}
                            <span class="stats-text-subtitle">MB</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Server Information -->
    <div class="max-w-screen-xl mx-auto">
        <div class="card">
            <div class="card-header flex justify-between items-center">
                <h3 class="text-white font-medium flex items-center gap-2">
                    <i class="fas fa-info-circle text-zinc-400"></i>
                    {{ __('Server Information') }}
                </h3>
                <span class="text-zinc-500 text-sm">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    {{ $server->created_at->isoFormat('LL') }}
                </span>
            </div>

            <!-- Grid Layout -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Server ID -->
                    <div class="flex flex-col space-y-1">
                        <label class="text-sm font-medium text-zinc-400">{{__('Server ID')}}</label>
                        <span class="text-zinc-200">{{ $server->id }}</span>
                    </div>

                    <!-- Pterodactyl ID -->
                    <div class="flex flex-col space-y-1">
                        <label class="text-sm font-medium text-zinc-400">{{__('Pterodactyl ID')}}</label>
                        <span class="text-zinc-200">{{ $server->identifier }}</span>
                    </div>

                    <!-- Hourly Price -->
                    <div class="flex flex-col space-y-1">
                        <label class="text-sm font-medium text-zinc-400">{{__('Hourly Price')}}</label>
                        <span class="text-zinc-200">{{ number_format($server->product->getHourlyPrice(), 2, '.', '') }}</span>
                    </div>

                    <!-- Monthly Price -->
                    <div class="flex flex-col space-y-1">
                        <label class="text-sm font-medium text-zinc-400">{{__('Monthly Price')}}</label>
                        <span class="text-zinc-200">{{ $server->product->getHourlyPrice() * 24 * 30 }}</span>
                    </div>

                    <!-- Location -->
                    <div class="flex flex-col space-y-1">
                        <label class="text-sm font-medium text-zinc-400">{{__('Location')}}</label>
                        <span class="text-zinc-200">{{ $serverAttributes["relationships"]["location"]["attributes"]["short"] }}</span>
                    </div>

                    <!-- Node -->
                    <div class="flex flex-col space-y-1">
                        <label class="text-sm font-medium text-zinc-400">{{__('Node')}}</label>
                        <span class="text-zinc-200">{{ $serverAttributes["relationships"]["node"]["attributes"]["name"] }}</span>
                    </div>

                    <!-- Backups -->
                    <div class="flex flex-col space-y-1">
                        <label class="text-sm font-medium text-zinc-400">{{__('Backups')}}</label>
                        <span class="text-zinc-200">{{ $server->product->backups }}</span>
                    </div>

                    <!-- OOM Killer -->
                    <div class="flex flex-col space-y-1">
                        <label class="text-sm font-medium text-zinc-400">{{__('OOM Killer')}}</label>
                        <span class="text-zinc-200">{{ $server->product->oom_killer ? __("enabled") : __("disabled") }}</span>
                    </div>

                    <!-- MySQL Database -->
                    <div class="flex flex-col space-y-1">
                        <label class="text-sm font-medium text-zinc-400">{{__('MySQL Database')}}</label>
                        <span class="text-zinc-200">{{ $server->product->databases }}</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col gap-4 mt-8 pt-6 border-t border-zinc-800/50">
                    @if($server_enable_upgrade && Auth::user()->can("user.server.upgrade"))
                        <button type="button" data-toggle="modal" data-target="#UpgradeModal{{ $server->id }}"
                            class="btn btn-primary w-full">
                            <i class="fas fa-upload mr-2"></i>
                            {{ __('Upgrade / Downgrade') }}
                        </button>
                    @endif

                    <button type="button" data-toggle="modal" data-target="#DeleteModal"
                        class="btn bg-red-500/10 text-red-400 hover:bg-red-500/20 w-full">
                        <i class="fas fa-trash mr-2"></i>
                        {{ __('Delete Server') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Upgrade Modal -->
    @if($server_enable_upgrade && Auth::user()->can("user.server.upgrade"))
        <div style="width: 100%; margin-block-start: 100px;" class="modal fade" id="UpgradeModal{{ $server->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div x-data class="modal-content">
                    <div class="modal-header card-header">
                        <h5 class="modal-title">{{__("Upgrade/Downgrade Server")}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body card-body">
                        <strong>{{__("Current Product")}}: </strong> {{ $server->product->name }}
                        <br>
                        <br>

                    <form action="{{ route('servers.upgrade', ['server' => $server->id]) }}" method="POST" class="upgrade-form">
                      @csrf
                          <select x-on:change="$el.value ? $refs.upgradeSubmit.disabled = false : $refs.upgradeSubmit.disabled = true" name="product_upgrade" id="product_upgrade" class="form-input2 form-control">
                            <option value="">{{__("Select the product")}}</option>
                              @foreach($products as $product)
                                  @if($product->id != $server->product->id && $product->disabled == false)
                                    <option value="{{ $product->id }}" @if($product->doesNotFit)disabled @endif>{{ $product->name }} [ {{ $credits_display_name }} {{ $product->price }} @if($product->doesNotFit)] {{__('Server can´t fit on this node')}} @else @if($product->minimum_credits!=-1) /
                                        {{__("Required")}}: {{$product->minimum_credits}} {{ $credits_display_name }}@endif ] @endif</option>
                                  @endif
                              @endforeach
                          </select>

                          <br> <strong>{{__("Caution") }}:</strong> {{__("Upgrading/Downgrading your server will reset your billing cycle to now. Your overpayed Credits will be refunded. The price for the new billing cycle will be withdrawed")}}. <br>
                          <br> {{__("Server will be automatically restarted once upgraded")}}
                      </div>
                      <div class="modal-footer card-body">
                          <button x-ref="upgradeSubmit" type="submit" class="btn btn-primary upgrade-once" style="width: 100%" disabled><strong>{{__("Change Product")}}</strong></button>
                      </div>
                      <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Modal -->
    <div class="modal fade" id="DeleteModal" tabindex="-1" role="dialog" aria-labelledby="DeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="DeleteModalLabel">{{__("Delete Server")}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{__("This is an irreversible action, all files of this server will be removed!")}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <form class="d-inline" method="post" action="{{ route('servers.destroy', ['server' => $server->id]) }}">
                        @csrf
                        @method('DELETE')
                        <button data-toggle="popover" data-trigger="hover" data-placement="top" class="mr-1 btn btn-danger">{{__("Delete")}}</button>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

