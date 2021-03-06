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


Route::post('/login', 'UserController@authenticate');


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['middleware' => ['jwt.verify']], function() {
        
	Route::get('/roles', 'RoleController@index')->middleware('SuperAdmin');
	Route::post('/register', 'UserController@register')->middleware('SuperAdmin');


	Route::resource('/company', 'CompanyController')->except(['create', 'edit']);
	Route::get('/getcompany', 'CompanyController@getCompany');
	Route::post('/company/{id}/disable', 'CompanyController@disable')->middleware('SuperAdmin');

	
	Route::resource('/trip', 'TripController')->except(['create', 'edit', 'index']);
	Route::get('/{companyId}/trip/', 'TripController@index');
	Route::get('/{companyId}/trips', 'TripController@getTrips');
	Route::post('/trip/{id}/cooperate', 'TripController@cooperate');
	Route::get('/trip/{tripId}/destination/{destId}/accomodations', 'TripController@accomodations');
	Route::get('/nationalities', 'TripController@nationality');


	Route::resource('/client', 'ClientController')->except(['create', 'edit', 'index']);
	Route::get('/{companyId}/client', 'ClientController@index');
	Route::get('/{companyId}/getseats', 'ClientController@getseats');
	Route::get('/trip/{tripId}/buses', 'ClientController@getBuses');
	Route::get('/trip/{tripId}/nationality', 'ClientController@getNationality');
	// Route::get('/trip/{tripId}/clients', 'ClientController@getNames');

	Route::resource('tour', 'TourController')->except(['create', 'edit', 'index']);
	Route::get('/{tripId}/tour', 'TourController@index');

	Route::resource('visa', 'VisaController')->except(['create', 'edit']);
	Route::get('countries', 'VisaController@countries');
	

	Route::resource('accept', 'VisaAcceptController')->except(['create', 'edit']);


	Route::resource('ticket', 'TicketController')->except(['create', 'edit', 'index']);
	Route::get('/{userId}/ticket', 'TicketController@index');



});
