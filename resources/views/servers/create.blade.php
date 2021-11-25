@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Servers</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('servers.index') }}">Servers</a>
                        <li class="breadcrumb-item"><a class="text-muted"
                                href="{{ route('servers.create') }}">Create</a>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->

    <!-- MAIN CONTENT -->
    <section x-data="serverApp()" class="content">
        <div class="container-xxl">
            <!-- FORM -->
            <form action="{{ route('servers.store') }}" method="post" class="row justify-content-center">
                @csrf
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title"><i class="fas fa-cogs mr-2"></i>{{ __('Server configuration') }}
                            </div>
                        </div>

                        @if ($productCount === 0 || $nodeCount === 0 || count($nests) === 0 || count($eggs) === 0)
                            <div class="alert alert-danger p-2 m-2">
                                <h5><i class="icon fas fa-exclamation-circle"></i>{{ __('Error!') }}</h5>
                                <p class="pl-4">
                                    @if (Auth::user()->role == 'admin')
                                        {{ __('Make sure to link your products to nodes and eggs.') }} <br>
                                        {{ __('There has to be at least 1 valid product for server creation') }}
                                    @endif
                                </p>
                                <ul>
                                    @if ($productCount === 0)
                                        <li> {{ __('No products available!') }}</li>
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


                        <div x-show="loading" class="overlay dark">
                            <i class="fas fa-2x fa-sync-alt"></i>
                        </div>

                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="list-group pl-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="name">{{ __('Name') }}</label>
                                <input x-model="name" id="name" name="name" type="text" required="required"
                                    class="form-control @error('name') is-invalid @enderror">
                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nest">{{ __('Software / Games') }}</label>
                                        <select class="custom-select" required name="nest" id="nest" x-model="selectedNest"
                                            @change="setEggs();">
                                            <option selected disabled hidden value="null">
                                                {{ count($nests) > 0 ? __('Please select software ...') : __('---') }}
                                            </option>
                                            @foreach ($nests as $nest)
                                                <option value="{{ $nest->id }}">{{ $nest->name }}</option>
                                            @endforeach
                                        </select>

                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="egg">{{ __('Specification ') }}</label>
                                        <div>
                                            <select id="egg" required name="egg" :disabled="eggs.length == 0"
                                                x-model="selectedEgg" @change="fetchLocations();" required="required"
                                                class="custom-select">
                                                <option x-text="getEggInputText()" selected disabled hidden value="null">
                                                </option>
                                                <template x-for="egg in eggs" :key="egg.id">
                                                    <option x-text="egg.name" :value="egg.id"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="node">{{ __('Node') }}</label>
                                <select name="node" required id="node" x-model="selectedNode" :disabled="!fetchedLocations"
                                    @change="fetchProducts();" class="custom-select">
                                    <option x-text="getNodeInputText()" disabled selected hidden value="null">
                                    </option>

                                    <template x-for="location in locations" :key="location.id">
                                        <optgroup :label="location.name">

                                            <template x-for="node in location.nodes" :key="node.id">
                                                <option x-text="node.name" :value="node.id">

                                                </option>
                                            </template>
                                        </optgroup>
                                    </template>

                                </select>
                            </div>


                            <!-- <div class="form-group">
                                                                                                                                                                                                                                                                                                                                                                                            <label for="product">{{ __('Resources') }}</label>
                                                                                                                                                                                                                                                                                                                                                                                            <select name="product" required id="product" :disabled="!fetchedProducts"
                                                                                                                                                                                                                                                                                                                                                                                                x-model="selectedProduct" @change="updateSelectedObjects()" class="custom-select">
                                                                                                                                                                                                                                                                                                                                                                                                <option x-text="getProductInputText()" disabled selected hidden value="null"></option>
                                                                                                                                                                                                                                                                                                                                                                                                <template x-for="product in products" :key="product.id">
                                                                                                                                                                                                                                                                                                                                                                                                    <option :disabled="product.minimum_credits > user.credits"
                                                                                                                                                                                                                                                                                                                                                                                                        x-text="getProductOptionText(product)" :value="product.id">
                                                                                                                                                                                                                                                                                                                                                                                                    </option>
                                                                                                                                                                                                                                                                                                                                                                                                </template>
                                                                                                                                                                                                                                                                                                                                                                                            </select>
                                                                                                                                                                                                                                                                                                                                                                                        </div> -->
                        </div>
                    </div>

                </div>

                <div class="w-100"></div>
                <div class="col" x-show="selectedNode != null">
                    <!-- <div class="row"><label for="product">{{ __('Resources') }}</label></div> -->
                    <div class="row justify-content-center">
                        <template x-for="product in products" :key="product.id">
                            <div class="card col-2 mr-2 ml-2 ">
                                <div class="card-body d-flex  flex-column">
                                    <h4 class="card-title" x-text="product.name"></h4>
                                    <div class="mt-2">
                                        <div>
                                            <p class="card-text text-muted mb-1">Resource Data:</p>
                                            <ul class="pl-0">
                                                <li class="d-flex justify-content-between">
                                                    <span class="d-inline-block">{{ __('CPU') }}</span>
                                                    <span class=" d-inline-block" x-text="product.cpu + ' %'"></span>
                                                </li>
                                                <div class="d-flex justify-content-between">
                                                    <span class="d-inline-block">{{ __('Memory') }}</span>
                                                    <span class=" d-inline-block"
                                                        x-text="product.memory + ' {{ __('MB') }}'"></span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="d-inline-block">{{ __('Disk') }}</span>
                                                    <span class="d-inline-block"
                                                        x-text="product.disk + ' {{ __('MB') }}'"></span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="d-inline-block">{{ __('Backups') }}</span>
                                                    <span class=" d-inline-block" x-text="product.backups"></span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="d-inline-block">{{ __('MySQL') }}
                                                        {{ __('Databases') }}</span>
                                                    <span class="d-inline-block" x-text="product.databases"></span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="d-inline-block">{{ __('Allocations') }}
                                                        ({{ __('ports') }})</span>
                                                    <span class="d-inline-block" x-text="product.allocations"></span>
                                                </div>
                                            </ul>
                                        </div>
                                        <div class="mt-2 mb-2">
                                            <span class="card-text text-muted">Description</span>
                                            <p class="card-text" x-text="product.description"></p>
                                        </div>
                                    </div>

                                    <div class="row flex mb-3 mt-auto">
                                        <div class="col-auto my-auto">
                                            Price:
                                        </div>
                                        <div class="col-auto">
                                            <div class="text-muted">per Hour</div>
                                            <span class="d-inline-block"
                                                x-text=" Math.round((product.price/30/24) * 100) / 100 + ' Credits'"></span>

                                        </div>
                                        <div class="col-auto">
                                            <div class="text-muted">per Month
                                            </div>
                                            <span class="d-inline-block" x-text="product.price + ' Credits'">
                                            </span>

                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-primary"
                                        :disabled="product.minimum_credits > user.credits">Create Server</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </form>
            <!-- END FORM -->

        </div>
    </section>
    <!-- END CONTENT -->


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
                selectedNode: null,
                selectedProduct: null,

                //selected objects based on input
                selectedNestObject: {},
                selectedEggObject: {},
                selectedNodeObject: {},
                selectedProductObject: {},

                //values
                user: {!! $user !!},
                nests: {!! $nests !!},
                eggsSave: {!! $eggs !!}, //store back-end eggs
                eggs: [],
                locations: [],
                products: [],


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
                    this.selectedNode = 'null';
                    this.selectedProduct = 'null';

                    this.eggs = this.eggsSave.filter(egg => egg.nest_id == this.selectedNest)

                    //automatically select the first entry if there is only 1
                    if (this.eggs.length === 1) {
                        this.selectedEgg = this.eggs[0].id;
                        await this.fetchLocations();
                        return;
                    }

                    this.updateSelectedObjects()
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
                    this.selectedNode = 'null';
                    this.selectedProduct = 'null';

                    let response = await axios.get(`{{ route('products.locations.egg') }}/${this.selectedEgg}`)
                        .catch(console.error)

                    this.fetchedLocations = true;
                    this.locations = response.data

                    //automatically select the first entry if there is only 1
                    if (this.locations.length === 1 && this.locations[0]?.nodes?.length === 1) {
                        this.selectedNode = this.locations[0]?.nodes[0]?.id;
                        await this.fetchProducts();
                        return;
                    }

                    this.loading = false;
                    this.updateSelectedObjects()
                },

                /**
                 * @description fetch all available products based on the selected node
                 * @note called whenever a node is selected
                 * @see selectedNode
                 */
                async fetchProducts() {
                    this.loading = true;
                    this.fetchedProducts = false;
                    this.products = [];
                    this.selectedProduct = 'null';

                    let response = await axios.get(
                            `{{ route('products.products.node') }}/${this.selectedEgg}/${this.selectedNode}`)
                        .catch(console.error)

                    this.fetchedProducts = true;
                    this.products = response.data

                    //automatically select the first entry if there is only 1
                    if (this.products.length === 1) {
                        this.selectedProduct = this.products[0].id;
                    }

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

                    this.selectedNodeObject = {};
                    this.locations.forEach(location => {
                        if (!this.selectedNodeObject?.id) {
                            this.selectedNodeObject = location.nodes.find(node => node.id == this.selectedNode) ??
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
                    if (Object.keys(this.selectedNodeObject).length === 0) return false;
                    if (Object.keys(this.selectedProductObject).length === 0) return false;
                    return !!this.name;
                },

                getNodeInputText() {
                    if (this.fetchedLocations) {
                        if (this.locations.length > 0) {
                            return '{{ __('Please select a node ...') }}';
                        }
                        return '{{ __('No nodes found matching current configuration') }}'
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
                }
            }
        }
    </script>
@endsection
