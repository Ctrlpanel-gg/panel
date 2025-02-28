<?php

namespace App\Models;

use App\Settings\GeneralSettings;
use App\Traits\HandlesMoneyFields;
use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Model;
use NumberFormatter;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;

class ShopProduct extends Model
{
    use LogsActivity, CausesActivity, HandlesMoneyFields;
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
        'price' => 'integer' 
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
        $originalPrice = $this->attributes['price'];
        $discountedPrice = (int)bcmul($originalPrice, bcsub(1, $discountRate, 4), 0);
        return $discountedPrice;
    }

    /**
     * @description Returns the tax as Number
     *
     * @return float
     */
    public function getTaxValue()
    {
        $taxPercent = $this->getTaxPercent();
        $priceAfterDiscount = $this->getPriceAfterDiscount();
        $taxValue = bcmul(
            bcdiv(bcmul($priceAfterDiscount, $taxPercent, 4), '100', 4),
            '1',
            2
        );
        
        return (int)$taxValue;
    }

    /**
     * @description Returns the full price of a Product including tax
     *
     * @return float
     */
    public function getTotalPrice()
    {
        return bcadd($this->getPriceAfterDiscount(), $this->getTaxValue(), 2);
    }

    /**
     * @description Get the Formatted price attribute.
     *
     * @return float
     */
    public function getPriceAttribute($value)
    {
        return $this->convertFromInteger($value);
    }

    /**
     * @description Set the price attribute.
     * 
     * @return int
     */
    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $this->convertToInteger($value);
    }
}
