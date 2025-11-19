    <!-- Navbar -->
    <nav class="fixed top-0 z-50 bg-gray-900/95 backdrop-blur-xl border border-gray-800/30 shadow-sm transition-all"
        x-data="{ sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false' }"
        @sidebar-toggle.window="sidebarOpen = $event.detail.open; localStorage.setItem('sidebarOpen', sidebarOpen)"
        :class="sidebarOpen ? 'left-0 md:left-64 right-0' : 'left-0 md:left-20 right-0'">
        <div class="flex items-center h-14 px-3">
            <!-- Left side - Brand & Toggle -->
            <div class="flex items-center space-x-3">
                <!-- Toggle Button -->
                <button
                    @click="sidebarOpen = !sidebarOpen; localStorage.setItem('sidebarOpen', sidebarOpen); $dispatch('sidebar-toggle', { open: sidebarOpen })"
                    class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors duration-150">
                    <i class="fas fa-bars text-lg"></i>
                </button>

                <!-- Brand Logo moved to sidebar -->
            </div>

            <!-- Center - Navbar Links -->
            <div class="hidden md:flex items-center space-x-1 ml-6 flex-1">
                <a href="{{ route('home') }}"
                    class="flex items-center px-3 py-1.5 rounded-lg text-sm text-gray-400 hover:text-white hover:bg-gray-800 transition-all duration-200">
                    <i class="fas fa-home mr-2"></i>
                    <span>{{ __('Home') }}</span>
                </a>

                @foreach ($useful_links as $link)
                    <a href="{{ $link->link }}" target="__blank"
                        class="flex items-center px-3 py-1.5 rounded-lg text-sm text-gray-400 hover:text-white hover:bg-gray-800 transition-all duration-200">
                        <i class="{{ $link->icon }} mr-2"></i>
                        <span>{{ $link->title }}</span>
                    </a>
                @endforeach
            </div>

            <!-- Right navbar links -->
            <div class="flex items-center space-x-2 ml-auto">

                <!-- Credits Dropdown -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="flex items-center px-3 py-1.5 rounded-lg bg-gradient-to-r from-accent-600 to-accent-500 hover:from-accent-500 hover:to-accent-600 text-white text-sm font-semibold transition-all duration-200">
                        <i class="fas fa-coins mr-2 text-xs"></i>
                        <span>{{ Currency::formatForDisplay(Auth::user()->credits) }}</span>
                        <i class="fas fa-chevron-down ml-2 text-xs" :class="open ? 'rotate-180' : ''"
                            style="transition: transform 0.2s;"></i>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-48 rounded-lg bg-gray-800 border border-gray-700 shadow-xl overflow-hidden"
                        style="display: none;">
                        <a href="{{ route('store.index') }}"
                            class="flex items-center px-3 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">
                            <i class="fas fa-coins w-4 mr-2 text-accent-400"></i>
                            <span>{{ __('Store') }}</span>
                        </a>
                        <div class="border-t border-gray-700"></div>
                        <a @click="open = false; $dispatch('open-redeem-modal')" href="javascript:void(0)"
                            class="flex items-center px-3 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-colors cursor-pointer">
                            <i class="fas fa-money-check-alt w-4 mr-2 text-success"></i>
                            <span>{{ __('Redeem code') }}</span>
                        </a>
                    </div>
                </div>

                <!-- User Profile Dropdown -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="flex items-center space-x-2 px-2 py-1 rounded-lg hover:bg-gray-800 transition-all duration-200 group">
                        <span class="hidden lg:block text-sm text-gray-300 group-hover:text-white transition-colors">
                            {{ Auth::user()->name }}
                        </span>
                        <div class="relative">
                            <img width="32" height="32"
                                class="rounded-full ring-2 ring-gray-800 group-hover:ring-accent-500 transition-all duration-200"
                                src="{{ Auth::user()->getAvatar() }}" alt="{{ Auth::user()->name }}">
                            @if (Auth::user()->unreadNotifications->count() != 0)
                                <span
                                    class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-gradient-to-r from-warning to-danger text-xs font-bold text-white animate-pulse">
                                    {{ Auth::user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </div>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-52 rounded-lg bg-gray-800 border border-gray-700 shadow-xl overflow-hidden"
                        style="display: none;">

                        <a href="{{ route('profile.index') }}"
                            class="flex items-center px-3 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">
                            <i class="fas fa-user w-4 mr-2 text-accent-400"></i>
                            <span>{{ __('Profile') }}</span>
                        </a>

                        <a href="{{ route('notifications.index') }}"
                            class="flex items-center px-3 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">
                            <i class="fas fa-bell w-4 mr-2 text-warning"></i>
                            <span>{{ __('Notifications') }}</span>
                            @if (Auth::user()->unreadNotifications->count() != 0)
                                <span
                                    class="ml-auto flex h-5 w-5 items-center justify-center rounded-full bg-gradient-to-r from-warning to-danger text-xs font-bold text-white">
                                    {{ Auth::user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </a>

                        <a href="{{ route('preferences.index') }}"
                            class="flex items-center px-3 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">
                            <i class="fas fa-cog w-4 mr-2 text-gray-400"></i>
                            <span>{{ __('Preferences') }}</span>
                        </a>

                        @if (session()->get('previousUser'))
                            <div class="border-t border-gray-700"></div>
                            <a href="{{ route('users.logbackin') }}"
                                class="flex items-center px-3 py-2 text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">
                                <i class="fas fa-sign-in-alt w-4 mr-2 text-success"></i>
                                <span>{{ __('Log back in') }}</span>
                            </a>
                        @endif

                        <div class="border-t border-gray-700"></div>
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center px-3 py-2 text-sm text-danger hover:bg-gray-700 hover:text-danger/80 transition-colors">
                                <i class="fas fa-sign-out-alt w-4 mr-2"></i>
                                <span>{{ __('Logout') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <!-- /.navbar -->
