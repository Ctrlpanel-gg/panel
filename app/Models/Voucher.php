<?php

namespace App\Models;

use App\Facades\Currency;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Exception;

/**
 * Class Voucher
 */
class Voucher extends Model
{
    use HasFactory, LogsActivity, CausesActivity;

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
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'uses' => 'integer',
    ];

    /**
     * @var string[]
     */
    protected $appends = [
        'used',
        'status',
    ];

    /**
     * Set the credits to be in cents.
     *
     * @return Attribute
     */
    protected function credits(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Currency::prepareForDatabase($value)
        );
    }

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
}
