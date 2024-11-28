<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;

class TicketBlacklist extends Model
{
    use LogsActivity, CausesActivity;
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
    protected $fillable = [
        'user_id', 'status', 'reason',
    ];



    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
