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
                    'settings.mercadopago.write'])
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
                    'admin.products.delete'])
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