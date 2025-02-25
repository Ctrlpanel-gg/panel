<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Traits\HandlesMoneyFields;
use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NumberFormatter;

class Payment extends Model
{
    use HasFactory, HandlesMoneyFields;

    public $incrementing = false;
    protected $primaryKey = 'id';

    /**
     * @var string[]
     */
    protected $fillable = [
        'id',
        'user_id',
        'payment_id',
        'payment_method',
        'status',
        'type',
        'amount',
        'price',
        'tax_value',
        'total_price',
        'tax_percent',
        'currency_code',
        'shop_item_product_id',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'status' => PaymentStatus::class
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (Payment $payment) {
            $client = new Client();

            $payment->{$payment->getKeyName()} = $client->generateId($size = 8);
        });
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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

    public function getPriceAttribute($value)
    {
        return $this->convertFromInteger($value);
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $this->convertToInteger($value);
    }

    public function getTaxValueAttribute($value)
    {
        return $this->convertFromInteger($value);
    }

    public function setTaxValueAttribute($value)
    {
        $this->attributes['tax_value'] = $this->convertToInteger($value);
    }

    public function getTotalPriceAttribute($value)
    {
        return $this->convertFromInteger($value);
    }

    public function setTotalPriceAttribute($value)
    {
        $this->attributes['total_price'] = $this->convertToInteger($value);
    }
}
