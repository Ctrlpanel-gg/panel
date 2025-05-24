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