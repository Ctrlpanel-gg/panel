<?php

namespace App\Models;

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

    public function getHourlyPrice()
    {
        // calculate the hourly price with the billing period
        switch($this->billing_period) {
            case 'daily':
                return $this->price / 24;
            case 'weekly':
                return $this->price / 24 / 7;
            case 'monthly':
                return $this->price / 24 / 30;
            case 'quarterly':
                return $this->price / 24 / 30 / 3;
            case 'half-annually':
                return $this->price / 24 / 30 / 6;
            case 'annually':
                return $this->price / 24 / 365;
            default:
                return $this->price;
        }
    }

    public function getMonthlyPrice()
    {
        // calculate the hourly price with the billing period
        switch($this->billing_period) {
            case 'hourly':
                return $this->price * 24 * 30;
            case 'daily':
                return $this->price * 30;
            case 'weekly':
                return $this->price * 4;
            case 'monthly':
                return $this->price;
            case 'quarterly':
                return $this->price / 3;
            case 'half-annually':
                return $this->price / 6;
            case 'annually':
                return $this->price / 12;
            default:
                return $this->price;
        }
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

    public function getWeeklyPrice()
    {
        return $this->price / 4;
    }

    /**
     * @return BelongsTo
     */
    public function servers()
    {
        return $this->belongsTo(Server::class, 'id', 'product_id');
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
