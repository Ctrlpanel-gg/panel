<?php

namespace App\Models;

use App\Facades\Currency;
use App\Settings\GeneralSettings;
use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
     * Set the price to be in cents.
     *
     * @return Attribute
     */
    protected function price(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Currency::prepareForDatabase($value)
        );
    }

    /**
     * Set the quantity to be in cents if the type is Credits.
     *
     * @return Attribute
     */
    protected function quantity(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $this->type === 'Credits'
                ? Currency::prepareForDatabase($value)
                : $value
        );
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function (ShopProduct $shopProduct) {
            $client = new Client();

            $shopProduct->{$shopProduct->getKeyName()} = $client->generateId($size = 21);
        });
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

        return $discountedPrice;
    }

    /**
     * @description Returns the tax as Number
     *
     * @return float
     */
    public function getTaxValue()
    {
        return $this->getPriceAfterDiscount() * $this->getTaxPercent() / 100;
    }

    /**
     * @description Returns the full price of a Product including tax
     *
     * @return float
     */
    public function getTotalPrice()
    {
        return $this->getPriceAfterDiscount() + $this->getTaxValue();
    }
}
