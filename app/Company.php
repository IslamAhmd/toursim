<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $guarded = [];

    protected $hidden = ['user_id'];

    public function users(){
    	return $this->hasMany('App\User');
    }
}
