<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use App\Company;
use JWTAuth;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function authenticate(Request $request)
    {		
            $credentials = $request->only('name', 'password');
            $user = User::where('name', $request->name)->first();
            if(! $user){
                return response()->json([
                "status" => "error",
                'error' => "User doesn't exist"
                 ]);
            }

            if (Company::where('user_id', $user->id)->exists()) {

                $companyId = Company::where('user_id', $user->id)->first()->id;

                $user->company_id = $companyId;

                if (Auth::attempt($credentials)) {

                    $token = JWTAuth::attempt($credentials);
                    return response()->json([
                        "status" => "success",
                        "data" => [
                            "token" => $token,
                            "user" => $user
                        ]
                    ], 200);



                }
    
            } else {

                if (Auth::attempt($credentials)) {

                    $token = JWTAuth::attempt($credentials);
                    return response()->json([
                        "status" => "success",
                        "data" => [
                            "token" => $token,
                            "user" => $user
                        ]
                    ], 200);



                }

            }


            return response()->json([
                "status" => "error",
                'error' => 'invalid_credentials'
            ]);
            
    }

    public function register(Request $request){

    	$role = new Role;
    	$rules = [
    		'name' => ['required', 'string', 'max:255', 'unique:users'],
        	'password' => ['required', 'string'],
        	'fees' => 'integer'
    	];

    	$validator = Validator::make($request->all(), $rules);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);
        }

        $user = new User;
        $user->name = $request->name;
        $user->fees = $request->fees;
        $user->password = bcrypt($request->password);
        if(Auth::user()->role->name == 'super_admin'){
        	$user->role_id = 2;
        	$user->role_name = 'admin';
        } 
        // elseif(Auth::user()->role->name == 'admin'){
        // 	$user->role_id = 3;
        // 	$user->role_name = 'hr';
        // } 
        elseif(Auth::user()->role->name == 'hr'){
        	$user->role_id = 4;
        	$user->role_name = 'user';
        }
        $user->save();

        return response()->json([

    		'status' => 'success',
    		'data' => $user

    	]);
    	

    }
}
