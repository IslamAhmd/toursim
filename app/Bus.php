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


    public function checkSeats(Array $seatsArr, $tripId){

        foreach ($seatsArr as $seat) {
            
            if($this->where('num', $seat)->where('trip_id', $tripId)->exists()){

                $clientId = $this->where('num', $seat)
                            ->where('trip_id', $tripId)->first()->client_id;

                if($clientId != null){
                    return true;
                }

                return false;

            }

            return false;

        }

    }

    public function clients(){
    	return $this->hasMany('App\Client');
    }
    
}
