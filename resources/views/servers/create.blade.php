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
        <div class="container">

            <!-- FORM -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">{{__('Server configuration')}}</div>
                        </div>

                        @if($productCount === 0 || $nodeCount === 0 || count($nests) === 0 || count($eggs) === 0 )
                            <div class="alert alert-danger p-2 m-2">
                                <h5><i class="icon fas fa-exclamation-circle"></i>Error!</h5>
                                <ul>
                                    @if($productCount === 0 )
                                        <li> {{__('No products available!')}}</li>
                                    @endif

                                    @if($nodeCount === 0 )
                                        <li>{{__('No nodes available!')}}</li>
                                    @endif

                                    @if(count($nests) === 0 )
                                        <li>{{__('No nests available!')}}</li>
                                    @endif

                                    @if(count($eggs) === 0 )
                                        <li>{{__('No eggs available!')}}</li>
                                    @endif
                                </ul>
                            </div>
                        @endif


                        <div x-show="loading" class="overlay dark">
                            <i class="fas fa-2x fa-sync-alt"></i>
                        </div>
                        <div class="card-body">
                            @csrf
                            <div class="form-group">
                                <label for="name">{{__('Name')}}</label>
                                <input x-model="name" id="name" name="name" type="text" required="required"
                                       class="form-control @error('name') is-invalid @enderror">

                                @error('name')
                                <div class="invalid-feedback">
                                    Please fill out this field.
                                </div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nest">{{__('Software')}}</label>
                                        <select class="custom-select"
                                                name="nest"
                                                id="nest"
                                                x-model="selectedNest"
                                                @change="setNests(); $refs.egg.selectedIndex = '0'">
                                            <option selected disabled
                                                    value="null">{{count($nests) > 0 ? __('Please select software..') : __('---')}}</option>
                                            @foreach ($nests as $nest)
                                                <option value="{{ $nest->id }}">{{ $nest->name }}</option>
                                            @endforeach
                                        </select>

                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="egg">{{__('Configuration')}}</label>
                                        <div>
                                            <select id="egg"
                                                    name="egg"
                                                    x-ref="egg"
                                                    :disabled="eggs.length == 0"
                                                    x-model="selectedEgg"
                                                    @change="fetchNodes(); $refs.node.selectedIndex = '0'"
                                                    required="required"
                                                    class="custom-select">
                                                <option x-text="getEggInputText()"
                                                        selected disabled hidden value="null"></option>
                                                <template x-for="egg in eggs" :key="egg.id">
                                                    <option x-text="egg.name" :value="egg.id"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="node">{{__('Node')}}</label>
                                <select name="node"
                                        id="node"
                                        x-ref="node"
                                        x-model="selectedNode"
                                        :disabled="!fetchedNodes"
                                        @change="fetchProducts();"
                                        class="custom-select">
                                    <option
                                        x-text="getNodeInputText()"
                                        disabled selected value="null"></option>
                                    <template x-for="node in nodes" :key="node.id">
                                        <option x-text="node.name" :value="node.id"></option>
                                    </template>
                                </select>
                            </div>


                            <div class="form-group">
                                <label for="product">{{__('Resources')}}</label>
                                <select name="product"
                                        id="product"
                                        x-ref="product"
                                        :disabled="!fetchedProducts"
                                        x-model="selectedProduct"
                                        class="custom-select">
                                    <option
                                        x-text="getProductInputText()"
                                        disabled selected value="null"></option>
                                    <template x-for="product in products" :key="product.id">
                                        <option x-text="product.name + ' (' + product.description + ')'"
                                                :value="product.id"></option>
                                    </template>
                                </select>
                            </div>


                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">{{__('Server details')}}</span>
                            </h4>
                            <ul class="list-group mb-3">
                                <li class="list-group-item d-flex justify-content-between lh-condensed">
                                    <div>
                                        <h6 class="my-0">{{__('Software')}}</h6>
                                        <small class="text-muted">Brief description</small>
                                    </div>
                                </li>
                                <li class="list-group-item d-flex justify-content-between lh-condensed">
                                    <div>
                                        <h6 class="my-0">{{__('Configuration')}}</h6>
                                        <small class="text-muted">Brief description</small>
                                    </div>
                                </li>
                                <li
                                    class="list-group-item d-flex justify-content-between lh-condensed">
                                    <div>
                                        <h6 class="my-0">{{__('Node')}}</h6>
                                        <small class="text-muted">Brief description</small>
                                    </div>
                                </li>
                                <li
                                    class="list-group-item d-flex justify-content-between lh-condensed">
                                    <div>
                                        <h6 class="my-0">{{__('Resources')}}</h6>
                                        <small class="text-muted">Brief description</small>
                                    </div>
                                </li>
                            </ul>
                            <ul x-show="selectedProduct" class="list-group">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>{{CREDITS_DISPLAY_NAME}} {{__('per month')}}</span>
                                    <strong x-text="selectedProduct"></strong>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END FORM -->

        </div>
    </section>
    <!-- END CONTENT -->


    <script>
        function serverApp() {
            return {
                loading: false,
                fetchedNodes: false,
                fetchedProducts: false,

                name: null,
                selectedNest: null,
                selectedEgg: null,
                selectedNode: null,
                selectedProduct: null,

                nests: {!! $nests !!},
                eggsSave:{!! $eggs !!}, //store back-end eggs
                eggs: [],
                nodes: [],
                products: [],


                /**
                 * @description set available eggs based on the selected nest
                 * @note called whenever a nest is selected
                 * @see selectedNest
                 */
                setNests() {
                    this.fetchedNodes = false;
                    this.fetchedProducts = false;
                    this.nodes = [];
                    this.products = [];

                    this.eggs = this.eggsSave.filter(egg => egg.nest_id == this.selectedNest)
                },

                /**
                 * @description fetch all available locations based on the selected egg
                 * @note called whenever a server configuration is selected
                 * @see selectedEg
                 */
                async fetchNodes() {
                    this.loading = true;
                    this.fetchedNodes = false;
                    this.fetchedProducts = false;
                    this.nodes = [];
                    this.products = [];

                    let response = await axios.get(`{{route('products.nodes.egg')}}/${this.selectedEgg}`)
                        .catch(console.error)

                    this.fetchedNodes = true;
                    this.nodes = response.data
                    this.loading = false;
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

                    let response = await axios.get(`{{route('products.products.node')}}/${this.selectedNode}`)
                        .catch(console.error)

                    this.fetchedProducts = true;
                    this.products = response.data
                    this.loading = false;
                },

                getNodeInputText() {
                    if (this.fetchedNodes) {
                        if (this.nodes.length > 0) {
                            return '{{__('Please select a node...')}}';
                        }
                        return '{{__('No nodes found matching current configuration')}}'
                    }
                    return '{{__('---')}}';
                },

                getProductInputText() {
                    if (this.fetchedProducts) {
                        if (this.products.length > 0) {
                            return '{{__('Please select a resource...')}}';
                        }
                        return '{{__('No resources found matching current configuration')}}'
                    }
                    return '{{__('---')}}';
                },

                getEggInputText() {
                    if (this.selectedNest) {
                        return '{{__('Please select a configuration...')}}';
                    }
                    return '{{__('---')}}';
                }
            }
        }
    </script>
@endsection
