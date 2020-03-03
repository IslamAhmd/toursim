<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TripTour extends Model
{
    protected $guarded =[];

    public function client(){
    	return $this->hasOne('App\TourClient', 'tour_id');
    }

}
