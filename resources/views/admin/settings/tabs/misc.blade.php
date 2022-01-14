<div class="tab-pane mt-3" id="misc">
    <form method="POST" enctype="multipart/form-data" class="mb-3"
        action="{{ route('admin.settings.update.miscsettings') }}">
        @csrf
        @method('PATCH')

        <div class="row">
            <!-- DISCORD -->
            <div class="col-md-3">
                <div class="row mb-3">
                    <div class="col  text-center">
                        <img height="50" src="{{ url('/images/discord_logo.png') }}"></br>
                        <span class=" mt-0 text-muted">({{ __('optional') }})</span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="discord-client-id">{{ __('Discord Client-ID') }}</label>
                        <input x-model="discord-client-id" id="discord-client-id" name="discord-client-id" type="text"
                            value="{{ config('SETTINGS::DISCORD:CLIENT_ID') }}"
                            class="form-control @error('discord-client-id') is-invalid @enderror">
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="discord-client-secret">{{ __('Discord Client-Secret') }} </label>
                        <input x-model="discord-client-secret" id="discord-client-secret" name="discord-client-secret"
                            type="text" value="{{ config('SETTINGS::DISCORD:CLIENT_SECRET') }}"
                            class="form-control @error('discord-client-secret') is-invalid @enderror">
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="discord-client-secret">{{ __('Discord Bot-Token') }} </label>
                        <input x-model="discord-bot-token" id="discord-bot-token" name="discord-bot-token" type="text"
                            value="{{ config('SETTINGS::DISCORD:BOT_TOKEN') }}"
                            class="form-control @error('discord-bot-token') is-invalid @enderror">
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="discord-client-secret">{{ __('Discord Guild-ID') }} </label>
                        <input x-model="discord-guild-id" id="discord-guild-id" name="discord-guild-id" type="number"
                            value="{{ config('SETTINGS::DISCORD:GUILD_ID') }}"
                            class="form-control @error('discord-guild-id') is-invalid @enderror">
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="discord-invite-url">{{ __('Discord Invite-URL') }} </label>
                        <input x-model="discord-invite-url" id="discord-invite-url" name="discord-invite-url"
                            type="text" value="{{ config('SETTINGS::DISCORD:INVITE_URL') }}"
                            class="form-control @error('discord-invite-url') is-invalid @enderror">
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="discord-role-id">{{ __('Discord Role-ID') }}
                        </label>
                        <input x-model="discord-role-id" id="discord-role-id" name="discord-role-id" type="number"
                            value="{{ config('SETTINGS::DISCORD:ROLE_ID') }}"
                            class="form-control @error('discord-role-id') is-invalid @enderror">
                    </div>
                </div>

            </div>
        </div>



        <button class="btn btn-primary mt-3">{{ __('Submit') }}</button>
    </form>



</div>
