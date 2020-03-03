<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\TourClient;
use App\TripTour;
use App\Trip;
use App\Client;
use Illuminate\Support\Facades\Auth;

class TourController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($tripId)
    {
        $trip = Trip::where('user_id', Auth::id())->findOrFail($tripId);

        $tours = TripTour::with('client')->where('trip_id', $trip->id)->get();

        return response()->json([

            'status' => 'success',
            'data' => $tours

        ]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $rules = [

            'trip_id' => 'required|exists:trips,id',
            'name' => 'required',
            'date' => 'required|date',
            'tour_leader' => 'required',
            'driver_name' => 'required',
            'mobile' => 'required',
            'client' => 'required',
            'passport_no' => 'required',
            'accomodation' => 'required',
            'adult' => 'required|integer',
            'child' => 'integer',
            'payment' => 'required|integer',
            'notes' => 'required'

        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);
        }


        $trip = Trip::where('user_id', Auth::id())
                    ->where('id', $request->trip_id)->firstOrFail();


        if($trip->trip_genre == "outgoing" && $trip->trip_type == "groups"){

            $tour = TripTour::create($request->only(['trip_id', 'name', 'date', 'tour_leader', 'driver_name', 'mobile']));
            $client = TourClient::create($request->only(['client', 'passport_no', 'accomodation', 'adult', 'child', 'payment', 'notes']));
            $client->tour_id = $tour->id;
            $client->total = $client->adult + $client->child;
            $client->save();

            $tour->client = $client;

            return response()->json([

                'status' => 'success',
                'data' => $tour

            ]);

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $tour = TripTour::with('client')->find($id);

        if(! $tour){

            return response()->json([

                'status' => 'error',
                'message' => 'Tour not Found'

            ]);

        }

        if(Trip::where('id', $tour->trip_id)->where('user_id', Auth::id())->exists()){

            return response()->json([

                'status' => 'success',
                'data' => $tour

            ]);

        }

        abort(403);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $tour = TripTour::with('client')->find($id);

        if(! $tour){

            return response()->json([

                'status' => 'error',
                'message' => 'Tour not Found'

            ]);

        }


        $rules = [

            'trip_id' => 'required|exists:trips,id',
            'name' => 'required',
            'date' => 'required|date',
            'tour_leader' => 'required',
            'driver_name' => 'required',
            'mobile' => 'required',
            'client' => 'required',
            'passport_no' => 'required',
            'accomodation' => 'required',
            'adult' => 'required|integer',
            'child' => 'integer',
            'payment' => 'required|integer',
            'notes' => 'required'

        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);
        }

        $trip = Trip::where('user_id', Auth::id())
                    ->where('id', $request->trip_id)->firstOrFail();

        if($trip->trip_genre == "outgoing" && $trip->trip_type == "groups"){

            $tour->update($request->only(['trip_id', 'name', 'date', 'tour_leader', 'driver_name', 'mobile']));
            $client->update($request->only(['client', 'passport_no', 'accomodation', 'adult', 'child', 'payment', 'notes']));
            $client->tour_id = $tour->id;
            $client->total = $client->adult + $client->child;
            $client->save();

            $tour->client = $client;

            return response()->json([

                'status' => 'success',
                'data' => $tour

            ]);

        }


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tour = TripTour::with('client')->find($id);

        if(! $tour){

            return response()->json([

                'status' => 'error',
                'message' => 'Tour not Found'

            ]);

        }

        if(Trip::where('id', $tour->trip_id)->where('user_id', Auth::id())->exists()){

            $tour->delete();

            return response()->json([

                'status' => 'success',
                'message' => 'Tour Deleted successfully'

            ]);

        }

        abort(403);
    }
}
