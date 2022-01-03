<div class="tab-pane mt-3" id="dashboard">
    <form method="POST" enctype="multipart/form-data" class="mb-3"
        action="{{ route('admin.settings.update.icons') }}">
        @csrf
        @method('PATCH')

        <div class="row">
            <div class="col-md-6 col-lg-4 col-12">
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
        </div>

        <button class="btn btn-primary">{{ __('Submit') }}</button>
    </form>

    <p class="text-muted">
        {{ __('Images and Icons may be cached, reload without cache to see your changes appear') }}
    </p>

</div>
