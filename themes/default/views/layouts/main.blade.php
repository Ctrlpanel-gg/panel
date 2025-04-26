<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @php($website_settings = app(App\Settings\WebsiteSettings::class))
    @php($general_settings = app(App\Settings\GeneralSettings::class))
    @php($discord_settings = app(App\Settings\DiscordSettings::class))
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="{{ $website_settings->seo_title }}" property="og:title">
    <meta content="{{ $website_settings->seo_description }}" property="og:description">
    <meta
        content='{{ \Illuminate\Support\Facades\Storage::disk('public')->exists('logo.png') ? asset('storage/logo.png') : asset('images/ctrlpanel_logo.png') }}'
        property="og:image">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon"
        href="{{ \Illuminate\Support\Facades\Storage::disk('public')->exists('favicon.ico') ? asset('storage/favicon.ico') : asset('favicon.ico') }}"
        type="image/x-icon">

    <script src="{{ asset('plugins/alpinejs/3.12.0_cdn.min.js') }}" defer></script>

    {{-- <link rel="stylesheet" href="{{asset('css/adminlte.min.css')}}"> --}}
    <link rel="stylesheet" href="{{ asset('plugins/datatables/jquery.dataTables.min.css') }}">

    {{-- summernote --}}
    <link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">

    {{-- datetimepicker --}}
    <link rel="stylesheet"
        href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">


    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="preload" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    </noscript>
    <script src="{{ asset('js/app.js') }}"></script>
    <!-- tinymce -->
    <script src="{{ asset('plugins/tinymce/js/tinymce/tinymce.min.js') }}"></script>

    <!-- SweetAlert2 with Dark Theme -->
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <style>
        /* SweetAlert2 Glass Theme Overrides */
        .swal2-popup {
            @apply bg-zinc-900/95 backdrop-blur-sm border border-zinc-800/50 !important;

        }
        .swal2-title {
            @apply text-white !important;
        }
        .swal2-html-container {
            @apply text-zinc-300 !important;
        }
        .swal2-confirm {
            @apply bg-primary-800 text-primary-200 hover:bg-primary-700 !important;
        }
        .swal2-deny {
            @apply bg-red-800 text-red-200 hover:bg-red-700 !important;
        }
        .swal2-cancel {
            @apply bg-zinc-800 text-zinc-200 hover:bg-zinc-700 !important;
        }
    </style>

    <style>
        #userDropdown.dropdown-toggle::after {
            display: none !important;
        }
        
        .sidebar-mini.sidebar-collapse .brand-link span {
            opacity: 0;
            visibility: hidden;
            width: 0;
            display: none;
        }

        .brand-link img {
            transition: margin .3s ease-in-out;
        }

        .sidebar-mini.sidebar-collapse .brand-link img {
            margin-right: 0;
        }
    </style>
    @vite('themes/default/sass/app.scss')
    @vite('themes/default/css/app.css')
</head>

