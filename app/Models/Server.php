<?php

namespace App\Models;

use App\Classes\Pterodactyl;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Client\Response;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Server
 * @package App\Models
 */
class Server extends Model
{
    use HasFactory;
    use LogsActivity;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var string[]
     */
    protected static $ignoreChangedAttributes = ['pterodactyl_id', 'identifier', 'updated_at'];

    /**
     * @var string[]
     */
    protected static $logAttributes = ['name', 'description'];

    /**
     * @var string[]
     */
    protected $fillable = [
        "name",
        "description",
        "suspended",
        "identifier",
        "product_id",
        "pterodactyl_id",
    ];

    /**
     * @var string[]
     */
    protected $dates = [
        'suspended'
    ];

    /**
     *
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function (Server $server) {
            $client = new Client();

            $server->{$server->getKeyName()} = $client->generateId($size = 21);
        });

        static::deleting(function (Server $server) {
            $response = Pterodactyl::client()->delete("/application/servers/{$server->pterodactyl_id}");
            if ($response->failed() && !is_null($server->pterodactyl_id)) throw new Exception($response['errors'][0]['code']);
        });
    }

    /**
     * @return bool
     */
    public function isSuspended()
    {
        return !is_null($this->suspended);
    }


    /**
     * @return PromiseInterface|Response
     */
    public function getPterodactylServer()
    {
        return Pterodactyl::client()->get("/application/servers/{$this->pterodactyl_id}");
    }

    /**
     *
     * @throws Exception
     */
    public function suspend()
    {
        $response = Pterodactyl::suspendServer($this);

        if ($response->successful()) {
            $this->update([
                'suspended' => now()
            ]);
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    public function unSuspend()
    {
        $response = Pterodactyl::unSuspendServer($this);

        if ($response->successful()) {
            $this->update([
                'suspended' => null
            ]);
        }

        return $this;
    }


    /**
     * @return HasOne
     */
    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
