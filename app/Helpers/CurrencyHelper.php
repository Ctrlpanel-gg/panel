<?php

namespace App\Helpers;

use NumberFormatter;

class CurrencyHelper
{
    private function convertForDisplay($amount)
    {
        return $amount / 1000;
    }

    public function formatForDisplay($amount, $decimals = 2)
    {
        return number_format($this->convertForDisplay($amount), $decimals, ',', '.');
    }

    public function formatForForm($amount, $decimals = 2)
    {
        return number_format($this->convertForDisplay($amount), $decimals, '.', '');
    }

    public function prepareForDatabase($amount)
    {
        return (int)($amount * 1000);
    }

    public function formatToCurrency(int $amount, $currency_code, $locale = null,)
    {
        $locale = $locale ?: str_replace('_', '-', app()->getLocale());

        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($this->convertForDisplay($amount), $currency_code);
    }
}
