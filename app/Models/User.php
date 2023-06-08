<?php

namespace App\Models;

use App\Notifications\Auth\QueuedVerifyEmail;
use App\Notifications\WelcomeMessage;
use App\Classes\PterodactylClient;
use App\Settings\PterodactylSettings;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, LogsActivity, CausesActivity, HasRoles;

    private PterodactylClient $pterodactyl;

    /**
     * @var string[]
     */
    protected static $logAttributes = ['name', 'email'];

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
        'role',
        'credits',
        'email',
        'server_limit',
        'password',
        'pterodactyl_id',
        'discord_verified_at',
        'avatar',
        'suspended',
        'referral_code',
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
        'credits' => 'float',
        'server_limit' => 'float',
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

        static::created(function (User $user) {
            $user->notify(new WelcomeMessage($user));
        });

        static::deleting(function (User $user) {


            // delete every server the user owns without using chunks
            $user->servers()->each(function ($server) {
                $server->delete();
            });

            $user->payments()->delete();

            $user->tickets()->delete();

            $user->ticketBlackList()->delete();

            $user->vouchers()->detach();

            $user->discordUser()->delete();

            $user->pterodactyl->application->delete("/application/users/{$user->pterodactyl_id}");
        });
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

    public function sendEmailVerificationNotification()
    {
        $this->notify(new QueuedVerifyEmail);
    }

    /**
     * @return string
     */
    public function credits()
    {
        return number_format($this->credits, 2, '.', '');
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
        foreach ($this->getServersWithProduct() as $server) {
            if ($this->credits >= $server->product->getHourlyPrice()) {
                $server->unSuspend();
            }
        }

        $this->update([
            'suspended' => false,
        ]);

        return $this;
    }


    /**
     * @return string
     */
    public function getAvatar()
    {
        return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->email)));
    }

    public function creditUsage()
    {
        $usage = 0;
        foreach ($this->getServersWithProduct() as $server) {
            $usage += $server->product->getHourlyPrice() * 24 * 30;
        }

        return number_format($usage, 2, '.', '');
    }

    private function getServersWithProduct()
    {
        return $this->servers()
            ->whereNull('suspended')
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
            'email_verified_at' => now(),
        ])->save();
    }

    public function reVerifyEmail()
    {
        $this->forceFill([
            'email_verified_at' => null,
        ])->save();
    }

    public function referredBy()
    {
        $referee = DB::table('user_referrals')->where("registered_user_id", $this->id)->first();

        if ($referee) {
            $referee = User::where("id", $referee->referral_id)->firstOrFail();
            return $referee;
        }
        return Null;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['role', 'name', 'server_limit', 'pterodactyl_id', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
