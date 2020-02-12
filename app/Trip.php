<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $guarded = [];

    protected $casts = [

    	'transportations' => 'array',
    	'guides' => 'array',
    	'accomodations' => 'array',
    	'users' => 'array',

    ];

    protected $hidden = ['user_id'];

    // public function setArrivalDateAttribute($value){
    // 	$this->attributes['arrival_date'] = date('Y/m/d', strtotime($value));
    // }

    // public function setDepartureDateAttribute($value){
    // 	$this->attributes['departure_date'] = date('Y/m/d', strtotime($value));
    // }

    public function destinations(){

    	return $this->hasMany('App\Destination');

    }

    public function programs(){

    	return $this->hasMany('App\TripProgram');

    }

    public function user(){
    	return $this->belongsTo('App\Trip');
    }
}
