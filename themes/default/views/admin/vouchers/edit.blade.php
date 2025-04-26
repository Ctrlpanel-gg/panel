@extends('layouts.main')

@section('content')
    <div class="min-h-screen bg-primary-950 p-8">
        <!-- Header -->
        <div class="w-full mb-8">
            <div class="glass-panel p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-light text-white">{{__('Vouchers')}}</h1>
                        <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 text-zinc-400">
                                <li><a href="{{route('home')}}" class="hover:text-white transition-colors">{{__('Dashboard')}}</a></li>
                                <li class="text-zinc-600">/</li>
                                <li><a href="{{route('admin.vouchers.index')}}" class="hover:text-white transition-colors">{{__('Vouchers')}}</a></li>
                                <li class="text-zinc-600">/</li>
                                <li class="text-zinc-500">{{__('Edit')}}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="w-full">
            <div class="glass-panel p-6">
                <h2 class="text-xl font-medium text-white mb-6">
                    <i class="fas fa-money-check-alt mr-2"></i>{{__('Voucher details')}}
                </h2>
                
                <form action="{{route('admin.vouchers.update' , $voucher->id)}}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="memo" class="text-zinc-300 mb-2 block">
                                {{__('Memo')}} 
                                <i data-toggle="popover" data-trigger="hover"
                                   data-content="Only admins can see this"
                                   class="fas fa-info-circle text-zinc-500"></i>
                            </label>
                            <input value="{{ $voucher->memo }}" placeholder="{{__('Summer break voucher')}}"
                                   id="memo" name="memo" type="text"
                                   class="form-input bg-zinc-800/50 border-zinc-700 text-white rounded-lg w-full @error('memo') is-invalid @enderror">
                            @error('memo')
                                <div class="text-red-500 mt-1">
                                    {{$message}}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="credits" class="text-zinc-300 mb-2 block">{{ $credits_display_name }} *</label>
                            <input value="{{$voucher->credits}}" placeholder="500" id="credits" name="credits"
                                   type="number" step="any" min="0" max="99999999"
                                   class="form-input bg-zinc-800/50 border-zinc-700 text-white rounded-lg w-full @error('credits') is-invalid @enderror">
                            @error('credits')
                                <div class="text-red-500 mt-1">
                                    {{$message}}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="code" class="text-zinc-300 mb-2 block">{{__('Code')}} *</label>
                            <div class="flex">
                                <input value="{{$voucher->code}}" placeholder="SUMMER" id="code" name="code"
                                       type="text" class="form-input bg-zinc-800/50 border-zinc-700 text-white rounded-l-lg w-full @error('code') is-invalid @enderror"
                                       required="required">
                                <button class="btn btn-primary rounded-l-none rounded-r-lg" onclick="setRandomCode()" type="button">
                                    {{__('Random')}}
                                </button>
                            </div>
                            @error('code')
                                <div class="text-red-500 mt-1">
                                    {{$message}}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="uses" class="text-zinc-300 mb-2 block">
                                {{__('Uses')}} * 
                                <i data-toggle="popover" data-trigger="hover"
                                   data-content="{{__('A voucher can only be used one time per user. Uses specifies the number of different users that can use this voucher.')}}"
                                   class="fas fa-info-circle text-zinc-500"></i>
                            </label>
                            <div class="flex">
                                <input value="{{$voucher->uses}}" id="uses" min="1" max="2147483647" name="uses"
                                       type="number" class="form-input bg-zinc-800/50 border-zinc-700 text-white rounded-l-lg w-full @error('uses') is-invalid @enderror"
                                       required="required">
                                <button class="btn btn-primary rounded-l-none rounded-r-lg" onclick="setMaxUses()"
                                        type="button">{{__('Max')}}
                                </button>
                            </div>
                            @error('uses')
                                <div class="text-red-500 mt-1">
                                    {{$message}}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="expires_at" class="text-zinc-300 mb-2 block">
                                {{__('Expires at')}} 
                                <i data-toggle="popover" data-trigger="hover"
                                   data-content="Timezone: {{ Config::get('app.timezone') }}"
                                   class="fas fa-info-circle text-zinc-500"></i>
                            </label>
                            <div class="relative max-w-full">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                    <svg class="w-4 h-4 text-zinc-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
                                    </svg>
                                </div>
                                <input datepicker datepicker-format="dd-mm-yyyy" datepicker-buttons
                                       value="{{$voucher->expires_at ? $voucher->expires_at->format('d-m-Y') : ''}}"
                                       name="expires_at" 
                                       id="expires_at" 
                                       type="text" 
                                       class="bg-zinc-800/50 border border-zinc-700 text-white text-sm rounded-lg block w-full ps-10 p-2.5 focus:ring-primary-500 focus:border-primary-500"
                                       placeholder="{{__('Select date')}}">
                            </div>
                            @error('expires_at')
                                <div class="text-red-500 mt-1">
                                    {{$message}}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 text-right">
                        <button type="submit" class="btn btn-primary">
                            {{__('Submit')}}
                        </button>
                    </div>

                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            // Flowbite is initialized automatically
            // You can add custom options if needed
            const datepickerEl = document.querySelector('[datepicker]');
            
            // You can extend with additional options if needed
            if(datepickerEl && flowbite && flowbite.datepicker) {
                const options = {
                    theme: {
                        background: "bg-zinc-800",
                        text: "text-white",
                        buttons: "bg-primary-700 hover:bg-primary-600 text-white",
                        selected: "bg-primary-700 text-white",
                    },
                };
                
                // Initialize with custom options if needed
                // flowbite.datepicker.init(datepickerEl, options);
            }
        });

        function setMaxUses() {
            let element = document.getElementById('uses')
            element.value = element.max;
            console.log(element.max)
        }

        function setRandomCode() {
            let element = document.getElementById('code')
            element.value = getRandomCode(36)
        }

        function getRandomCode(length) {
            let result = '';
            let characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-';
            let charactersLength = characters.length;
            for (let i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() *
                    charactersLength));
            }
            return result;
        }
    </script>
@endsection
