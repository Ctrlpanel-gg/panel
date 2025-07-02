@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1>{{__('Server Settings')}}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{__('Dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('servers.index') }}">{{__('Server')}}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                href="{{ route('servers.show', $server->id) }}">{{__('Settings')}}</a>
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
            <div class="pt-3 row">
                <div class="mb-4 col-xl-3 col-sm-6 mb-xl-0">
                  <div class="card">
                    <div class="p-3 card-body">
                      <div class="row">
                        <div class="col-8">
                          <div class="numbers">
                            <p class="mb-0 text-sm text-uppercase font-weight-bold">{{ __('SERVER NAME') }}</p>
                            <h5 class="font-weight-bolder" id="domain_text">
                              <span class="text-sm text-success font-weight-bolder">{{ $server->name }}</span>
                            </h5>
                          </div>
                        </div>
                        <div class="col-4 text-end">
                          <div class="text-center icon icon-shape bg-gradient-primary shadow-primary rounded-circle">
                            <i class='bx bx-fingerprint' style="color: white;"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="mb-4 col-xl-3 col-sm-6 mb-xl-0">
                  <div class="card">
                    <div class="p-3 card-body">
                      <div class="row">
                        <div class="col-8">
                          <div class="numbers">
                            <p class="mb-0 text-sm text-uppercase font-weight-bold">{{ __('CPU') }}</p>
                            <h5 class="font-weight-bolder">
                              <span class="text-sm text-success font-weight-bolder">@if($server->product->cpu == 0){{ __('Unlimited') }} @else {{$server->product->cpu}} % @endif</span>
                            </h5>
                          </div>
                        </div>
                        <div class="col-4 text-end">
                          <div class="text-center icon icon-shape bg-gradient-danger shadow-danger rounded-circle">
                            <i class='bx bxs-chip' style="color: white;"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="mb-4 col-xl-3 col-sm-6 mb-xl-0">
                  <div class="card">
                    <div class="p-3 card-body">
                      <div class="row">
                        <div class="col-8">
                          <div class="numbers">
                            <p class="mb-0 text-sm text-uppercase font-weight-bold">{{ __('MEMORY') }}</p>
                            <h5 class="font-weight-bolder">
                              <span class="text-sm text-success font-weight-bolder">@if($server->product->memory == 0){{ __('Unlimited') }} @else {{$server->product->memory}}MB @endif</span>
                            </h5>
                          </div>
                        </div>
                        <div class="col-4 text-end">
                          <div class="text-center icon icon-shape bg-gradient-success shadow-success rounded-circle">
                            <i class='bx bxs-memory-card' style="color: white;"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                  <div class="card">
                    <div class="p-3 card-body">
                      <div class="row">
                        <div class="col-8">
                          <div class="numbers">
                            <p class="mb-0 text-sm text-uppercase font-weight-bold">{{ __('STORAGE') }}</p>
                            <h5 class="font-weight-bolder">
                              <span class="text-sm text-success font-weight-bolder">@if($server->product->disk == 0){{ __('Unlimited') }} @else {{$server->product->disk}}MB @endif</span>
                            </h5>
                          </div>
                        </div>
                        <div class="col-4 text-end">
                          <div class="text-center icon icon-shape bg-gradient-warning shadow-warning rounded-circle">
                            <i class='bx bxs-hdd' style="color: white;"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="float-right card-title"><i title="Created at" class="mr-2 fas fa-calendar-alt"></i><span>{{ $server->created_at->isoFormat('LL') }}</span></h5>
                            <h5 class="card-title"><i class="mr-2 fas fa-sliders-h"></i>{{__('Server Information')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
        
                                <div class="col-sm-12 col-md-6 mb-3 mb-md-0">
                                    <div class="row">
                                        <div class="col-sm-4 col-md-5">
                                            <label>{{__('Server ID')}}</label>
                                        </div>
                                        <div class="col-sm-8 col-md-7">
                                            <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                                {{ $server->id }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6 mb-3 mb-md-0">
                                    <div class="row">
                                        <div class="col-sm-4 col-md-5">
                                            <label>{{__('Pterodactyl ID')}}</label>
                                        </div>
                                        <div class="col-sm-8 col-md-7">
                                            <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                                {{ $server->identifier }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6 mb-3 mb-md-0">
                                    <div class="row">
                                        <div class="col-sm-4 col-md-5">
                                            <label>{{__('Hourly Price')}}</label>
                                        </div>
                                        <div class="col-sm-8 col-md-7">
                                            <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                              {{ Currency::formatForDisplay($server->product->getHourlyPrice()) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6 mb-3 mb-md-0">
                                    <div class="row">
                                        <div class="col-sm-4 col-md-5">
                                            <label>{{__('Monthly Price')}}</label>
                                        </div>
                                        <div class="col-sm-8 col-md-7">
                                            <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                              {{ Currency::formatForDisplay($server->product->getMonthlyPrice()) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6 mb-3 mb-md-0">
                                    <div class="row">
                                        <div class="col-sm-4 col-md-5">
                                            <label>{{__('Location')}}</label>
                                        </div>
                                        <div class="col-sm-8 col-md-7">
                                            <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                                {{ $serverAttributes["relationships"]["location"]["attributes"]["short"] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6 mb-3 mb-md-0">
                                    <div class="row">
                                        <div class="col-sm-4 col-md-5">
                                            <label>{{__('Node')}}</label>
                                        </div>
                                        <div class="col-sm-8 col-md-7">
                                            <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                                {{ $serverAttributes["relationships"]["node"]["attributes"]["name"] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6 mb-3 mb-md-0">
                                    <div class="row">
                                        <div class="col-sm-4 col-md-5">
                                            <label>{{__('Backups')}}</label>
                                        </div>
                                        <div class="col-sm-8 col-md-7">
                                            <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                                {{ $server->product->backups }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6 mb-3 mb-md-0">
                                    <div class="row">
                                        <div class="col-sm-4 col-md-5">
                                            <label>{{__('OOM Killer')}}</label>
                                        </div>
                                        <div class="col-sm-8 col-md-7">
                                            <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                                {{ $server->product->oom_killer ? __("enabled") : __("disabled") }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-6 mb-3 mb-md-0">
                                    <div class="row">
                                        <div class="col-sm-4 col-md-5">
                                            <label>{{__('MySQL Database')}}</label>
                                        </div>
                                        <div class="col-sm-8 col-md-7">
                                            <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                                {{ $server->product->databases }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
        
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="text-center col-md-12">
                                <!-- Upgrade Button trigger modal -->
                                @if($server_enable_upgrade && Auth::user()->can("user.server.upgrade"))
                                    <button type="button" data-toggle="modal" data-target="#UpgradeModal{{ $server->id }}" target="__blank"
                                        class="btn btn-info btn-md">
                                        <i class="mr-2 fas fa-upload"></i>
                                        <span>{{ __('Upgrade / Downgrade') }}</span>
                                    </button>
                                <!-- Upgrade Modal -->
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
                                                            <option value="{{ $product->id }}" @if($product->doesNotFit)disabled @endif>{{ $product->name }} [ {{ $credits_display_name }} {{ $product->display_price }} @if($product->doesNotFit)] {{__('Server can´t fit on this node')}} @else @if($product->minimum_credits != null) /
                                                                {{__("Required")}}: {{$product->display_minimum_credits}} {{ $credits_display_name }}@endif ] @endif</option>
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
                                <!-- Delete Button trigger modal -->
                                <button type="button" data-toggle="modal" data-target="#DeleteModal" target="__blank"
                                    class="btn btn-danger btn-md">
                                    <i class="mr-2 fas fa-trash"></i>
                                    <span>{{ __('Delete') }}</span>
                                </button>
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
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><i class="mr-2 fas fa-flag"></i>{{__('Server Billing Priority')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>{{__('Current Billing Priority')}}</label>
                                </div>
                                <div class="col-lg-8">
                                    <span style="" class="d-inline-block">
                                        {{ $server->effective_billing_priority->label() }} - ({{ $server->effective_billing_priority->description() }})
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="text-center col-md-12">
                                <!-- Billing Priority Button trigger modal -->
                                <button type="button" data-toggle="modal" data-target="#BillingPriorityModal" target="__blank"
                                    class="btn btn-info btn-md">
                                    <i class="mr-2 fas fa-flag"></i>
                                    <span>{{ __('Change Billing Priority') }}</span>
                                </button>
                                <!-- Billing Priority Modal -->
                                <div class="modal fade" id="BillingPriorityModal" tabindex="-1" role="dialog" aria-labelledby="BillingPriorityModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div x-data class="modal-content">
                                            <div class="modal-header card-header">
                                                <h5 class="modal-title">{{__("Update Billing Priority")}}</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body card-body">
                                                <form action="{{ route('servers.updateBillingPriority', ['server' => $server->id]) }}" method="POST" id="billing_priority_form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select x-on:change="$el.value ? $refs.prioritySubmit.disabled = false : $refs.prioritySubmit.disabled = true" name="billing_priority" id="billing_priority" class="form-input2 form-control">
                                                        <option value="">{{__("Select the billing priority")}}</option>
                                                        @foreach(App\Enums\BillingPriority::cases() as $priority)
                                                            <option value="{{ $priority->value }}" @selected($server->effective_billing_priority == $priority)>
                                                                {{ $priority->label() }} - ({{ $priority->description() }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </form>
                                            </div>
                                            <div class="modal-footer">
                                                <button x-ref="prioritySubmit" type="submit" form="billing_priority_form" class="btn btn-primary billing_priority_submit w-100" disabled>
                                                    {{ __('Update') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



        </div>
        <!-- END CUSTOM CONTENT -->
        </div>
    </section>
    <!-- END CONTENT -->
    <script type="text/javascript">
      $(".upgrade-form").submit(function (e) {

          $(".upgrade-once").attr("disabled", true);
          return true;
      })

      $("#billing_priority_form").submit(function (e) {

          $(".billing_priority_submit").attr("disabled", true);
          return true;
      })

     </script>

@endsection

