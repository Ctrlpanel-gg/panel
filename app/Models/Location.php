<?php

namespace App\Models;

use App\Classes\Pterodactyl;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    public $incrementing = false;

    public $guarded = [];


    public function nodes(){
        return $this->hasMany(Node::class , 'location_id' , 'id');
    }

    /**
     * Sync locations with pterodactyl panel
     * @throws Exception
     */
    public static function syncLocations(){
        $locations = Pterodactyl::getLocations();

        $locations = array_map(function($val) {
            return array(
                'id' => $val['attributes']['id'],
                'name' => $val['attributes']['short'],
                'description' => $val['attributes']['long']
            );
        }, $locations);

        foreach ($locations as $location) {
            self::firstOrCreate(['id' => $location['id']] , $location);
        }
    }
}
