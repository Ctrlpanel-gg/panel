<?php

namespace App\Traits;

trait HandlesMoneyFields
{
    protected function convertToInteger($amount, $precision = 2)
    {
        // For credits, use precision 4
        if ($this instanceof User || property_exists($this, 'minimum_credits')) {
            $precision = 4;
        }
        return (int) round($amount * pow(10, $precision));
    }

    protected function convertFromInteger($amount, $precision = 2) 
    {
        // For credits, use precision 4
        if ($this instanceof User || property_exists($this, 'minimum_credits')) {
            $precision = 4;
        }
        return $amount / pow(10, $precision);
    }

    public function formatToCurrency($value)
    {
        return '€' . number_format($this->convertFromInteger($value), 2);
    }
}
