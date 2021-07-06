<?php

namespace App\Models;

use App\Classes\Pterodactyl;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Node extends Model
{
    use HasFactory;

    public $incrementing = false;

    public $guarded = [];


    /**
     * @return BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @throws Exception
     */
    public static function syncNodes(){
        Location::syncLocations();
        $nodes = Pterodactyl::getNodes();


        $nodes = array_map(function($node) {
            return array(
                'id' => $node['attributes']['id'],
                'location_id' => $node['attributes']['location_id'],
                'name' => $node['attributes']['name'],
                'description' => $node['attributes']['description'],
                'disabled' => '1'
            );
        }, $nodes);

        foreach ($nodes as $node) {
            self::firstOrCreate(['id' => $node['id']] , $node);
        }

    }

    /**
     * @return BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
