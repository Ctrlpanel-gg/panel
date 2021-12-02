<?php

namespace App\Models;

use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Model;
use NumberFormatter;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Configuration;

class PaypalProduct extends Model
{
    use LogsActivity;
    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string[]
     */
    protected $fillable = [
        "type",
        "price",
        "description",
        "display",
        "currency_code",
        "quantity",
        "disabled",
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (PaypalProduct $paypalProduct) {
            $client = new Client();

            $paypalProduct->{$paypalProduct->getKeyName()} = $client->generateId($size = 21);
        });
    }

    /**
     * @param mixed $value
     * @param string $locale
     *
     * @return float
     */
    public function formatToCurrency($value,$locale = 'en_US')
    {
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($value, $this->currency_code);
    }

    /**
    * @description Returns the tax in % taken from the Configuration
    *
    * @return int
    */
    public function getTaxPercent()
    {
        $tax = Configuration::getValueByKey("SALES_TAX");
        return $tax < 0 ? 0 : $tax;
    }

    /**
    * @description Returns the tax as Number
    *
    * @return float
    */
    public function getTaxValue()
    {
        return number_format($this->price*$this->getTaxPercent()/100,2);
    }

    /**
    * @description Returns the full price of a Product including tax
    *
    * @return float
    */
    public function getTotalPrice()
    {
        return number_format($this->price+$this->getTaxValue(),2);
    }
}
