<div class="tab-pane mt-3" id="misc">
    <form method="POST" enctype="multipart/form-data" class="mb-3"
        action="{{ route('admin.settings.update.miscsettings') }}">
        @csrf
        @method('PATCH')

        <div class="row">
            <div class="col-md-6 col-lg-4 col-12">

                <!-- PHPMYADMIN -->

                <!-- Icetoast das sieht auch kacke aus... -->

                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label
                            for="phpmyadmin-url">{{ __('The URL to your PHPMYADMIN Panel. Must not end with a /, leave blank to remove database button') }}</label>
                        <input x-model="phpmyadmin-url" id="phpmyadmin-url" name="phpmyadmin-url" type="text"
                            value="{{ config('SETTINGS::MISC:PHPMYADMIN:URL') }}"
                            class="form-control @error('phpmyadmin-url') is-invalid @enderror">
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
                </div>
                <div class="form-group">
                    <div class="custom-file mb-3">
                        <input type="file" accept="image/x-icon" class="custom-file-input" name="favicon" id="favicon">
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
            <div class="col-md-3">
                <img class="mb-3" height="50" src="{{ url('/images/discord_logo.png') }}">

                <!-- DISCORD -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="discord-client-id">{{ __('Your Discord client-id') }} ( Discord API Credentials -
                            https://discordapp.com/developers/applications/ ) </label>
                        <input x-model="discord-client-id" id="discord-client-id" name="discord-client-id" type="text"
                            value="{{ config('SETTINGS::DISCORD:CLIENT_ID') }}"
                            class="form-control @error('discord-client-id') is-invalid @enderror">
                    </div>
                </div>
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="discord-client-secret">{{ __('Your Discord client-secret') }} </label>
                        <input x-model="discord-client-secret" id="discord-client-secret" name="discord-client-secret"
                            type="text" value="{{ config('SETTINGS::DISCORD:CLIENT_SECRET') }}"
                            class="form-control @error('discord-client-secret') is-invalid @enderror">
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="discord-client-secret">{{ __('Your Discord Bot-token') }} </label>
                        <input x-model="discord-bot-token" id="discord-bot-token" name="discord-bot-token" type="text"
                            value="{{ config('SETTINGS::DISCORD:BOT_TOKEN') }}"
                            class="form-control @error('discord-bot-token') is-invalid @enderror">
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="discord-client-secret">{{ __('Your Discord Guild-ID') }} </label>
                        <input x-model="discord-guild-id" id="discord-guild-id" name="discord-guild-id" type="number"
                            value="{{ config('SETTINGS::DISCORD:GUILD_ID') }}"
                            class="form-control @error('discord-guild-id') is-invalid @enderror">
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="discord-invite-url">{{ __('Your Discord Server iniviation url') }} </label>
                        <input x-model="discord-invite-url" id="discord-invite-url" name="discord-invite-url"
                            type="text" value="{{ config('SETTINGS::DISCORD:INVITE_URL') }}"
                            class="form-control @error('discord-invite-url') is-invalid @enderror">
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label
                            for="discord-role-id">{{ __('Discord role that will be assigned to users when they register (optional)') }}
                        </label>
                        <input x-model="discord-role-id" id="discord-role-id" name="discord-role-id" type="number"
                            value="{{ config('SETTINGS::DISCORD:ROLE_ID') }}"
                            class="form-control @error('discord-role-id') is-invalid @enderror">
                    </div>
                </div>

            </div>

        </div>

        <button class="btn btn-primary">{{ __('Submit') }}</button>
    </form>

    <p class="text-muted">
        {{ __('Images and Icons may be cached, reload without cache to see your changes appear') }}
    </p>

</div>
