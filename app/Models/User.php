<?php

namespace App\Models;

use App\Classes\Pterodactyl;
use App\Notifications\Auth\QueuedVerifyEmail;
use App\Notifications\WelcomeMessage;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, LogsActivity, CausesActivity;

    protected static $logAttributes = ['name', 'email'];

    protected static $ignoreChangedAttributes = [
        'remember_token',
        'credits',
        'updated_at',
        'server_limit',
        'last_seen',
        'ip',
        'pterodactyl_id'
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
        'avatar'
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
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function (User $user) {
            $user->notify(new WelcomeMessage($user));
        });

        static::deleting(function (User $user) {
            $user->servers()->chunk(10 , function ($servers) {
                foreach ($servers as $server) {
                    $server->delete();
                }
            });

            $user->payments()->chunk(10 , function ($payments) {
                foreach ($payments as $payment) {
                    $payment->delete();
                }
            });

            Pterodactyl::client()->delete("/application/users/{$user->pterodactyl_id}");
        });
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new QueuedVerifyEmail);
    }

    public function credits()
    {
        return number_format($this->credits, 2, '.', '');
    }

    public function getAvatar(){
        return "https://www.gravatar.com/avatar/" . md5(strtolower(trim($this->email)));
    }

    public function creditUsage()
    {
        $usage = 0;

        foreach ($this->Servers as $server){
            $usage += $server->product->price;
        }

        return number_format($usage, 2, '.', '');
    }

    public function getVerifiedStatus(){
        $status = '';
        if ($this->hasVerifiedEmail()) $status .= 'email ';
        if ($this->discordUser()->exists()) $status .= 'discord';
        $status = str_replace(' ' , '/' , $status);
        return $status;
    }

    public function discordUser(){
        return $this->hasOne(DiscordUser::class);
    }

    public function servers()
    {
        return $this->hasMany(Server::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

}
