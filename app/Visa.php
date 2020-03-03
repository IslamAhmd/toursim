<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Visa extends Model
{
    protected $guarded = [];

    protected $casts = [

    	'type' => 'array'

    ];

    public function accepts(){
    	return $this->hasOne('App\VisaAccept', 'visa_id');
    }

    public function company(){
    	return $this->belongsTo('App\Company');
    }
}
