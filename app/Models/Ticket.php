<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model {
    protected $fillable = [
        'user_id', 'ticketcategory_id', 'ticket_id', 'title', 'priority', 'message', 'status', 'server'
    ];

    public function ticketcategory(){
    return $this->belongsTo(TicketCategory::class);}

    public function ticketcomments(){
    return $this->hasMany(TicketComment::class);}

    public function user(){
    return $this->belongsTo(User::class);}
} 
  