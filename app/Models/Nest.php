<?php

namespace App\Models;

use App\Classes\Pterodactyl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nest extends Model
{
    use HasFactory;

    public $incrementing = false;

    public $fillable = [
        'id',
        'name',
        'description',
        'disabled',
    ];


    public function eggs(){
        return $this->hasMany(Egg::class);
    }

    public static function syncNests(){
        self::query()->delete();
        $nests = Pterodactyl::getNests();

        foreach ($nests as $nest) {
            self::firstOrCreate(['id' => $nest['attributes']['id']] , array_merge($nest['attributes'] , ['disabled' => '1']));
        }
    }
}
