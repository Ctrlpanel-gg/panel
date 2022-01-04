@inject('Invoices', 'App\Classes\Settings\InvoiceSettingsC')

<div class="tab-pane mt-3" id="invoice">
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
                        <input x-model="company-name" id="company-name" name="company-name" type="text"
                            value="{{ $Invoices->invoiceSettings->company_name }}"
                            class="form-control @error('company-name') is-invalid @enderror">
                    </div>
                </div>
                <!-- address -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="company-address">{{ __('Enter your companys address') }}</label>
                        <input x-model="company-address" id="company-address" name="company-address" type="text"
                            value="{{ $Invoices->invoiceSettings->company_address }}"
                            class="form-control @error('company-address') is-invalid @enderror">
                    </div>
                </div>
                <!-- Phone -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="company-phone">{{ __('Enter your companys phone number') }}</label>
                        <input x-model="company-phone" id="company-phone" name="company-phone" type="text"
                            value="{{ $Invoices->invoiceSettings->company_phone }}"
                            class="form-control @error('company-phone') is-invalid @enderror">
                    </div>
                </div>

                <!-- VAT -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="company-vat">{{ __('Enter your companys VAT id') }}</label>
                        <input x-model="company-vat" id="company-vat" name="company-vat" type="text"
                            value="{{ $Invoices->invoiceSettings->company_vat }}"
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
                            value="{{ $Invoices->invoiceSettings->company_mail }}"
                            class="form-control @error('company-mail') is-invalid @enderror">
                    </div>
                </div>
                <!-- website -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="company-web">{{ __('Enter your companys website') }}</label>
                        <input x-model="company-web" id="company-web" name="company-web" type="text"
                            value="{{ $Invoices->invoiceSettings->company_web }}"
                            class="form-control @error('company-web') is-invalid @enderror">
                    </div>
                </div>

                <!-- website -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="invoice-prefix">{{ __('Enter your custom invoice prefix') }}</label>
                        <input x-model="invoice-prefix" id="invoice-prefix" name="invoice-prefix" type="text"
                            value="{{ $Invoices->invoiceSettings->invoice_prefix }}"
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
                            {{ $Invoices->invoiceSettings->message }}
                        </span>
                    @enderror
                </div>

            </div>
        </div>
        <button class="btn btn-primary">{{ __('Submit') }}</button>

        <!-- end -->

    </form>
</div>
