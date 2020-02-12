<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Role;

class RoleController extends Controller
{

    public function index(){

    	$roles = Role::get();

    	return response()->json([

    		'status' => 'success',
    		'data' => $roles

    	]);

    }
}
