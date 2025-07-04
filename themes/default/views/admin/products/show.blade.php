@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1>{{__('Products')}}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{__('Dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">{{__('Products')}}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                href="{{ route('admin.products.show', $product->id) }}">{{__('Show')}}</a>
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

            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title"><i class="mr-2 fas fa-sliders-h"></i>{{__('Product')}}</h5>
                    <div class="ml-auto">
                        <a data-content="Edit" data-trigger="hover" data-toggle="tooltip"
                            href="{{ route('admin.products.edit', $product->id) }}" class="mr-1 btn btn-sm btn-info"><i
                                class="fas fa-pen"></i></a>
                        <form class="d-inline" onsubmit="return submitResult();" method="post"
                            action="{{ route('admin.products.destroy', $product->id) }}">
                            {{ csrf_field() }}
                            {{ method_field('DELETE') }}
                            <button data-content="Delete" data-trigger="hover" data-toggle="tooltip"
                                class="mr-1 btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>{{__('ID')}}</label>
                                </div>
                                <div class="col-lg-8">
                                    <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                        {{ $product->id }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>{{__('Name')}}</label>
                                </div>
                                <div class="col-lg-8">
                                    <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                        {{ $product->name }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>{{__('Price')}}</label>
                                </div>
                                <div class="col-lg-8">
                                    <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                        <i class="mr-1 fas fa-coins"></i>{{ Currency::formatForDisplay($product->price) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>{{__('Minimum')}} {{ $credits_display_name }}</label>
                                </div>
                                <div class="col-lg-8">
                                    <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                        <i class="mr-1 fas fa-coins"></i>{{ !$product->minimum_credits ? Currency::formatForDisplay($minimum_credits) : $product->display_minimum_credits }}
                                    </span>
                                </div>
                            </div>
                        </div>


                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>{{__('Memory')}}</label>
                                </div>
                                <div class="col-lg-8">
                                    <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                        {{ $product->memory }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>{{__('CPU')}}</label>
                                </div>
                                <div class="col-lg-8">
                                    <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                        {{ $product->cpu }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>{{__('Swap')}}</label>
                                </div>
                                <div class="col-lg-8">
                                    <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                        {{ $product->swap }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>{{__('Disk')}}</label>
                                </div>
                                <div class="col-lg-8">
                                    <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                        {{ $product->disk }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>{{__('IO')}}</label>
                                </div>
                                <div class="col-lg-8">
                                    <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                        {{ $product->io }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>{{__('Databases')}}</label>
                                </div>
                                <div class="col-lg-8">
                                    <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                        {{ $product->databases }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>{{__('Allocations')}}</label>
                                </div>
                                <div class="col-lg-8">
                                    <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                        {{ $product->allocations }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>{{__('Created at')}}</label>
                                </div>
                                <div class="col-lg-8">
                                    <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                        {{ $product->created_at ? $product->created_at->diffForHumans() : '' }}
                                    </span>
                                </div>
                            </div>
                        </div>


                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>{{__('Description')}}</label>
                                </div>
                                <div class="col-lg-8">
                                    <span class="d-block text-truncate">
                                        {{ $product->description }}
                                    </span>
                                </div>
                            </div>
                        </div>


                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>{{__('Updated at')}}</label>
                                </div>
                                <div class="col-lg-8">
                                    <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                        {{ $product->updated_at ? $product->updated_at->diffForHumans() : '' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="mr-2 fas fa-server"></i>{{__('Servers')}}</h5>
                </div>
                <div class="card-body table-responsive">

                    @include('admin.servers.table' , ['filter' => '?product=' . $product->id])

                </div>
            </div>


        </div>
        <!-- END CUSTOM CONTENT -->
        </div>
    </section>
    <!-- END CONTENT -->



@endsection
