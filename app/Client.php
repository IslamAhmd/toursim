<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Trip;

class Client extends Model
{
    protected $guarded = [];

    protected $casts = [

    	'destination' => 'array',
    	'seats_numbers' => 'array',
    ];


    public function infos(){
    	return $this->hasMany('App\ClientInfo');
    }

    public function bus(){
    	return $this->belongsTo('App\Bus');
    }

    public function companion(){
        return $this->hasMany('App\Companion');
    }

}
