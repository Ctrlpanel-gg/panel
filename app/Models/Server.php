<?php

namespace App\Models;

use Carbon\Carbon;
use App\Classes\PterodactylClient;
use App\Enums\BillingPeriod;
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
        "billing_period",
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
        'billing_priority' => BillingPriority::class,
        'billing_period' => BillingPeriod::class,
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

    public function getBillingPeriodAttribute($value)
    {
        return $value ? $value : $this->product->default_billing_period;
    }

    public function getHourlyPrice()
    {
        return match($this->billing_period) {
            BillingPeriod::DAILY => $this->product->price / 24,
            BillingPeriod::WEEKLY => $this->product->price / 24 / 7,
            BillingPeriod::MONTHLY => $this->product->price / 24 / 30,
            BillingPeriod::QUARTERLY => $this->product->price / 24 / 30 / 3,
            BillingPeriod::HALF_ANNUALLY => $this->product->price / 24 / 30 / 6,
            BillingPeriod::ANNUALLY => $this->product->price / 24 / 365,
            default => $this->product->price,
        };
    }

    public function getMonthlyPrice()
    {
        return match($this->billing_period) {
            BillingPeriod::HOURLY => $this->product->price * 24 * 30,
            BillingPeriod::DAILY => $this->product->price * 30,
            BillingPeriod::WEEKLY => $this->product->price * 4,
            BillingPeriod::MONTHLY => $this->product->price,
            BillingPeriod::QUARTERLY => $this->product->price / 3,
            BillingPeriod::HALF_ANNUALLY => $this->product->price / 6,
            BillingPeriod::ANNUALLY => $this->product->price / 12,
            default => $this->product->price,
        };
    }

    public function getNextBillingDate()
    {
        return match($this->billing_period) {
            BillingPeriod::ANNUALLY => Carbon::parse($this->last_billed)->addYear(),
            BillingPeriod::HALF_ANNUALLY => Carbon::parse($this->last_billed)->addMonths(6),
            BillingPeriod::QUARTERLY => Carbon::parse($this->last_billed)->addMonths(3),
            BillingPeriod::MONTHLY => Carbon::parse($this->last_billed)->addMonth(),
            BillingPeriod::WEEKLY => Carbon::parse($this->last_billed)->addWeek(),
            BillingPeriod::DAILY => Carbon::parse($this->last_billed)->addDay(),
            BillingPeriod::HOURLY => Carbon::parse($this->last_billed)->addHour(),
            default => null,
        };
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
