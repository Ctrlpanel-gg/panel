<?php

namespace App\Models;

use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use HasFactory, LogsActivity;

    public $incrementing = false;

    protected $guarded = ['id'];

    public static function boot() {
        parent::boot();

        static::creating(function(Product $product) {
            $client = new Client();

            $product->{$product->getKeyName()} = $client->generateId($size = 21);
        });
    }

    /**
     * @return BelongsTo
     */
    public function servers(): BelongsTo
    {
        return $this->belongsTo(Server::class , 'id' , 'product_id');
    }
}
