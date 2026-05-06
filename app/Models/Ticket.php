<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Ticket extends Model
{
    use LogsActivity;

    const PRIORITY_LOW = 'Low';
    const PRIORITY_MEDIUM = 'Medium';
    const PRIORITY_HIGH = 'High';

    const PRIORITY_VALUES = [
        self::PRIORITY_LOW,
        self::PRIORITY_MEDIUM,
        self::PRIORITY_HIGH,
    ];

    protected $fillable = [
        'user_id', 'ticketcategory_id', 'ticket_id', 'title', 'priority', 'message', 'status', 'server',
    ];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            -> logOnlyDirty()
            -> logOnly(['*'])
            -> dontSubmitEmptyLogs();
    }

    public function ticketcategory()
    {
        return $this->belongsTo(TicketCategory::class);
    }

    public function ticketcomments()
    {
        return $this->hasMany(TicketComment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
