<?php

namespace App\Traits;

use App\Models\User;

trait HandlesMoneyFields
{
    /**
     * Convert a decimal value to integer for storage
     * 
     * @param mixed $amount The decimal amount to convert
     * @param int $precision The number of decimal places to preserve
     * @return int
     */
    protected function convertToInteger($amount, $precision = 2)
    {
        if ($this instanceof User || property_exists($this, 'minimum_credits')) {
            $precision = 4;
        }
        return (int) bcmul($amount, bcpow(10, $precision), 0);
    }

    /**
     * Convert an integer value back to decimal for display
     * 
     * @param int $amount The integer amount to convert
     * @param int $precision The number of decimal places to display
     * @return string
     */
    protected function convertFromInteger($amount, $precision = 2) 
    {
        if ($this instanceof User || property_exists($this, 'minimum_credits')) {
            $precision = 4;
        }
        return bcdiv($amount, bcpow(10, $precision), $precision);
    }

    /**
     * Format a monetary value to currency string
     * 
     * @param int $value The integer monetary value
     * @return string
     */
    public function formatToCurrency($value)
    {
        $currencyCode = property_exists($this, 'currency_code') ? $this->currency_code : '€';
        return $currencyCode . number_format($this->convertFromInteger($value), 2);
    }
}
