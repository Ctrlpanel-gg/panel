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

    public $incrementing = false;

    protected $fillable = [
        'type',
        'price',
        'description',
        'display',
        'currency_code',
        'quantity',
        'disabled',
    ];

    protected $casts = [
        'price' => 'integer',
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

    /**
     * Get price after applying discount
     * 
     * @return string
     */
    public function getPriceAfterDiscount()
    {
        $discountRate = PartnerDiscount::getDiscount() / 100;
        $price = (int)$this->attributes['price'];
        $discountedPrice = $price * (1 - $discountRate);
        return round($discountedPrice);
    }

    /**
     * Get tax value
     * 
     * @return string
     */
    public function getTaxValue()
    {
        $taxPercent = $this->getTaxPercent();
        $priceAfterDiscount = $this->getPriceAfterDiscount();
        $taxValue = bcmul(bcdiv(bcmul($priceAfterDiscount, $taxPercent, 4), '100', 4), '1', 2);
        return $taxValue;
    }

    /**
     * Get total price including tax
     * 
     * @return string
     */
    public function getTotalPrice()
    {
        return bcadd($this->getPriceAfterDiscount(), $this->getTaxValue(), 2);
    }

    /**
     * Price attribute accessor
     * 
     * @param int $value
     * @return string
     */
    public function getPriceAttribute($value)
    {
        return $this->convertFromInteger($value);
    }

    /**
     * Price attribute mutator
     * 
     * @param mixed $value
     * @return void
     */
    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $this->convertToInteger($value);
    }
}
