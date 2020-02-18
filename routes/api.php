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
	Route::post('/register', 'UserController@register')->middleware('SuperAdmin');
	Route::resource('/company', 'CompanyController')->except(['create', 'edit']);
	Route::get('/getcompany', 'CompanyController@getCompany');
	Route::post('/company/{id}/disable', 'CompanyController@disable')->middleware('SuperAdmin');
	Route::resource('/trip', 'TripController')->except(['create', 'edit']);
	Route::get('/trips', 'TripController@getTrips');
	Route::post('/trip/{id}/cooperate', 'TripController@cooperate');
	Route::get('/trip/{tripId}/destination/{destId}/accomodations', 'TripController@accomodations');
	Route::resource('/client', 'ClientController')->except(['create', 'edit']);
	Route::get('getseats', 'ClientController@getseats');
	Route::get('/trip/{id}/buses', 'ClientController@getBuses');


});
