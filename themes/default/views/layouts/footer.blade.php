        <footer
            class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 border-t border-gray-700/50 transition-all duration-300 ease-in-out"
            x-data="{ sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false' }" @sidebar-toggle.window="sidebarOpen = $event.detail.open"
            :class="sidebarOpen ? 'md:ml-64' : 'md:ml-20'" x-cloak>
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                    {{-- Copyright section --}}
                    <div class="text-center sm:text-left">
                        <p class="text-gray-400 text-sm">
                            Copyright &copy; {{ date('Y') }}
                            <a href="{{ url('/') }}"
                                class="text-accent-400 hover:text-accent-300 font-semibold transition-colors duration-200">
                                {{ config('app.name', 'Ctrlpanel.gg') }}
                            </a>.
                            All rights reserved.
                        </p>
                        <p class="text-gray-500 text-xs mt-1">
                            Powered by
                            <a href="https://CtrlPanel.gg" target="_blank"
                                class="text-accent-400 hover:text-accent-300 font-semibold transition-colors duration-200">
                                CtrlPanel
                            </a>
                            &copy; 2021-{{ date('Y') }}
                        </p>
                    </div>

                    @if ($website_settings->show_imprint || $website_settings->show_privacy || $website_settings->show_tos)
                        {{-- Legal links --}}
                        <div class="flex items-center space-x-4">
                            @if ($website_settings->show_imprint)
                                <a target="_blank" href="{{ route('terms', 'imprint') }}"
                                    class="text-sm text-gray-400 hover:text-white font-medium transition-colors duration-200">
                                    {{ __('Imprint') }}
                                </a>
                            @endif

                            @if ($website_settings->show_privacy)
                                <a target="_blank" href="{{ route('terms', 'privacy') }}"
                                    class="text-sm text-gray-400 hover:text-white font-medium transition-colors duration-200">
                                    {{ __('Privacy') }}
                                </a>
                            @endif

                            @if ($website_settings->show_tos)
                                <a target="_blank" href="{{ route('terms', 'tos') }}"
                                    class="text-sm text-gray-400 hover:text-white font-medium transition-colors duration-200">
                                    {{ __('Terms of Service') }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </footer>
