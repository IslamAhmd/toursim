<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
	protected $guarded = [];
    protected $casts = [

    	'accomodation' => 'array'

    ];

    public function trip(){
    	return $this->belongsTo('App\Trip');
    }
}
