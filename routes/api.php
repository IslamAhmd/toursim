<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', 'UserController@authenticate');


Route::group(['middleware' => ['jwt.verify']], function() {
        
	Route::get('/roles', 'RoleController@index')->middleware('SuperAdmin');
	Route::post('/register', 'UserController@register');
	Route::resource('/company', 'CompanyController')->except(['create', 'edit'])->middleware(['Admin']);
	Route::get('/getcompany', 'CompanyController@getCompany')->middleware(['Admin']);
	Route::resource('/trip', 'TripController')->except(['create', 'edit'])->middleware(['Admin']);
	Route::post('/trip/{id}/cooperate', 'TripController@cooperate')->middleware(['Admin']);


});
