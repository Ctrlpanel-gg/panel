<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Configuration extends Model
{
    use HasFactory;

    public const CACHE_TAG = 'configuration';

    public $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    public static function boot()
    {
        parent::boot();

        static::updated(function (Configuration $configuration) {
            Cache::forget(self::CACHE_TAG .':'. $configuration->key);
        });
    }

    /**
     * @param string $key
     * @param $default
     * @return mixed
     */
    public static function getValueByKey(string $key, $default = null)
    {
        return Cache::rememberForever(self::CACHE_TAG .':'. $key, function () use ($default, $key) {
            $configuration = self::find($key);
            return $configuration ? $configuration->value : $default;
        });
    }
}
