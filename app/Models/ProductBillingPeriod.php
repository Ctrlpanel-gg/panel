<?php

namespace App\Models;

use App\Enums\BillingPeriod;
use App\Facades\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ProductBillingPeriod extends Model
{
    protected $fillable = [
        'product_id',
        'billing_period',
        'price'
    ];

    protected $casts = [
        'billing_period' => BillingPeriod::class,
    ];

    protected $appends = [
        'period_label',
        'per_period',
        'period_description',
    ];

    /**
     * Set the price to be in cents.
     *
     * @return Attribute
     */
    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Currency::formatForForm($value),
            set: fn ($value) => Currency::prepareForDatabase($value)
        );
    }

    /**
     * @return belongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getPeriodLabelAttribute()
    {
        return $this->billing_period->label();
    }

    public function getPeriodDescriptionAttribute()
    {
        return $this->billing_period->description();
    }

    public function getPerPeriodAttribute()
    {
        return $this->billing_period->perPeriod();
    }
}
