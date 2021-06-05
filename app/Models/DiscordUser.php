<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;

class DiscordUser extends Model
{
    use HasFactory;

    protected $guarded = [];

    public $incrementing = false;

    /**
     * @return BelongsTo
     */
    public function user(){
        return $this->belongsTo(User::class);
    }

    /**
     * @return string
     */
    public function getAvatar(){
        return "https://cdn.discordapp.com/avatars/" . $this->id . "/" . $this->avatar . ".png";
    }
}
