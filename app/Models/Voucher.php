<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Voucher
 * @package App\Models
 */
class Voucher extends Model
{
    use HasFactory;

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

    protected $dates = [
        'expires_at'
    ];

    /**
     *
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function (Voucher $voucher) {
            $voucher->users()->detach();
        });
    }

    /**
     * @return string
     */
    public function getStatus(){
        if ($this->users()->count() >= $this->uses) return 'USES_LIMIT_REACHED';
        if (!is_null($this->expires_at)){
            if ($this->expires_at->isPast()) return 'EXPIRED';
        }

        return 'VALID';
    }

    /**
     * @param User $user
     * @return float
     */
    public function redeem(User $user){
        try {
            $user->increment('credits' , $this->credits);
            $this->users()->attach($user);
        }catch (\Exception $exception) {
            throw $exception;
        }

        return $this->credits;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
