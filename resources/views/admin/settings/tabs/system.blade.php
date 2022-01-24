<div class="tab-pane mt-3" id="system">
    <form method="POST" enctype="multipart/form-data" class="mb-3"
        action="{{ route('admin.settings.update.systemsettings') }}">
        @csrf
        @method('PATCH')

        <div class="row">
            {{-- System --}}
            <div class="col-md-3 px-3">
                <div class="row mb-2">
                    <div class="col text-center">
                        <h1>{{ __('System') }}</h1>
                    </div>
                </div>
                <div class="form-group">
                    <div class="custom-control mb-1 p-0">
                        <div class="col m-0 p-0 d-flex justify-content-between align-items-center">
                            <div>
                                <input value="true" id="register-ip-check" name="register-ip-check"
                                    {{ config('SETTINGS::SYSTEM:REGISTER_IP_CHECK') == 'true' ? 'checked' : '' }}
                                    type="checkbox">
                                <label for="register-ip-check">{{ __('Register IP Check') }} </label>
                            </div>
                            <i data-toggle="popover" data-trigger="hover" data-html="true"
                                data-content="{{ __('Prevent users from making multiple accounts using the same IP address.') }}"
                                class="fas fa-info-circle"></i>
                        </div>
                    </div>
                    <div class="custom-control mb-3 p-0">
                        <div class="col m-0 p-0 d-flex justify-content-between align-items-center">
                            <div>
                                <input value="true" id="server-create-charge-first" name="server-create-charge-first"
                                    {{ config('SETTINGS::SYSTEM:SERVER_CREATE_CHARGE_FIRST_HOUR') == 'true' ? 'checked' : '' }}
                                    type="checkbox">
                                <label for="server-create-charge-first">{{ __('Charge first hour at creation') }}
                                </label>
                            </div>
                            <i data-toggle="popover" data-trigger="hover" data-html="true"
                                data-content="{{ __('Charges the first hour worth of credits upon creating a server.') }}"
                                class="fas fa-info-circle"></i>
                        </div>
                    </div>

                    <div class="custom-control mb-3 p-0">
                        <label for="credits-display-name">{{ __('Credits Display Name') }}</label>
                        <input x-model="credits-display-name" id="credits-display-name" name="credits-display-name"
                            type="text" value="{{ config('SETTINGS::SYSTEM:CREDITS_DISPLAY_NAME', 'Credits') }}"
                            class="form-control @error('credits-display-name') is-invalid @enderror" required>
                    </div>
                    <div class="custom-control p-0 mb-3">
                        <div class="col m-0 p-0 d-flex justify-content-between align-items-center">
                            <label for="phpmyadmin-url">{{ __('PHPMyAdmin URL') }}</label>
                            <i data-toggle="popover" data-trigger="hover" data-html="true"
                                data-content="{{ __('Enter the URL to your PHPMyAdmin installation. <strong>Without a trailing slash!</strong>') }}"
                                class="fas fa-info-circle"></i>
                        </div>
                        <input x-model="phpmyadmin-url" id="phpmyadmin-url" name="phpmyadmin-url" type="text"
                            value="{{ config('SETTINGS::MISC:PHPMYADMIN:URL') }}"
                            class="form-control @error('phpmyadmin-url') is-invalid @enderror">
                    </div>
                    <div class="custom-control p-0 mb-3">
                        <div class="col m-0 p-0 d-flex justify-content-between align-items-center">
                            <label for="pterodactyl-url">{{ __('Pterodactyl URL') }}</label>
                            <i data-toggle="popover" data-trigger="hover" data-html="true"
                                data-content="{{ __('Enter the URL to your Pterodactyl installation. <strong>Without a trailing slash!</strong>') }}"
                                class="fas fa-info-circle"></i>
                        </div>
                        <input x-model="pterodactyl-url" id="pterodactyl-url" name="pterodactyl-url" type="text"
                            value="{{ config('SETTINGS::SYSTEM:PTERODACTYL:URL') }}"
                            class="form-control @error('pterodactyl-url') is-invalid @enderror" required>
                    </div>
                    <div class="custom-control p-0 mb-3">
                        <div class="col m-0 p-0 d-flex justify-content-between align-items-center">
                            <label for="pterodactyl-api-key">{{ __('Pterodactyl API Key') }}</label>
                            <i data-toggle="popover" data-trigger="hover" data-html="true"
                                data-content="{{ __('Enter the API Key to your Pterodactyl installation.') }}"
                                class="fas fa-info-circle"></i>
                        </div>
                        <input x-model="pterodactyl-api-key" id="pterodactyl-api-key" name="pterodactyl-api-key"
                            type="text" value="{{ config('SETTINGS::SYSTEM:PTERODACTYL:TOKEN') }}"
                            class="form-control @error('pterodactyl-api-key') is-invalid @enderror" required>
                    </div>

                </div>

            </div>

            {{-- User --}}
            <div class="col-md-3 px-3">
                <div class="row mb-2">
                    <div class="col text-center">
                        <h1>{{ __('User') }}</h1>
                    </div>
                </div>
                <div class="form-group">
                    <div class="custom-control mb-1 p-0">
                        <input value="true" id="force-discord-verification" name="force-discord-verification"
                            {{ config('SETTINGS::USER:FORCE_DISCORD_VERIFICATION') == 'true' ? 'checked' : '' }}
                            type="checkbox">
                        <label for="force-discord-verification">{{ __('Force Discord verification') }}
                        </label>
                    </div>
                    <div class="custom-control mb-3 p-0">
                        <input value="true" id="force-email-verification" name="force-email-verification"
                            {{ config('SETTINGS::USER:FORCE_EMAIL_VERIFICATION') == 'true' ? 'checked' : '' }}
                            type="checkbox">
                        <label for="force-email-verification">{{ __('Force E-Mail verification') }} </label>
                    </div>

                    <div class="custom-control mb-3 p-0">
                        <label for="initial-credits">{{ __('Initial Credits') }}</label>
                        <input x-model="initial-credits" id="initial-credits" name="initial-credits" type="number"
                            value="{{ config('SETTINGS::USER:INITIAL_CREDITS') }}"
                            class="form-control @error('initial-credits') is-invalid @enderror" required>
                    </div>
                    <div class="custom-control mb-3 p-0">
                        <label for="initial-server-limit">{{ __('Initial Server Limit') }}</label>
                        <input x-model="initial-server-limit" id="initial-server-limit" name="initial-server-limit"
                            type="number" value="{{ config('SETTINGS::USER:INITIAL_SERVER_LIMIT') }}"
                            class="form-control @error('initial-server-limit') is-invalid @enderror" required>
                    </div>
                    <div class="custom-control mb-3 p-0">
                        <label
                            for="credits-reward-amount-discord">{{ __('Credits Reward Amount - Discord') }}</label>
                        <input x-model="credits-reward-amount-discord" id="credits-reward-amount-discord"
                            name="credits-reward-amount-discord" type="number"
                            value="{{ config('SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_DISCORD') }}"
                            class="form-control @error('credits-reward-amount-discord') is-invalid @enderror" required>
                    </div>

                    <div class="custom-control mb-3 p-0">
                        <label for="credits-reward-amount-email">{{ __('Credits Reward Amount - E-Mail') }}</label>
                        <input x-model="credits-reward-amount-email" id="credits-reward-amount-email"
                            name="credits-reward-amount-email" type="number"
                            value="{{ config('SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_EMAIL') }}"
                            class="form-control @error('credits-reward-amount-email') is-invalid @enderror" required>
                    </div>
                    <div class="custom-control mb-3 p-0">
                        <label for="server-limit-discord">{{ __('Server Limit Increase - Discord') }}</label>
                        <input x-model="server-limit-discord" id="server-limit-discord" name="server-limit-discord"
                            type="number"
                            value="{{ config('SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_DISCORD') }}"
                            class="form-control @error('server-limit-discord') is-invalid @enderror" required>
                    </div>
                    <div class="custom-control mb-3 p-0">
                        <label for="server-limit-email">{{ __('Server Limit Increase - E-Mail') }}</label>
                        <input x-model="server-limit-email" id="server-limit-email" name="server-limit-email"
                            type="number"
                            value="{{ config('SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_EMAIL') }}"
                            class="form-control @error('server-limit-email') is-invalid @enderror" required>
                    </div>
                </div>
            </div>

            {{-- Server --}}
            <div class="col-md-3 px-3">
                <div class="row mb-2">
                    <div class="col text-center">
                        <h1>{{ __('Server') }}</h1>
                    </div>
                </div>
                <div class="form-group">
                    <div class="custom-control mb-3 p-0">
                        <div class="col m-0 p-0 d-flex justify-content-between align-items-center">
                            <label for="initial-credits">{{ __('Server Allocation Limit') }}</label>
                            <i data-toggle="popover" data-trigger="hover" data-html="true"
                                data-content="{{ __('The maximum amount of allocations to pull per node for automatic deployment, if more allocations are being used than this limit is set to, no new servers can be created!') }}"
                                class="fas fa-info-circle"></i>
                        </div>
                        <input x-model="allocation-limit" id="allocation-limit" name="allocation-limit" type="number"
                            value="{{ config('SETTINGS::SERVER:ALLOCATION_LIMIT') }}"
                            class="form-control @error('allocation-limit') is-invalid @enderror" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="custom-file mb-3 mt-3">
                        <input type="file" accept="image/png,image/jpeg,image/jpg" class="custom-file-input" name="icon"
                            id="icon">
                        <label class="custom-file-label selected" for="icon">{{ __('Select panel icon') }}</label>
                    </div>
                    @error('icon')
                        <span class="text-danger">
                            {{ $message }}
                        </span>
                    @enderror


                    <div class="form-group">
                        <div class="custom-file mb-3">
                            <input type="file" accept="image/x-icon" class="custom-file-input" name="favicon"
                                id="favicon">
                            <label class="custom-file-label selected"
                                for="favicon">{{ __('Select panel favicon') }}</label>
                        </div>
                        @error('favicon')
                            <span class="text-danger">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <button class="btn btn-primary ml-3 mt-3">{{ __('Submit') }}</button>
        </div>
    </form>
</div>
