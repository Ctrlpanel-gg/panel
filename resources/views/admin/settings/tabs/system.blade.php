<div class="tab-pane mt-3" id="system">
    <form method="POST" enctype="multipart/form-data" class="mb-3"
        action="{{ route('admin.settings.update.systemsettings') }}">
        @csrf
        @method('PATCH')

        <div class="form-group">
            <div class="custom-control mb-3">
                <label for="phpmyadmin-url">{{ __('PHPMyAdmin URL') }}</label>
                <input x-model="phpmyadmin-url" id="phpmyadmin-url" name="phpmyadmin-url" type="text"
                    value="{{ config('SETTINGS::MISC:PHPMYADMIN:URL') }}"
                    class="form-control @error('phpmyadmin-url') is-invalid @enderror">
            </div>
        </div>

</div>
