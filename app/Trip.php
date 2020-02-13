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


    protected $appends = array('Destinations');


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


    // public function getGroupNameAttribute(){

    //     return 'lol';

    // }

    public function getDestinationsAttribute($dests){

        // $arr = [];

        // // $dests = $this->Destinations;

        // foreach ((array) $dests as $dest) {
            
        //     $obj->dest = $dest;

        //     $myJSON = json_encode($obj);

        //     $arr = $myJSON;

        //     // array_push($arr, $myJSON);

        // }

        return 1;

    }
}
