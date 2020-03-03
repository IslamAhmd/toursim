<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TourClient extends Model
{
    protected $guarded =[];

    public function tour(){
    	return $this->hasOne('App\TripTour', 'tour_id');
    }
}
