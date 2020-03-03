<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VisaAccept extends Model
{
    protected $guarded = [];

    public function visas(){
    	return $this->hasOne('App\Visa');
    }

}
