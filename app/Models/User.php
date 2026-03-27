<?php

namespace App\Models;

use App\Notifications\Auth\QueuedVerifyEmail;
use App\Classes\PterodactylClient;
use App\Facades\Currency;
use App\Settings\PterodactylSettings;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, LogsActivity, CausesActivity, HasRoles;

    private ?PterodactylClient $pterodactyl = null;

    /**
     * @var string[]
     */
    protected static $logAttributes = ['name'];

    /**
     * @var string[]
     */
    protected static $ignoreChangedAttributes = [
        'remember_token',
        'credits',
        'updated_at',
        'server_limit',
        'last_seen',
        'ip',
        'pterodactyl_id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'ip',
        'mac',
        'last_seen',
        'role', //discontinued in 1.0.7
        'credits',
        'email',
        'server_limit',
        'password',
        'pterodactyl_id',
        'discord_verified_at',
        'avatar',
        'suspended',
        'referral_code',
        'email_verified_reward',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen' => 'datetime',
        'server_limit' => 'integer',
        'email_verified_reward' => 'boolean'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function (User $user) {
            DB::transaction(function () use ($user) {
                foreach ($user->servers()->cursor() as $server) {
                    $server->delete();
                }

                $user->payments()->delete();
                $user->tickets()->delete();
                $user->ticketBlackList()->delete();
                $user->vouchers()->detach();
                $user->discordUser()->delete();

                $referralRecords = DB::table('user_referrals')->where('registered_user_id', $user->id)->get();
                foreach ($referralRecords as $ref) {
                    DB::table('user_referrals')
                        ->where('referral_id', $ref->referral_id)
                        ->where('registered_user_id', $ref->registered_user_id)
                        ->update([
                            'deleted_at' => now(),
                            'deleted_username' => $user->name,
                            'deleted_user_id' => $user->id,
                        ]);
                }

                if ($user->pterodactyl_id) {
                    $response = $user->pterodactyl()->application->delete("/application/users/{$user->pterodactyl_id}");
                    $status = (string) data_get($response->json(), 'errors.0.status', '');
                    if ($response->failed() && $status !== '404') {
                        throw new \RuntimeException(
                            (string) data_get($response->json(), 'errors.0.detail', $response->body())
                        );
                    }
                }
            });
        });
    }

    private function pterodactyl(): PterodactylClient
    {
        if ($this->pterodactyl === null) {
            $this->pterodactyl = new PterodactylClient(app(PterodactylSettings::class));
        }

        return $this->pterodactyl;
    }

    /**
     * Set the credits to be in cents.
     *
     * @return Attribute
     */
    protected function credits(): Attribute
    {
        return Attribute::make(
            // We only convert when the user already exists, to avoid 2 conversions.
            set: fn ($value) => $this->exists ? Currency::prepareForDatabase($value) : $value,
        );
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')
            ->orderBy('created_at', 'desc');
    }

    /**
     * @return HasMany
     */
    public function servers()
    {
        return $this->hasMany(Server::class);
    }

    /**
     * @return HasMany
     */
    public function activeServers()
    {
        return $this->servers()
            ->whereNull('canceled')
            ->whereNull('suspended');
    }

    /**
     * @return HasMany
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasMany
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * @return HasMany
     */
    public function ticketBlackList()
    {
        return $this->hasMany(TicketBlacklist::class);
    }

    /**
     * @return BelongsToMany
     */
    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class);
    }

    /**
     * @return BelongsToMany
     */
    public function coupons()
    {
        return $this->belongsToMany(Coupon::class, 'user_coupons');
    }

    /**
     * @return HasOne
     */
    public function discordUser()
    {
        return $this->hasOne(DiscordUser::class);
    }

    public function sendEmailVerificationNotification(): bool
    {
        try {
            $rateLimitKey = 'verify-mail:' . $this->id;

            // Rate limit the email verification notification to 5 attempt per 30 minutes
            $executed = RateLimiter::attempt(
                key: $rateLimitKey,
                maxAttempts: 5,
                callback: function () {
                    $this->notify(new QueuedVerifyEmail);
                },
                decaySeconds: 1800
            );

            if (!$executed) {
                return false;
            }

            return true;
        }catch (\Exception $exception){
            Log::error($exception->getMessage());

            return false;
        }
    }

    /**
     * @return bool
     */
    public function isSuspended()
    {
        return $this->suspended;
    }

    public function suspend()
    {
        foreach ($this->servers as $server) {
            $server->suspend();
        }

        $this->update([
            'suspended' => true,
        ]);

        return $this;
    }

    public function unSuspend()
    {
        $availableCredits = $this->credits;

        foreach ($this->getSuspendedServersWithProduct() as $server) {
            $hourlyPrice = $server->product->getHourlyPrice();
            if ($availableCredits >= $hourlyPrice) {
                $server->unSuspend();
                $availableCredits -= $hourlyPrice;
            }
        }

        $this->update([
            'suspended' => $this->servers()->whereNotNull('suspended')->exists(),
        ]);

        return $this;
    }


    /**
     * @return string
     */
    public function getAvatar()
    {
        if (! empty($this->avatar)) {
            return $this->avatar;
        }

        return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->email)));
    }

    public function creditUsage()
    {
        $usage = 0;

        foreach ($this->getServersWithProduct() as $server) {
            $usage += $server->product->getMonthlyPrice();
        }

        return $usage;
    }

    public function getServersWithProduct()
    {
        return $this->servers()
            ->whereNull('suspended')
            ->whereNull('canceled')
            ->with('product')
            ->get();
    }

    public function getSuspendedServersWithProduct()
    {
        return $this->servers()
            ->whereNotNull('suspended')
            ->whereNull('canceled')
            ->with('product')
            ->get();
    }

    /**
     * @return array|string|string[]
     */
    public function getVerifiedStatus()
    {
        $status = '';
        if ($this->hasVerifiedEmail()) {
            $status .= 'email ';
        }
        if ($this->discordUser()->exists()) {
            $status .= 'discord';
        }
        $status = str_replace(' ', '/', $status);

        return $status;
    }

    public function verifyEmail()
    {
        $this->forceFill([
            'email_verified_at' => now()
        ])->save();
    }

    public function reVerifyEmail()
    {
        $this->forceFill([
            'email_verified_at' => null
        ])->save();
    }

    public function referredBy()
    {
        $referee = DB::table('user_referrals')->where("registered_user_id", $this->id)->first();

        if ($referee && $referee->referral_id) {
            return User::find($referee->referral_id);
        }

        return null;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['role', 'name', 'server_limit', 'pterodactyl_id', 'credits', 'server_limit', 'suspended', 'referral_code'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly(['credits', 'server_limit', 'updated_at']);
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $properties = $activity->properties?->toArray() ?? [];

        foreach (['attributes', 'old'] as $section) {
            if (! isset($properties[$section]) || ! is_array($properties[$section])) {
                continue;
            }

            if (array_key_exists('email', $properties[$section])) {
                $properties[$section]['email'] = '[redacted]';
            }
        }

        $activity->properties = $properties;
    }
}
