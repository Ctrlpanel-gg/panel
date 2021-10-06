@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Products</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.products.index')}}">Products</a></li>
                        <li class="breadcrumb-item"><a class="text-muted" href="{{route('admin.products.create')}}">Create</a>
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

            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{route('admin.products.store')}}" method="POST">
                                @csrf
                                <div class="d-flex flex-row-reverse">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" name="disabled" class="custom-control-input custom-control-input-danger" id="switch1">
                                        <label class="custom-control-label" for="switch1">Disabled <i data-toggle="popover" data-trigger="hover" data-content="Will hide this option from being selected" class="fas fa-info-circle"></i></label>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="name">Name</label>
                                            <input value="{{old('name')}}" id="name" name="name" type="text"
                                                   class="form-control @error('name') is-invalid @enderror"
                                                   required="required">
                                            @error('name')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="price">Price in credits</label>
                                            <input value="{{old('price')}}" id="price" name="price"
                                                   type="number"
                                                   class="form-control @error('price') is-invalid @enderror"
                                                   required="required">
                                            @error('price')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="memory">Memory</label>
                                            <input value="{{old('memory')}}" id="memory" name="memory"
                                                   type="number"
                                                   class="form-control @error('memory') is-invalid @enderror"
                                                   required="required">
                                            @error('memory')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="cpu">Cpu</label>
                                            <input value="{{old('cpu')}}" id="cpu" name="cpu"
                                                   type="number"
                                                   class="form-control @error('cpu') is-invalid @enderror"
                                                   required="required">
                                            @error('cpu')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="swap">Swap</label>
                                            <input value="{{old('swap')}}" id="swap" name="swap"
                                                   type="number"
                                                   class="form-control @error('swap') is-invalid @enderror"
                                                   required="required">
                                            @error('swap')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="description">Description <i data-toggle="popover" data-trigger="hover" data-content="This is what the users sees" class="fas fa-info-circle"></i></label>
                                            <textarea id="description" name="description"
                                                      type="text"
                                                      class="form-control @error('description') is-invalid @enderror"
                                                      required="required">{{old('description')}}</textarea>
                                            @error('description')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror
                                        </div>

                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <label for="disk">Disk</label>
                                            <input value="{{old('disk') ?? 1000}}" id="disk" name="disk"
                                                   type="number"
                                                   class="form-control @error('disk') is-invalid @enderror"
                                                   required="required">
                                            @error('disk')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="io">IO</label>
                                            <input value="{{old('io') ?? 500}}" id="io" name="io"
                                                   type="number"
                                                   class="form-control @error('io') is-invalid @enderror"
                                                   required="required">
                                            @error('io')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="databases">Databases</label>
                                            <input value="{{old('databases') ?? 1}}" id="databases"
                                                   name="databases"
                                                   type="number"
                                                   class="form-control @error('databases') is-invalid @enderror"
                                                   required="required">
                                            @error('databases')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="backups">Backups</label>
                                            <input value="{{old('backups') ?? 1}}" id="backups"
                                                   name="backups"
                                                   type="number"
                                                   class="form-control @error('backups') is-invalid @enderror"
                                                   required="required">
                                            @error('backups')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="allocations">Allocations</label>
                                            <input value="{{old('allocations') ?? 0}}"
                                                   id="allocations" name="allocations"
                                                   type="number"
                                                   class="form-control @error('allocations') is-invalid @enderror"
                                                   required="required">
                                            @error('allocations')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-primary">
                                        Submit
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <!-- END CONTENT -->



@endsection
