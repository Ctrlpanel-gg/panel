<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\HandlesMoneyFields;

/**
 * Class Voucher
 */
class Voucher extends Model
{
    use HasFactory, LogsActivity, CausesActivity, HandlesMoneyFields;
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    /**
     * @var string[]
     */
    protected $fillable = [
        'memo',
        'code',
        'credits',
        'uses',
        'expires_at',
        'max_uses'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'credits' => 'integer',
        'uses' => 'integer',
        'max_uses' => 'integer'
    ];

    protected $appends = ['used', 'status'];

    /**
     * @return int
     */
    public function getUsedAttribute()
    {
        return $this->users()->count();
    }

    /**
     * @return string
     */
    public function getStatusAttribute()
    {
        return $this->getStatus();
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function (Voucher $voucher) {
            $voucher->users()->detach();
        });
    }

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        if ($this->users()->count() >= $this->uses) {
            return 'USES_LIMIT_REACHED';
        }
        if (! is_null($this->expires_at)) {
            if ($this->expires_at->isPast()) {
                return __('EXPIRED');
            }
        }

        return __('VALID');
    }

    /**
     * @param  User  $user
     * @return float
     *
     * @throws Exception
     */
    public function redeem(User $user)
    {
        try {
            $user->increment('credits', $this->credits);
            $this->users()->attach($user);
            $this->logRedeem($user);
        } catch (Exception $exception) {
            throw $exception;
        }

        return $this->credits;
    }

    /**
     * @param  User  $user
     * @return null
     */
    private function logRedeem(User $user)
    {
        activity()
            ->performedOn($this)
            ->causedBy($user)
            ->log('redeemed');

        return null;
    }

    public function getCreditsAttribute($value)
    {
        return $this->convertFromInteger($value, 4);
    }

    public function setCreditsAttribute($value)
    {
        $this->attributes['credits'] = $this->convertToInteger($value, 4);
    }
}
