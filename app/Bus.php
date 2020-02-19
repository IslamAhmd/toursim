<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Bus extends Model
{
    protected $guarded = [];

    protected $casts = [

    	'accomodation' => 'array'

    ];

    public function countSeats($id){

    	return $this->where('trip_id', $id)
                    ->where('client_id', '!=', null)
    				->count();

    }

    public function clients(){
    	return $this->hasMany('App\Client');
    }
    
}
