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
        'Destinations' => 'array'

    ];

    protected $hidden = ['user_id'];

    public function destinations(){

    	return $this->hasMany('App\Destination');

    }

    public function programs(){

    	return $this->hasMany('App\TripProgram');

    }

    public function user(){
    	return $this->belongsTo('App\Trip');
    }

    public function bus(){
        return $this->hasOne('App\Bus');
    }


}
