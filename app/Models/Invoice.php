<?php

namespace App\Models;

use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_name',
        'invoice_user',
        'payment_id'
    ];

}
