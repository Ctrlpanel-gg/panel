<?php

namespace App\Models;

use App\Constants\Roles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role as BaseRole;

class Role extends BaseRole
{
    use HasFactory, LogsActivity, CausesActivity;

    /**
     * Determine if the role can be deleted.
     * 
     * @return bool
     */
    public function isDeletable(): bool
    {
        return !in_array($this->id, [
            Roles::ADMIN_ROLE_ID,
            Roles::SUPPORT_TEAM_ROLE_ID,
            Roles::CLIENT_ROLE_ID
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'power',
        'color'
    ];
}
