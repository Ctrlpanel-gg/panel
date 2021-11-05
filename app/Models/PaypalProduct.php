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
     * @param string $locale
     * @return string
     */
    public function formatToCurrency($value,$locale = 'de_DE')
    {
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($value, $this->currency_code);
    }

    public function getTaxPercent(){
        $tax = Configuration::getValueByKey("SALES_TAX");
        if ( $tax < 0 ) {
            return 0;
        }
        return $tax;
    }

    public function getTaxValue(){
        return $this->price*$this->getTaxPercent()/100;
    }

    public function getTotalPrice(){
        return $this->price+($this->getTaxValue());
    }
}
