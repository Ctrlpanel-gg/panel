<?php

namespace App\Traits;

trait HandlesMoneyFields
{
    protected function convertToInteger($amount, $precision = 2)
    {
        // Issue: Potential floating point precision errors in multiplication
        if ($this instanceof User || property_exists($this, 'minimum_credits')) {
            $precision = 4;
        }
        // Fix: Use BCMath for precise calculations
        return (int) bcmul($amount, bcpow(10, $precision), 0);
    }

    protected function convertFromInteger($amount, $precision = 2) 
    {
        if ($this instanceof User || property_exists($this, 'minimum_credits')) {
            $precision = 4;
        }
        // Fix: Use BCMath for precise division
        return bcdiv($amount, bcpow(10, $precision), $precision);
    }

    public function formatToCurrency($value)
    {
        // Need to respect currency_code if available
        $currencyCode = property_exists($this, 'currency_code') ? $this->currency_code : '€';
        return $currencyCode . number_format($this->convertFromInteger($value), 2);
    }
}
