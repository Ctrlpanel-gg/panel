<?php

namespace App\Models;

use App\Settings\GeneralSettings;
use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Model;
use NumberFormatter;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;

class ShopProduct extends Model
{
    use LogsActivity, CausesActivity;
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string[]
     */
    protected $fillable = [
        'type',
        'price',
        'description',
        'display',
        'currency_code',
        'quantity',
        'disabled',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'price' => 'float'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (ShopProduct $shopProduct) {
            $client = new Client();

            $shopProduct->{$shopProduct->getKeyName()} = $client->generateId($size = 21);
        });
    }

    /**
     * @param  mixed  $value
     * @param  string  $locale
     * @return float
     */
    public function formatToCurrency($value, $locale = 'en_US')
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
        $generalSettings = new GeneralSettings();
        $tax = $generalSettings->sales_tax;

        return $tax < 0 ? 0 : $tax;
    }

    public function getPriceAfterDiscount()
    {
        $discountRate = PartnerDiscount::getDiscount() / 100;
        $discountedPrice = $this->price * (1 - $discountRate);
        return round($discountedPrice, 2);
    }

    /**
     * @description Returns the tax as Number
     *
     * @return float
     */
    public function getTaxValue()
    {
        $taxValue = $this->getPriceAfterDiscount() * $this->getTaxPercent() / 100;
        return round($taxValue, 2);
    }

    /**
     * @description Returns the full price of a Product including tax
     *
     * @return float
     */
    public function getTotalPrice()
    {
        $total = $this->getPriceAfterDiscount() + $this->getTaxValue();
        return round($total, 2);
    }
}
