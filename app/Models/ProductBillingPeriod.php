<?php

namespace App\Models;

use App\Enums\BillingPeriod;
use Illuminate\Database\Eloquent\Model;

class ProductBillingPeriod extends Model
{
    protected $fillable = [
        'product_id',
        'billing_period',
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
