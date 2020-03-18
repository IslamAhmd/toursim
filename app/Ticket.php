<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = [];

    public function destination(){
    	return $this->hasMany('App\TicketDestination');
    }

    public function passenger(){
    	return $this->hasMany('App\TicketPassenger');
    }
    
}
