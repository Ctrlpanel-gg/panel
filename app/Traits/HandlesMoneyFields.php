<?php

namespace App\Traits;

use App\Models\User;

trait HandlesMoneyFields
{

    protected function convertToInteger($amount, $precision = 2)
    {
        if ($this instanceof User || property_exists($this, 'minimum_credits')) {
            $precision = 4;
        }
        return (int) bcmul($amount, bcpow(10, $precision), 0);
    }

    protected function convertFromInteger($amount, $precision = 2) 
    {
        if ($this instanceof User || property_exists($this, 'minimum_credits')) {
            $precision = 4;
        }
        return bcdiv($amount, bcpow(10, $precision), $precision);
    }

    public function formatToCurrency($value)
    {
        $currencyCode = property_exists($this, 'currency_code') ? $this->currency_code : '€';
        return $currencyCode . number_format($this->convertFromInteger($value), 2);
    }
}
