<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TripProgram extends Model
{
    protected $guarded =[];

    public function trip(){
    	return $this->belongsTo('App\Trip');
    }

    public function setTripTimeAttribute($value){
    	$this->attributes['trip_time'] = date('H:i', strtotime($value));
    }

}
