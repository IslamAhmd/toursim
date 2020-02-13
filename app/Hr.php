<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hr extends Model
{
    protected $guarded = [];

    public function company(){
    	return $this->hasOne('App\Company');
    }
}
