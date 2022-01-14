@inject('Invoices', 'App\Classes\Settings\Invoices')

<div class="tab-pane mt-3" id="invoices">
    <form method="POST" enctype="multipart/form-data" class="mb-3"
        action="{{ route('admin.settings.update.invoicesettings') }}">
        @csrf
        @method('PATCH')

        <div class="row">
            <div class="col-md-6">
                <!-- Name -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="company-name">{{ __('Enter your companys name') }}</label>
                        <input x-model="company-name" id="company-name" name="company-name" type="text" required
                            value="{{ config('SETTINGS::INVOICE:COMPANY_NAME') }}"
                            class="form-control @error('company-name') is-invalid @enderror">
                    </div>
                </div>
                <!-- address -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="company-address">{{ __('Enter your companys address') }}</label>
                        <input x-model="company-address" id="company-address" name="company-address" type="text"
                            value="{{ config('SETTINGS::INVOICE:COMPANY_ADDRESS') }}"
                            class="form-control @error('company-address') is-invalid @enderror">
                    </div>
                </div>
                <!-- Phone -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="company-phone">{{ __('Enter your companys phone number') }}</label>
                        <input x-model="company-phone" id="company-phone" name="company-phone" type="text"
                            value="{{ config('SETTINGS::INVOICE:COMPANY_PHONE') }}"
                            class="form-control @error('company-phone') is-invalid @enderror">
                    </div>
                </div>

                <!-- VAT -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="company-vat">{{ __('Enter your companys VAT id') }}</label>
                        <input x-model="company-vat" id="company-vat" name="company-vat" type="text"
                            value="{{ config('SETTINGS::INVOICE:COMPANY_VAT') }}"
                            class="form-control @error('company-vat') is-invalid @enderror">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <!-- email -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="company-mail">{{ __('Enter your companys email address') }}</label>
                        <input x-model="company-mail" id="company-mail" name="company-mail" type="text"
                            value="{{ config('SETTINGS::INVOICE:COMPANY_MAIL') }}"
                            class="form-control @error('company-mail') is-invalid @enderror">
                    </div>
                </div>
                <!-- website -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="company-web">{{ __('Enter your companys website') }}</label>
                        <input x-model="company-web" id="company-web" name="company-web" type="text"
                            value="{{ config('SETTINGS::INVOICE:COMPANY_WEBSITE') }}"
                            class="form-control @error('company-web') is-invalid @enderror">
                    </div>
                </div>

                <!-- prefix -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="invoice-prefix">{{ __('Enter your custom invoice prefix') }}</label>
                        <input x-model="invoice-prefix" id="invoice-prefix" name="invoice-prefix" type="text" required
                            value="{{ config('SETTINGS::INVOICE:PREFIX') }}"
                            class="form-control @error('invoice-prefix') is-invalid @enderror">
                    </div>
                </div>

                <!-- logo -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="logo">{{ __('Logo') }}</label>
                        <div class="custom-file mb-3">
                            <input type="file" accept="image/png,image/jpeg,image/jpg" class="custom-file-input"
                                name="logo" id="logo">
                            <label class="custom-file-label selected"
                                for="favicon">{{ __('Select Invoice Logo') }}</label>
                        </div>
                    </div>
                    @error('logo')
                        <span class="text-danger">
                        </span>
                    @enderror
                </div>

            </div>
        </div>
        <button class="btn btn-primary">{{ __('Submit') }}</button>

        <!-- end -->

    </form>
</div>
