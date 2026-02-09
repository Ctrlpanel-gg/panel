<?php

namespace App\Models;

use Carbon\Carbon;
use App\Classes\PterodactylClient;
use App\Enums\BillingPriority;
use App\Settings\PterodactylSettings;
use GuzzleHttp\Promise\PromiseInterface;
use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Client\Response;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Exception;

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

    // tap into activity log to attach API metadata and add pseudo-attributes for UI
    public function tapActivity(
        \Spatie\Activitylog\Models\Activity $activity,
        string $eventName
    ) {
        $propertiesArray = $activity->properties->toArray();
        $properties = collect($propertiesArray);

        $request = request();
        $apiMemo = $request->attributes->get('application_api_memo');
        $reason = $request->input('reason');

        // Attach top-level reason/lines and via/api_memo for updated/deleted events
        if (in_array($eventName, ['updated', 'deleted'])) {
            if (!empty($reason)) {
                $properties->put('reason', $reason);
                $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $reason))));
                if (!empty($lines)) {
                    $properties->put('reason_lines', $lines);
                }
            }

            if ($apiMemo) {
                $properties->put('via', 'api');
                $properties->put('api_memo', $apiMemo);
            } elseif ($request->is('api/*')) {
                $properties->put('via', 'api');
            } else {
                $properties->put('via', 'web');
            }
        }

        // If an update toggled `suspended`, add pseudo-attributes so the UI shows Reason/Via/API Memo in the same "Updated" block
        if ($eventName === 'updated') {
            $attrs = $properties->get('attributes', []);
            if (is_array($attrs) && array_key_exists('suspended', $attrs)) {
                $olds = $properties->get('old', []);

                if (!empty($reason)) {
                    $attrs['Reason'] = $reason;
                    $olds['Reason'] = null;
                }

                if ($apiMemo) {
                    $attrs['Via'] = 'api';
                    $olds['Via'] = null;

                    $attrs['API Memo'] = $apiMemo;
                    $olds['API Memo'] = null;
                } else {
                    // show that it was done via web for non-API requests
                    $attrs['Via'] = 'web';
                    $olds['Via'] = null;
                }

                $properties->put('attributes', $attrs);
                $properties->put('old', $olds);

                // Set causer to authenticated user if present; do NOT attach ApplicationApi or token
                if (auth()->check()) {
                    $activity->causer_id = auth()->id();
                    $activity->causer_type = auth()->user()::class;
                }

                $activity->properties = $properties->toArray();
                return;
            }
        }

        // Default: write back any modified properties
        $activity->properties = $properties->toArray();
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
        "billing_priority",
        "product_id",
        "pterodactyl_id",
        "user_id",
        "last_billed",
        "canceled"
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'suspended' => 'datetime',
        'last_billed' => 'datetime',
        'canceled' => 'datetime',
        'billing_priority' => BillingPriority::class
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
     * @return BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getEffectiveBillingPriorityAttribute()
    {
        return $this->billing_priority ?? $this->product->default_billing_priority;
    }

    public function scopeByBillingPriority($query)
    {
        return $query->orderByRaw('COALESCE(servers.billing_priority, (
                SELECT default_billing_priority
                FROM products
                WHERE products.id = servers.product_id
            ))')
            ->orderBy('created_at', 'asc');
    }
}
