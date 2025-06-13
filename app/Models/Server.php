<?php

namespace App\Models;

use Carbon\Carbon;
use App\Classes\PterodactylClient;
use App\Settings\PterodactylSettings;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Client\Response;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Server
 */
class Server extends Model
{
    use HasFactory;
    use LogsActivity;

    private PterodactylClient $pterodactyl;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

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
        "last_billed",
        "canceled"
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'suspended' => 'datetime',
    ];

    public function __construct()
    {
        parent::__construct();

        $ptero_settings = new PterodactylSettings();
        $this->pterodactyl = new PterodactylClient($ptero_settings);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function (Server $server) {
            $client = new Client();

            $server->{$server->getKeyName()} = $client->generateId($size = 21);
        });

        static::deleting(function (Server $server) {
            $response = $server->pterodactyl->application->delete("/application/servers/{$server->pterodactyl_id}");
            if ($response->failed() && !is_null($server->pterodactyl_id)) {
                //only return error when it's not a 404 error
                if ($response['errors'][0]['status'] != '404') {
                    throw new Exception($response['errors'][0]['code']);
                }
            }
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
        return $this->pterodactyl->application->get("/application/servers/{$this->pterodactyl_id}");
    }

    /**
     * @throws Exception
     */
    public function suspend()
    {
        $response = $this->pterodactyl->suspendServer($this);

        if ($response->successful()) {
            $this->update([
                'suspended' => now(),
            ]);
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    public function unSuspend()
    {
        $response = $this->pterodactyl->unSuspendServer($this);

        if ($response->successful()) {
            $this->update([
                'suspended' => null,
                'last_billed' => Carbon::now()->toDateTimeString(),
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
