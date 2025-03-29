<?php

namespace App\Helpers;

class CurrencyHelper
{
    public function convertForDisplay($amount)
    {
        return $amount / 1000;
    }

    public function formatForDisplay($amount, $decimals = 2)
    {
        return number_format($this->convertForDisplay($amount), $decimals, '.', ',');
    }

    public function formatForForm($amount, $decimals = 2)
    {
        return number_format($this->convertForDisplay($amount), $decimals, '.', '');
    }

    public function prepareForDatabase($amount)
    {
        return (int)($amount * 1000);
    }
}
