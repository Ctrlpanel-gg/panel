<?php

namespace app\Settings;

use Spatie\LaravelSettings\Settings;

class InvoiceSettings extends Settings
{
    public string $company_address;

    public string $company_mail;

    public string $company_name;

    public string $company_phone;

    public int $company_vat;

    public string $company_website;

    public bool $enabled;

    public string $prefix;

    public static function group(): string
    {
        return 'invoice';
    }
}