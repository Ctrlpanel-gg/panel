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
     * @param  float/int
     * @param string $locale
     * @return string
     */
    public function formatToCurrency($value,$locale = 'en_US')
    {
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($value, $this->currency_code);
    }

    /**
     * @desc Returns the tax in % or 0 if less than 0
     * @return int
     */
    public function getTaxPercent()
    {
        $tax = Configuration::getValueByKey("SALES_TAX");
        return $tax < 0 ? 0 : $tax;
    }

    /**
     * @desc Returns the total tax value.
     * @return float
     */
    public function getTaxValue()
    {
        return $this->price*$this->getTaxPercent()/100;
    }

    /**
     * @desc Returns the total price incl. tax
     * @return float
     */
    public function getTotalPrice() 
    {
        return $this->price+($this->getTaxValue());
    }
}