<body class="min-h-screen bg-zinc-950 sidebar-mini layout-fixed">
    <div class="wrapper bg-zinc-950">
        <!-- Navbar -->
        <nav class="main-header sticky-top navbar navbar-expand bg-zinc-900/50 backdrop-blur-sm border-b border-zinc-800/50">
            <!-- Left navbar links -->
            <ul class="navbar-nav flex items-center gap-2">
                <li class="nav-item">
                    <button class="p-2 text-zinc-400 hover:text-white transition-colors rounded-lg hover:bg-zinc-800/50" data-widget="pushmenu">
                        <i class="fas fa-bars"></i>
                    </button>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('home') }}" class="p-2 text-zinc-400 hover:text-white transition-colors rounded-lg hover:bg-zinc-800/50 flex items-center gap-2">
                        <i class="fas fa-home"></i>
                        <span>{{ __('Home') }}</span>
                    </a>
                </li>
                @if (!empty($discord_settings->invite_url))
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="{{ $discord_settings->invite_url }}" class="p-2 text-zinc-400 hover:text-white transition-colors rounded-lg hover:bg-zinc-800/50 flex items-center gap-2" target="__blank">
                            <i class="fab fa-discord"></i>
                            <span>{{ __('Discord') }}</span>
                        </a>
                    </li>
                @endif

                @foreach ($useful_links as $link)
                    <li class="nav-item d-none d-sm-inline-block">
                        <a href="{{ $link->link }}" class="p-2 text-zinc-400 hover:text-white transition-colors rounded-lg hover:bg-zinc-800/50 flex items-center gap-2" target="__blank">
                            <i class="{{ $link->icon }}"></i>
                            <span>{{ $link->title }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <!-- Right navbar links -->
            <ul class="ml-auto navbar-nav flex items-center gap-2">
                <!-- Credits Dropdown -->
                <li class="nav-item dropdown">
                    <button class="p-2 text-zinc-400 hover:text-white transition-colors rounded-lg hover:bg-zinc-800/50 flex items-center gap-2" id="creditsDropdown" data-toggle="dropdown">
                        <i class="fas fa-coins"></i>
                        <span>{{ Auth::user()->credits() }}</span>
                    </button>
                    <div class="shadow dropdown-menu dropdown-menu-right bg-zinc-800 border border-zinc-700 rounded-lg mt-2">
                        <a class="dropdown-item text-zinc-300 hover:bg-zinc-700 px-4 py-2 flex items-center gap-2" href="{{ route('store.index') }}">
                            <i class="fas fa-coins"></i>
                            <span>{{ __('Store') }}</span>
                        </a>
                        <div class="border-t border-zinc-700 my-1"></div>
                        <a class="dropdown-item text-zinc-300 hover:bg-zinc-700 px-4 py-2 flex items-center gap-2" data-toggle="modal" data-target="#redeemVoucherModal" href="javascript:void(0)">
                            <i class="fas fa-money-check-alt"></i>
                            <span>{{ __('Redeem code') }}</span>
                        </a>
                    </div>
                </li>

                <!-- User Dropdown -->
                <li class="nav-item dropdown">
                    <button class="p-2 text-zinc-400 hover:text-white transition-colors rounded-lg hover:bg-zinc-800/50 flex items-center gap-2" id="userDropdown" data-toggle="dropdown">
                        <span>{{ Auth::user()->name }}</span>
                        <img class="w-8 h-8 rounded-full shadow-md object-cover" src="{{ Auth::user()->getAvatar() }}" alt="User avatar">
                        @if (Auth::user()->unreadNotifications->count() != 0)
                            <span class="absolute top-1 right-1 w-2 h-2 bg-warning rounded-full"></span>
                        @endif
                    </button>
                    <div class="shadow dropdown-menu dropdown-menu-right bg-zinc-800 border border-zinc-700 rounded-lg mt-2">
                        <a class="dropdown-item text-zinc-300 hover:bg-zinc-700 px-4 py-2 flex items-center gap-2" href="{{ route('profile.index') }}">
                            <i class="fas fa-user"></i>
                            <span>{{ __('Profile') }}</span>
                        </a>
                        <a class="dropdown-item text-zinc-300 hover:bg-zinc-700 px-4 py-2 flex items-center gap-2" href="{{ route('notifications.index') }}">
                            <i class="fas fa-bell"></i>
                            <span>{{ __('Notifications') }}</span>
                            @if (Auth::user()->unreadNotifications->count() != 0)
                                <span class="badge badge-warning navbar-badge">
                                    {{ Auth::user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </a>
                        <a class="dropdown-item text-zinc-300 hover:bg-zinc-700 px-4 py-2 flex items-center gap-2" href="{{ route('preferences.index') }}">
                            <i class="fas fa-cog"></i>
                            <span>{{ __('Preferences') }}</span>
                        </a>
                        @if (session()->get('previousUser'))
                            <div class="border-t border-zinc-700 my-1"></div>
                            <a class="dropdown-item text-zinc-300 hover:bg-zinc-700 px-4 py-2 flex items-center gap-2" href="{{ route('users.logbackin') }}">
                                <i class="fas fa-sign-in-alt"></i>
                                <span>{{ __('Log back in') }}</span>
                            </a>
                        @endif
                        <div class="border-t border-zinc-700 my-1"></div>
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item text-zinc-300 hover:bg-zinc-700 px-4 py-2 flex items-center gap-2 w-full text-left" type="submit">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>{{ __('Logout') }}</span>
                            </button>
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        </form>
                    </div>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->
        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary bg-zinc-900/50 backdrop-blur-sm border-r border-zinc-800/50">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="brand-link flex items-center px-4 py-4 border-b border-zinc-800/50">
                <img width="32" height="32"
                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->exists('icon.png') ? asset('storage/icon.png') : asset('images/ctrlpanel_logo.png') }}"
                    alt="{{ config('app.name', 'Laravel') }} Logo" 
                    class="rounded-full">
                <span class="ml-3 font-medium text-white brand-text">{{ config('app.name', 'CtrlPanel.gg') }}</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar overflow-y-auto">
                <!-- Sidebar Menu -->
                <nav class="my-4">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="{{ route('home') }}"
                                class="nav-link @if (Request::routeIs('home')) active @endif">
                                <i class="nav-icon fa fa-home text-zinc-400"></i>
                                <p class="ml-3">{{ __('Dashboard') }}</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('servers.index') }}"
                                class="nav-link @if (Request::routeIs('servers.*')) active @endif">
                                <i class="nav-icon fa fa-server text-zinc-400"></i>
                                <p class="ml-3">{{ __('Servers') }}
                                    <span class="badge ml-auto px-2 py-1 text-xs font-medium bg-primary-800/50 text-primary-200 rounded-md">
                                        {{ Auth::user()->servers()->count() }} / {{ Auth::user()->server_limit }}
                                    </span>
                                </p>
                            </a>
                        </li>

                        @if (env('APP_ENV') == 'local' || $general_settings->store_enabled)
                            <li class="nav-item">
                                <a href="{{ route('store.index') }}"
                                    class="nav-link @if (Request::routeIs('store.*') || Request::routeIs('checkout')) active @endif">
                                    <i class="nav-icon fa fa-coins text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Store') }}</p>
                                </a>
                            </li>
                        @endif
                        @php($ticket_enabled = app(App\Settings\TicketSettings::class)->enabled)
                        @if ($ticket_enabled)
                            @canany(["user.ticket.read", "user.ticket.write"])
                            <li class="nav-item">
                                <a href="{{ route('ticket.index') }}"
                                    class="nav-link @if (Request::routeIs('ticket.*')) active @endif">
                                    <i class="nav-icon fas fa-ticket-alt text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Support Ticket') }}</p>
                                </a>
                            </li>
                                @endcanany
                        @endif

                    <!-- lol how do i make this shorter? -->
                        @canany(['settings.discord.read','settings.discord.write','settings.general.read','settings.general.write','settings.invoice.read','settings.invoice.write','settings.locale.read','settings.locale.write','settings.mail.read','settings.mail.write','settings.pterodactyl.read','settings.pterodactyl.write','settings.referral.read','settings.referral.write','settings.server.read','settings.server.write','settings.ticket.read','settings.ticket.write','settings.user.read','settings.user.write','settings.website.read','settings.website.write','settings.paypal.read','settings.paypal.write','settings.stripe.read','settings.stripe.write','settings.mollie.read','settings.mollie.write','settings.mercadopago.read','settings.mercadopago.write','admin.overview.read','admin.overview.sync','admin.ticket.read','admin.tickets.write','admin.ticket_blacklist.read','admin.ticket_blacklist.write','admin.roles.read','admin.roles.write','admin.api.read','admin.api.write'])
                            <li class="nav-header">{{ __('Administration') }}</li>
                        @endcanany

                        @canany(['admin.overview.read','admin.overview.sync'])
                            <li class="nav-item">
                                <a href="{{ route('admin.overview.index') }}"
                                    class="nav-link @if (Request::routeIs('admin.overview.*')) active @endif">
                                    <i class="nav-icon fa fa-home text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Overview') }}</p>
                                </a>
                            </li>
                        @endcanany

                        @canany(['admin.ticket.read','admin.tickets.write'])
                            <li class="nav-item">
                                <a href="{{ route('admin.ticket.index') }}"
                                   class="nav-link @if (Request::routeIs('admin.ticket.index')) active @endif">
                                    <i class="nav-icon fas fa-ticket-alt text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Ticket List') }}</p>
                                </a>
                            </li>
                        @endcanany

                        @canany(['admin.ticket.read','admin.tickets.write'])
                            <li class="nav-item">
                                <a href="{{ route('admin.ticket.category.index') }}"
                                   class="nav-link @if (Request::routeIs('admin.ticket.category.*')) active @endif">
                                    <i class="nav-icon fas fa-list text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Ticket Categories') }}</p>
                                </a>
                            </li>
                        @endcanany

                        @canany(['admin.ticket_blacklist.read','admin.ticket_blacklist.write'])
                            <li class="nav-item">
                                <a href="{{ route('admin.ticket.blacklist') }}"
                                   class="nav-link @if (Request::routeIs('admin.ticket.blacklist')) active @endif">
                                    <i class="nav-icon fas fa-user-times text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Ticket Blacklist') }}</p>
                                </a>
                            </li>
                        @endcanany

                        @canany(['admin.roles.read','admin.roles.write'])
                            <li class="nav-item">
                                <a href="{{ route('admin.roles.index') }}"
                                   class="nav-link @if (Request::routeIs('admin.roles.*')) active @endif">
                                    <i class="nav-icon fa fa-user-check text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Role Management') }}</p>
                                </a>
                            </li>
                            @endcanany

                        @canany(['settings.discord.read',
                                'settings.discord.write',
                                'settings.general.read',
                                'settings.general.write',
                                'settings.invoice.read',
                                'settings.invoice.write',
                                'settings.locale.read',
                                'settings.locale.write',
                                'settings.mail.read',
                                'settings.mail.write',
                                'settings.pterodactyl.read',
                                'settings.pterodactyl.write',
                                'settings.referral.read',
                                'settings.referral.write',
                                'settings.server.read',
                                'settings.server.write',
                                'settings.ticket.read',
                                'settings.ticket.write',
                                'settings.user.read',
                                'settings.user.write',
                                'settings.website.read',
                                'settings.website.write',
                                'settings.paypal.read',
                                'settings.paypal.write',
                                'settings.stripe.read',
                                'settings.stripe.write',
                                'settings.mollie.read',
                                'settings.mollie.write',
                                'settings.mercadopago.read',
                                'settings.mercadopago.write',])
                            <li class="nav-item">
                                <a href="{{ route('admin.settings.index') . '#icons' }}"
                                    class="nav-link @if (Request::routeIs('admin.settings.*')) active @endif">
                                    <i class="nav-icon fas fa-tools text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Settings') }}</p>
                                </a>
                            </li>
                        @endcanany

                        @canany(['admin.api.read','admin.api.write'])
                            <li class="nav-item">
                                <a href="{{ route('admin.api.index') }}"
                                    class="nav-link @if (Request::routeIs('admin.api.*')) active @endif">
                                    <i class="nav-icon fa fa-gamepad text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Application API') }}</p>
                                </a>
                            </li>
                        @endcanany

                        <!-- good fuck do i shorten this lol -->
                        @canany(['admin.users.read',
                                'admin.users.write',
                                'admin.users.suspend',
                                'admin.users.write.credits',
                                'admin.users.write.username',
                                'admin.users.write.password',
                                'admin.users.write.role',
                                'admin.users.write.referral',
                                'admin.users.write.pterodactyl','admin.servers.read',
                                'admin.servers.write',
                                'admin.servers.suspend',
                                'admin.servers.write.owner',
                                'admin.servers.write.identifier',
                                'admin.servers.delete','admin.products.read',
                                'admin.products.create',
                                'admin.products.edit',
                                'admin.products.delete',])
                            <li class="nav-header">{{ __('Management') }}</li>
                        @endcanany



                        @canany(['admin.users.read',
                                'admin.users.write',
                                'admin.users.suspend',
                                'admin.users.write.credits',
                                'admin.users.write.username',
                                'admin.users.write.password',
                                'admin.users.write.role',
                                'admin.users.write.referral',
                                'admin.users.write.pterodactyl'])
                            <li class="nav-item">
                                <a href="{{ route('admin.users.index') }}"
                                    class="nav-link @if (Request::routeIs('admin.users.*')) active @endif">
                                    <i class="nav-icon fas fa-users text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Users') }}</p>
                                </a>
                            </li>
                        @endcanany
                        @canany(['admin.servers.read',
                                'admin.servers.write',
                                'admin.servers.suspend',
                                'admin.servers.write.owner',
                                'admin.servers.write.identifier',
                                'admin.servers.delete'])
                            <li class="nav-item">
                                <a href="{{ route('admin.servers.index') }}"
                                    class="nav-link @if (Request::routeIs('admin.servers.*')) active @endif">
                                    <i class="nav-icon fas fa-server text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Servers') }}</p>
                                </a>
                            </li>
                        @endcanany
                        @canany(['admin.products.read',
                                'admin.products.create',
                                'admin.products.edit',
                                'admin.products.delete'])
                            <li class="nav-item">
                                <a href="{{ route('admin.products.index') }}"
                                    class="nav-link @if (Request::routeIs('admin.products.*')) active @endif">
                                    <i class="nav-icon fas fa-sliders-h text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Products') }}</p>
                                </a>
                            </li>
                        @endcanany
                        @canany(['admin.store.read','admin.store.write','admin.store.disable'])
                            <li class="nav-item">
                                <a href="{{ route('admin.store.index') }}"
                                    class="nav-link @if (Request::routeIs('admin.store.*')) active @endif">
                                    <i class="nav-icon fas fa-shopping-basket text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Store') }}</p>
                                </a>
                            </li>
                        @endcanany
                        @canany(["admin.voucher.read","admin.voucher.write"])
                            <li class="nav-item">
                                <a href="{{ route('admin.vouchers.index') }}"
                                    class="nav-link @if (Request::routeIs('admin.vouchers.*')) active @endif">
                                    <i class="nav-icon fas fa-money-check-alt text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Vouchers') }}</p>
                                </a>
                            </li>
                        @endcanany
                        @canany(["admin.partners.read","admin.partners.write"])
                            <li class="nav-item">
                                <a href="{{ route('admin.partners.index') }}"
                                    class="nav-link @if (Request::routeIs('admin.partners.*')) active @endif">
                                    <i class="nav-icon fas fa-handshake text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Partners') }}</p>
                                </a>
                            </li>
                        @endcanany

												@canany(["admin.coupons.read", "admin.coupons.write"])
                            <li class="nav-item">
                                <a href="{{ route('admin.coupons.index') }}"
                                    class="nav-link @if (Request::routeIs('admin.coupons.*')) active @endif">
                                    <i class="nav-icon fas fa-ticket-alt text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Coupons') }}</p>
                                </a>
                            </li>
                        @endcanany

                            @canany(["admin.useful_links.read","admin.legal.read"])
                                <li class="nav-header">{{ __('Other') }}</li>
                            @endcanany

                        @canany(["admin.useful_links.read","admin.useful_links.write"])
                            <li class="nav-item">
                                <a href="{{ route('admin.usefullinks.index') }}"
                                    class="nav-link @if (Request::routeIs('admin.usefullinks.*')) active @endif">
                                    <i class="nav-icon fas fa-link text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Useful Links') }}</p>
                                </a>
                            </li>
                            @endcanany

                            @canany(["admin.payments.read","admin.logs.read"])
                                <li class="nav-header">{{ __('Logs') }}</li>
                            @endcanany

                        @can("admin.payments.read")
                            <li class="nav-item">
                                <a href="{{ route('admin.payments.index') }}"
                                    class="nav-link @if (Request::routeIs('admin.payments.*')) active @endif">
                                    <i class="nav-icon fas fa-money-bill-wave text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Payments') }}
                                        <span
                                            class="badge badge-success right">{{ \App\Models\Payment::count() }}</span>
                                    </p>
                                </a>
                            </li>
                        @endcan

                        @can("admin.logs.read")
                            <li class="nav-item">
                                <a href="{{ route('admin.activitylogs.index') }}"
                                    class="nav-link @if (Request::routeIs('admin.activitylogs.*')) active @endif">
                                    <i class="nav-icon fas fa-clipboard-list text-zinc-400"></i>
                                    <p class="ml-3">{{ __('Activity Logs') }}</p>
                                </a>
                            </li>
                        @endcan


                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->

        <div class="content-wrapper bg-zinc-950">

            <!--
            @if (!Auth::user()->hasVerifiedEmail())
                @if (Auth::user()->created_at->diffInHours(now(), false) > 1)
                    <div class="p-2 m-2 alert alert-warning">
                        <h5><i class="icon fas fa-exclamation-circle"></i> {{ __('Warning!') }}</h5>
                        {{ __('You have not yet verified your email address') }} <a class="text-primary"
                            href="{{ route('verification.send') }}">{{ __('Click here to resend verification email') }}</a>
                        <br>
                        {{ __('Please contact support If you didnt receive your verification email.') }}
                    </div>
                @endif
            @endif
            -->

            @yield('content')

            @include('models.redeem_voucher_modal')
        </div>
        <!-- /.content-wrapper -->
        <footer class="bg-zinc-900/50 border-t border-zinc-800/50 p-4 mt-auto">
            <div class="w-full flex flex-wrap justify-between items-center text-sm text-zinc-400">
                <div>
                    <strong>Copyright &copy; 2021-{{ date('Y') }} 
                        <a href="{{ url('/') }}" class="text-zinc-300 hover:text-white transition-colors">
                            {{ env('APP_NAME', 'Laravel') }}
                        </a>
                    </strong>
                    <span class="px-1">·</span>
                    Powered by <a href="https://CtrlPanel.gg" class="text-zinc-300 hover:text-white transition-colors">CtrlPanel</a>
                    @if (!str_contains(config('BRANCHNAME'), 'main') && !str_contains(config('BRANCHNAME'), 'unknown'))
                        <span class="px-1">·</span> 
                        Version <b>{{ config('app')['version'] }} - {{ config('BRANCHNAME') }}</b>
                    @endif
                </div>

                <div class="flex gap-3">
                    @if ($website_settings->show_imprint)
                        <a href="{{ route('terms', 'imprint') }}" target="_blank" 
                           class="hover:text-white transition-colors">
                            {{ __('Imprint') }}
                        </a>
                    @endif

                    @if ($website_settings->show_privacy)
                        <a href="{{ route('terms', 'privacy') }}" target="_blank"
                           class="hover:text-white transition-colors">
                            {{ __('Privacy') }}
                        </a>
                    @endif

                    @if ($website_settings->show_tos)
                        <a href="{{ route('terms', 'tos') }}" target="_blank"
                           class="hover:text-white transition-colors">
                            {{ __('Terms of Service') }}
                        </a>
                    @endif
                </div>
            </div>
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- Scripts -->
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <!-- Summernote -->
    <script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
    <!-- select2 -->
    <script src="{{ asset('plugins/select2/js/select2.min.js') }}"></script>

    <!-- Moment.js -->
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>

    <!-- Datetimepicker -->
    <script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>

    <!-- Select2 -->
    <script src={{ asset('plugins/select2/js/select2.min.js') }}></script>


    <script>
        $(document).ready(function() {
            $('[data-toggle="popover"]').popover();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        });
    </script>
    <script>
        // Toast notification configuration
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: 'rgb(24 24 27 / 0.9)',
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Session notifications
        @if (Session::has('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: '{{ Session::get('error') }}',
                customClass: {
                    popup: 'glass-panel !bg-zinc-900/95',
                }
            });
        @endif

        @if (Session::has('success'))
            Toast.fire({
                icon: 'success',
                title: '{{ Session::get('success') }}'
            });
        @endif

        @if (Session::has('info'))
            Toast.fire({
                icon: 'info',
                title: '{{ Session::get('info') }}'
            });
        @endif

        @if (Session::has('warning'))
            Toast.fire({
                icon: 'warning',
                title: '{{ Session::get('warning') }}'
            });
        @endif
    </script>
</body>

</html>
