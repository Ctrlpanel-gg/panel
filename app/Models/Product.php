<?php

namespace App\Models;

use App\Enums\BillingPeriod;
use App\Enums\BillingPriority;
use App\Facades\Currency;
use App\Models\Pterodactyl\Egg;
use App\Models\Pterodactyl\Node;
use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use HasFactory;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    public $incrementing = false;

    protected $guarded = ['id'];

    /**
     * @var string[]
     */
    protected $appends = [
        'display_price',
        'display_minimum_credits',
        'default_billing_priority_label',
        //'default_billing_period_label',
    ];

    protected $casts = [
        'default_billing_priority' => BillingPriority::class,
        //'default_billing_period' => BillingPeriod::class,
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (Product $product) {
            $client = new Client();

            $product->{$product->getKeyName()} = $client->generateId($size = 21);
        });

        static::deleting(function (Product $product) {
            $product->nodes()->detach();
            $product->eggs()->detach();
        });
    }

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
     * Set the minimum credits to be in cents.
     *
     * @return Attribute
     */
    protected function minimumCredits(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ? Currency::prepareForDatabase($value) : null
        );
    }

    /**
     * Get the display price formatted.
     *
     * @return string
     */
    public function getDisplayPriceAttribute()
    {
        return Currency::formatForDisplay($this->price);
    }

    /**
     * Get the display minimum credits formatted.
     *
     * @return string|null
     */
    public function getDisplayMinimumCreditsAttribute()
    {
        return $this->minimum_credits ? Currency::formatForDisplay($this->minimum_credits) : null;
    }

    public function getDefaultBillingPriorityLabelAttribute()
    {
        return $this->default_billing_priority->label();
    }

    // public function getDefaultBillingPeriodLabelAttribute()
    // {
    //     return $this->default_billing_period->label();
    // }

    public function getWeeklyPrice()
    {
        return $this->price / 4;
    }

    /**
    * @return hasMany
    */
    public function billingPeriods()
    {
        return $this->hasMany(ProductBillingPeriod::class);
    }

    /**
     * @return hasMany
     */
    public function servers()
    {
        return $this->hasMany(Server::class, 'product_id', 'id');
    }

    /**
     * @return BelongsToMany
     */
    public function eggs()
    {
        return $this->belongsToMany(Egg::class);
    }

    /**
     * @return BelongsToMany
     */
    public function nodes()
    {
        return $this->belongsToMany(Node::class);
    }
}
