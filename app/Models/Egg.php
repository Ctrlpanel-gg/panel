<?php

namespace App\Models;

use App\Classes\Pterodactyl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Egg extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $fillable = [
        'id',
        'nest_id',
        'name',
        'description',
        'docker_image',
        'startup',
        'environment',
    ];

    /**
     * @return array
     */
    public function getEnvironmentVariables()
    {
        $array = [];

        foreach (json_decode($this->environment) as $variable) {
            foreach ($variable as $key => $value) {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    public static function syncEggs()
    {

        Nest::all()->each(function (Nest $nest) {
            $eggs = Pterodactyl::getEggs($nest);

            foreach ($eggs as $egg) {
                $array = [];
                $environment = [];

                $array['id'] = $egg['attributes']['id'];
                $array['nest_id'] = $egg['attributes']['nest'];
                $array['name'] = $egg['attributes']['name'];
                $array['description'] = $egg['attributes']['description'];
                $array['docker_image'] = $egg['attributes']['docker_image'];
                $array['startup'] = $egg['attributes']['startup'];

                //get environment variables
                foreach ($egg['attributes']['relationships']['variables']['data'] as $variable) {
                    $environment[$variable['attributes']['env_variable']] = $variable['attributes']['default_value'];
                }

                $array['environment'] = json_encode([$environment]);

                self::firstOrCreate(['id' => $array['id']], $array);
            }

        });
    }

    /**
     * @return BelongsTo
     */
    public function nest()
    {
        return $this->belongsTo(Nest::class, 'id', 'nest_id');
    }

    /**
     * @return BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
