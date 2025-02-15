@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <header class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <h1 class="text-3xl font-light text-white">{{ __('Create Server') }}</h1>
            <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-zinc-400">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li><a href="{{ route('servers.index') }}" class="hover:text-white transition-colors">{{ __('Servers') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li class="text-zinc-500">{{ __('Create') }}</li>
                </ol>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-xl mx-auto" x-data="serverApp()">
        <form action="{{ route('servers.store') }}" method="post" x-on:submit="submitClicked = true" id="serverForm">
            @csrf
            
            <!-- Configuration Card -->
            <div class="card mb-8 max-w-2xl mx-auto">
                <div class="card-header">
                    <h3 class="text-white font-medium flex items-center gap-2">
                        <i class="fas fa-cogs text-zinc-400"></i>
                        {{ __('Server Configuration') }}
                    </h3>
                </div>

                <div class="relative">
                    <!-- Warnings and Errors -->
                    @if (!$server_creation_enabled)
                        <div class="m-4 p-4 bg-amber-500/10 text-amber-400 rounded-lg border border-amber-500/20">
                            {{ __('The creation of new servers has been disabled for regular users, enable it again') }}
                            <a href="{{ route('admin.settings.index', '#Server') }}" class="text-amber-300 hover:text-amber-200">{{ __('here') }}</a>.
                        </div>
                    @endif

                    @if ($productCount === 0 || $nodeCount === 0 || count($nests) === 0 || count($eggs) === 0)
                        <div class="m-4 p-4 bg-red-500/10 text-red-400 rounded-lg border border-red-500/20">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-exclamation-circle"></i>
                                <h4 class="font-medium">{{ __('Error!') }}</h4>
                            </div>
                            @if (Auth::user()->hasRole("Admin"))
                                <p class="mb-3">
                                    {{ __('Make sure to link your products to nodes and eggs.') }} <br>
                                    {{ __('There has to be at least 1 valid product for server creation') }}
                                    <a href="{{ route('admin.overview.sync') }}" class="text-red-300 hover:text-red-200">{{ __('Sync now') }}</a>
                                </p>
                            @endif
                            <ul class="list-disc list-inside">
                                @if ($productCount === 0)
                                    <li>{{ __('No products available!') }}</li>
                                @endif
                                @if ($nodeCount === 0)
                                    <li>{{ __('No nodes have been linked!') }}</li>
                                @endif
                                @if (count($nests) === 0)
                                    <li>{{ __('No nests available!') }}</li>
                                @endif
                                @if (count($eggs) === 0)
                                    <li>{{ __('No eggs have been linked!') }}</li>
                                @endif
                            </ul>
                        </div>
                    @endif

                    <!-- Form Fields -->
                    <div class="p-6 space-y-6">
                        @if ($errors->any())
                            <div class="p-4 bg-red-500/10 text-red-400 rounded-lg border border-red-500/20">
                                <ul class="list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Server Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-zinc-400 mb-2">{{ __('Name') }}</label>
                            <input x-model="name" id="name" name="name" type="text" required
                                class="input @error('name') border-red-500/50 focus:border-red-500/50 @enderror">
                        </div>

                        <!-- Software Selection -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nest" class="block text-sm font-medium text-zinc-400 mb-2">{{ __('Software / Games') }}</label>
                                <select class="input" required name="nest" id="nest" x-model="selectedNest" @change="setEggs();">
                                    <option selected disabled hidden value="null">
                                        {{ count($nests) > 0 ? __('Please select software ...') : __('---') }}
                                    </option>
                                    @foreach ($nests as $nest)
                                        <option value="{{ $nest->id }}">{{ $nest->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="egg" class="block text-sm font-medium text-zinc-400 mb-2">{{ __('Specification') }}</label>
                                <select class="input" id="egg" required name="egg" :disabled="eggs.length == 0"
                                    x-model="selectedEgg" @change="fetchLocations();">
                                    <option x-text="getEggInputText()" selected disabled hidden value="null"></option>
                                    <template x-for="egg in eggs" :key="egg.id">
                                        <option x-text="egg.name" :value="egg.id"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- Location Selection -->
                        <div>
                            <label for="location" class="block text-sm font-medium text-zinc-400 mb-2">
                                {{ __('Location') }}
                                <i x-show="locationDescription != null" data-toggle="popover" data-trigger="click"
                                   x-bind:data-content="locationDescription"
                                   class="fas fa-info-circle ml-1 text-zinc-500"></i>
                            </label>
                            <select class="input" name="location" required id="location" 
                                    x-model="selectedLocation" :disabled="!fetchedLocations"
                                    @change="fetchProducts();">
                                <option x-text="getLocationInputText()" disabled selected hidden value="null"></option>
                                <template x-for="location in locations" :key="location.id">
                                    <option x-text="location.name" :value="location.id"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Loading Overlay -->
                    <div x-show="loading" class="absolute inset-0 bg-zinc-900/50 backdrop-blur-sm flex items-center justify-center">
                        <i class="fas fa-sync-alt fa-2x text-zinc-400 animate-spin"></i>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div x-show="selectedLocation != null" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <template x-for="product in products" :key="product.id">
                    <div class="card">
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-lg font-medium text-white" x-text="product.name"></h4>
                                <span class="text-sm text-zinc-500" 
                                      x-text="product.serverlimit > 0 
                                          ? product.servers_count + ' / ' + product.serverlimit 
                                          : '{{ __('No limit') }}'">
                                </span>
                            </div>

                            <!-- Resources -->
                            <div class="space-y-3 mb-6">
                                <div class="flex justify-between text-sm">
                                    <span class="text-zinc-400"><i class="fas fa-microchip w-5"></i> {{ __('CPU') }}</span>
                                    <span class="text-zinc-300" x-text="product.cpu + ' {{ __('vCores') }}'"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-zinc-400"><i class="fas fa-memory w-5"></i> {{ __('Memory') }}</span>
                                    <span class="text-zinc-300" x-text="product.memory + ' {{ __('MB') }}'"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-zinc-400"><i class="fas fa-hdd w-5"></i> {{ __('Disk') }}</span>
                                    <span class="text-zinc-300" x-text="product.disk + ' {{ __('MB') }}'"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-zinc-400"><i class="fas fa-save w-5"></i> {{ __('Backups') }}</span>
                                    <span class="text-zinc-300" x-text="product.backups"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-zinc-400"><i class="fas fa-database w-5"></i> {{ __('Databases') }}</span>
                                    <span class="text-zinc-300" x-text="product.databases"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-zinc-400"><i class="fas fa-network-wired w-5"></i> {{ __('Ports') }}</span>
                                    <span class="text-zinc-300" x-text="product.allocations"></span>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-6">
                                <p class="text-sm text-zinc-400" x-text="product.description"></p>
                            </div>

                            <!-- Price -->
                            <div class="p-3 bg-zinc-800/50 rounded-lg mb-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-zinc-400">{{ __('Price') }}</span>
                                    <span class="text-white font-medium" x-text="product.price + ' {{ $credits_display_name }}'"></span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <button type="button" class="btn w-full"
                                :class="(product.minimum_credits > user.credits && product.price > user.credits) || 
                                    product.doesNotFit || 
                                    (product.servers_count >= product.serverlimit && product.serverlimit != 0) || 
                                    submitClicked
                                    ? 'bg-zinc-800 text-zinc-500 cursor-not-allowed'
                                    : 'btn-primary'"
                                :disabled="(product.minimum_credits > user.credits && product.price > user.credits) || 
                                    product.doesNotFit || 
                                    product.servers_count >= product.serverlimit && product.serverlimit != 0 || 
                                    submitClicked"
                                @click="setProduct(product.id);"
                                x-text="product.doesNotFit
                                    ? '{{ __('Server cant fit on this Location') }}'
                                    : (product.servers_count >= product.serverlimit && product.serverlimit != 0
                                        ? '{{ __('Max. Servers with configuration reached') }}'
                                        : (product.minimum_credits > user.credits && product.price > user.credits
                                            ? '{{ __('Not enough') }} {{ $credits_display_name }}'
                                            : '{{ __('Create server') }}'))">
                            </button>

                            @if (env('APP_ENV') == 'local' || $store_enabled)
                                <template x-if="product.price > user.credits || product.minimum_credits > user.credits">
                                    <a href="{{ route('store.index') }}" class="block mt-2">
                                        <button type="button" class="btn w-full bg-amber-500/10 text-amber-400 hover:bg-amber-500/20">
                                            {{ __('Buy more') }} {{ $credits_display_name }}
                                        </button>
                                    </a>
                                </template>
                            @endif
                        </div>
                    </div>
                </template>
            </div>

            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="product" id="product" x-model="selectedProduct">
            <input type="hidden" name="egg_variables" id="egg_variables">
        </form>
    </div>
</div>

<!-- Keep existing JavaScript -->
<script>
    function serverApp() {
        return {
            //loading
            loading: false,
            fetchedLocations: false,
            fetchedProducts: false,

            //input fields
            name: null,
            selectedNest: null,
            selectedEgg: null,
            selectedLocation: null,
            selectedProduct: null,
            locationDescription: null,

            //selected objects based on input
            selectedNestObject: {},
            selectedEggObject: {},
            selectedLocationObject: {},
            selectedProductObject: {},

            //values
            user: {!! $user !!},
            nests: {!! $nests !!},
            eggsSave: {!! $eggs !!}, //store back-end eggs
            eggs: [],
            locations: [],
            products: [],

            submitClicked: false,


            /**
             * @description set available eggs based on the selected nest
             * @note called whenever a nest is selected
             * @see selectedNest
             */
            async setEggs() {
                this.fetchedLocations = false;
                this.fetchedProducts = false;
                this.locations = [];
                this.products = [];
                this.selectedEgg = 'null';
                this.selectedLocation = 'null';
                this.selectedProduct = null;
                this.locationDescription = 'null';

                this.eggs = this.eggsSave.filter(egg => egg.nest_id == this.selectedNest)

                //automatically select the first entry if there is only 1
                if (this.eggs.length === 1) {
                    this.selectedEgg = this.eggs[0].id;
                    await this.fetchLocations();
                    return;
                }

                this.updateSelectedObjects()
            },

            setProduct(productId) {
                if (!productId) return

                this.selectedProduct = productId;
                this.updateSelectedObjects();

                let hasEmptyRequiredVariables = this.hasEmptyRequiredVariables(this.selectedEggObject.environment);

                if(hasEmptyRequiredVariables.length > 0) {
                  this.dispatchModal(hasEmptyRequiredVariables);
                } else {
                  document.getElementById('product').value = productId;
                  document.getElementById('serverForm').submit();
                }
            },

            /**
             * @description fetch all available locations based on the selected egg
             * @note called whenever a server configuration is selected
             * @see selectedEg
             */
            async fetchLocations() {
                this.loading = true;
                this.fetchedLocations = false;
                this.fetchedProducts = false;
                this.locations = [];
                this.products = [];
                this.selectedLocation = 'null';
                this.selectedProduct = 'null';
                this.locationDescription = null;

                let response = await axios.get(`{{ route('products.locations.egg') }}/${this.selectedEgg}`)
                    .catch(console.error)

                this.fetchedLocations = true;
                this.locations = response.data

                //automatically select the first entry if there is only 1
                if (this.locations.length === 1 && this.locations[0]?.nodes?.length === 1) {
                    this.selectedLocation = this.locations[0]?.id;

                    await this.fetchProducts();
                    return;
                }

                this.loading = false;
                this.updateSelectedObjects()
            },

            /**
             * @description fetch all available products based on the selected node
             * @note called whenever a node is selected
             * @see selectedLocation
             */
            async fetchProducts() {
                this.loading = true;
                this.fetchedProducts = false;
                this.products = [];
                this.selectedProduct = null;

                let response = await axios.get(
                        `{{ route('products.products.location') }}/${this.selectedEgg}/${this.selectedLocation}`)
                    .catch(console.error)

                this.fetchedProducts = true;

                // TODO: Sortable by user chosen property (cpu, ram, disk...)
                this.products = response.data.sort((p1, p2) => parseInt(p1.price, 10) > parseInt(p2.price, 10) &&
                    1 || -1)

                //divide cpu by 100 for each product
                this.products.forEach(product => {
                    product.cpu = product.cpu / 100;
                })

                //format price to have no decimals if it is a whole number
                this.products.forEach(product => {
                    if (product.price % 1 === 0) {
                        product.price = Math.round(product.price);
                    }
                })

                this.locationDescription = this.locations.find(location => location.id == this.selectedLocation).description ?? null;
                this.loading = false;
                this.updateSelectedObjects()
            },


            /**
             * @description map selected id's to selected objects
             * @note being used in the server info box
             */
            updateSelectedObjects() {
                this.selectedNestObject = this.nests.find(nest => nest.id == this.selectedNest) ?? {}
                this.selectedEggObject = this.eggs.find(egg => egg.id == this.selectedEgg) ?? {}

                this.selectedLocationObject = {};
                this.locations.forEach(location => {
                    if (!this.selectedLocationObject?.id) {
                        this.selectedLocationObject = location.nodes.find(node => node.id == this.selectedLocation) ??
                            {};
                    }
                })

                this.selectedProductObject = this.products.find(product => product.id == this.selectedProduct) ?? {}
            },

            /**
             * @description check if all options are selected
             * @return {boolean}
             */
            isFormValid() {
                if (Object.keys(this.selectedNestObject).length === 0) return false;
                if (Object.keys(this.selectedEggObject).length === 0) return false;
                if (Object.keys(this.selectedLocationObject).length === 0) return false;
                if (Object.keys(this.selectedProductObject).length === 0) return false;
                return !!this.name;
            },

          hasEmptyRequiredVariables(environment) {
            if (!environment) return [];

            return environment.filter((variable) => {
              const hasRequiredRule = variable.rules?.includes("required");
              const isDefaultNull = !variable.default_value;

              return hasRequiredRule && isDefaultNull;
            });
          },

            getLocationInputText() {
                if (this.fetchedLocations) {
                    if (this.locations.length > 0) {
                        return '{{ __('Please select a location ...') }}';
                    }
                    return '{{ __('No location found matching current configuration') }}'
                }
                return '{{ __('---') }}';
            },

            getProductInputText() {
                if (this.fetchedProducts) {
                    if (this.products.length > 0) {
                        return '{{ __('Please select a resource ...') }}';
                    }
                    return '{{ __('No resources found matching current configuration') }}'
                }
                return '{{ __('---') }}';
            },

            getEggInputText() {
                if (this.selectedNest) {
                    return '{{ __('Please select a configuration ...') }}';
                }
                return '{{ __('---') }}';
            },

            getProductOptionText(product) {
                let text = product.name + ' (' + product.description + ')';

                if (product.minimum_credits > this.user.credits) {
                    return '{{ __('Not enough credits!') }} | ' + text;
                }

                return text;
            },

            dispatchModal(variables) {
              Swal.fire({
                title: '{{ __('Required Variables') }}',
                html: `
                  ${variables.map(variable => `
                    <div class="text-left form-group">
                      <div class="d-flex justify-content-between">
                        <label for="${variable.env_variable}">${variable.name}</label>
                        ${variable.description
                          ? `
                            <span>
                              <i data-toggle="tooltip" data-placement="top" title="${variable.description}" class="fas fa-info-circle"></i>
                            </span>
                          `
                          : ''
                        }
                      </div>
                      ${
                        variable.rules.includes("in:")
                          ? (() => {
                            const inValues = variable.rules
                              .match(/in:([^|]+)/)[1]
                              .split(',');
                            return `
                              <select name="${variable.env_variable}" id="${variable.env_variable}" required="required" class="custom-select">
                                  ${inValues.map(value => `
                                      <option value="${value}">${value}</option>
                                  `).join('')}
                              </select>
                            `;
                          })()
                          : `<input id="${variable.env_variable}" name="${variable.env_variable}" type="text" required="required" class="form-control">`
                      }
                      <div id="${variable.env_variable}-error" class="mt-1"></div>
                    </div>
                  `).join('')
                  }
                `,
                confirmButtonText: '{{ __('Submit') }}',
                showCancelButton: true,
                cancelButtonText: '{{ __('Cancel') }}',
                showLoaderOnConfirm: true,
                preConfirm: async () => {
                  const filledVariables = variables.map(variable => {
                    const value = document.getElementById(variable.env_variable).value;
                    return {
                        ...variable,
                        filled_value: value
                    };
                  });

                  const response = await fetch('{{ route("servers.validateDeploymentVariables") }}', {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                      variables: filledVariables
                    })
                  })

                  if (!response.ok) {
                    const errorData = await response.json();

                    variables.forEach(variable => {
                        const errorContainer = document.getElementById(`${variable.env_variable}-error`);
                        if (errorContainer) {
                            errorContainer.innerHTML = '';
                        }
                    });

                    if (errorData.errors) {
                        Object.entries(errorData.errors).forEach(([key, messages]) => {
                            const errorContainer = document.getElementById(`${key}-error`);
                            if (errorContainer) {
                                errorContainer.innerHTML = messages.map(message => `
                                    <small class="text-danger">${message}</small>
                                `).join('');
                            }
                        });
                    }

                    return false;
                  }

                  return response.json();
                },
                didOpen: () => {
                  $('[data-toggle="tooltip"]').tooltip();
                },
              }).then((result) => {
                if (result.isConfirmed && result.value.success) {
                  document.getElementById('egg_variables').value = JSON.stringify(result.value.variables);
                  document.getElementById('serverForm').submit();
                }
              });
            }
        }
    }
</script>
@endsection
