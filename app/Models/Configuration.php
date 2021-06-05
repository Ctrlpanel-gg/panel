<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    use HasFactory;

    public $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    /**
     * @param string $key
     * @param $default
     * @return mixed
     */
    public static function getValueByKey(string $key , $default = null)
    {
        $configuration = self::find($key);
        return $configuration ? $configuration->value : $default;
    }
}
