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

    public function formatToCurrency($value, $locale = 'en_US')
    {
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($value, $this->currency_code);
    }

    public function getTaxPercent()
    {
        $generalSettings = new GeneralSettings();
        $tax = $generalSettings->sales_tax;

        return $tax < 0 ? 0 : $tax;
    }

    public function getPriceAfterDiscount()
    {
        $discountRate = PartnerDiscount::getDiscount() / 100;
        $price = (int)$this->attributes['price'];
        $discountedPrice = $price * (1 - $discountRate);
        return round($discountedPrice);
    }

    public function getTaxValue()
    {
        $taxPercent = $this->getTaxPercent();
        $priceAfterDiscount = $this->getPriceAfterDiscount();
        $taxValue = bcmul(bcdiv(bcmul($priceAfterDiscount, $taxPercent, 4), '100', 4), '1', 2);
        return $taxValue;
    }

    public function getTotalPrice()
    {
        return bcadd($this->getPriceAfterDiscount(), $this->getTaxValue(), 2);
    }

    public function getPriceAttribute($value)
    {
        return $this->convertFromInteger($value);
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $this->convertToInteger($value);
    }
}
