<?php

namespace App\Models;

use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Pterodactyl\Egg;
use App\Models\Pterodactyl\Node;

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

    protected function calculatePrice(): int|float {
        return (($this->price * 100) + $this->price_cents) / 100;
    }

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

    public function getHourlyPrice(): float
    {
        $fullPrice = $this->calculatePrice();

        switch ($this->billing_period) {
            case 'daily':
                return $fullPrice / 24;
            case 'weekly':
                return $fullPrice / (24 * 7);
            case 'monthly':
                return $fullPrice / (24 * 30);
            case 'quarterly':
                return $fullPrice / (24 * 30 * 3);
            case 'half-annually':
                return $fullPrice / (24 * 30 * 6);
            case 'annually':
                return $fullPrice / (24 * 365);
            default:
                return $fullPrice;
        }
    }

    public function getMonthlyPrice(): float
    {
        $fullPrice = $this->calculatePrice();

        switch ($this->billing_period) {
            case 'hourly':
                return $fullPrice * 24 * 30;
            case 'daily':
                return $fullPrice * 30;
            case 'weekly':
                return $fullPrice * 4;
            case 'monthly':
                return $fullPrice;
            case 'quarterly':
                return $fullPrice / 3;
            case 'half-annually':
                return $fullPrice / 6;
            case 'annually':
                return $fullPrice / 12;
            default:
                return $fullPrice;
        }
    }

    public function getWeeklyPrice(): float
    {
        $fullPrice = $this->calculatePrice();
        return $fullPrice / 4;
    }

    public function servers(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'id', 'product_id');
    }

    public function eggs(): BelongsToMany
    {
        return $this->belongsToMany(Egg::class);
    }

    public function nodes(): BelongsToMany
    {
        return $this->belongsToMany(Node::class);
    }
}