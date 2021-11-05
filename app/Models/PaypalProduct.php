<?php

namespace App\Models;

use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Model;
use NumberFormatter;
use Spatie\Activitylog\Traits\LogsActivity;

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
}
