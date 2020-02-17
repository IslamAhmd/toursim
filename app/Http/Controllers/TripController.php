<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Trip;
use App\User;
use App\Company;
use App\Destination;
use App\TripProgram;
use Illuminate\Support\Facades\Auth;
use Validator;
use \stdClass;



class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function __construct(){

        // $this->middleware('SuperOrAdmin')->only(['index', 'getCompany', 'show']);
        $this->middleware('Admin')->only(['store', 'update', 'destroy', 'cooperate']);
        // $this->middleware('SuperAdmin')->only(['destroy', 'disable']);

    }


    public function index()
    {
        $Daytrips = Trip::with(['destinations', 'programs'])->where('trip_type', 'dayuse')->get();

        $Groupstrips = Trip::with(['destinations', 'programs'])->where('trip_type', 'groups')->get();


        return response()->json([

            'status' => 'success',
            'data' => [

                'dayuse' => $Daytrips,
                'groups' => $Groupstrips,

            ]

        ]);
    }

    public function getTrips(){

        $trips = Trip::with(['destinations', 'programs'])->get();


        return response()->json([

            'status' => 'success',
            'data' => $trips

        ]);
    }

    public function accomodations($tripId, $destId){

        $tripsAcc = Destination::where('trip_id', $tripId)
                               ->where('id', $destId)
                               ->first()->accomodation;
                               

        $accomodationsArr = [];
        $obj = new stdClass();
        foreach ($tripsAcc as $value) {
            
            $obj->acc = $value;

            $accomodationsArr[] = $obj;

        }

        return response()->json([

            'status' => 'success',
            'data' => $accomodationsArr

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

            'trip_type' => 'required',
            'group_name' => 'required',
            'group_type' => 'required',
            'group_desc' => 'required',
            'transportations' => 'required',
            'Destinations' => 'required',
            'guides' => 'required',
            'arrival_date' => 'required',
            'departure_date' => 'required',
            'capacity' => 'integer',
            'remain_chairs' => 'integer',
            'accomodations' => 'required_if:trip_type,groups',
            // 'dests.*.name' => 'required_if:trip_type,groups',
            'dests' => 'required_if:trip_type,groups',
            'dests.*.arrival_date' => 'required_if:trip_type,groups|date',
            'dests.*.departure_date' => 'required_if:trip_type,groups|date',
            'dests.*.accomodation' => 'required_if:trip_type,groups',
            'programs' => 'required',
            'programs.*.date' => 'required|date',
            'programs.*.items.*.time' => 'required',
            'programs.*.items.*.desc' => 'required',


        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);
        }


        // add new trip
        $trip = Trip::create($request->except(['dests', 'programs']));
        $trip->user_id = Auth::id();
        $trip->company_id = Company::where('user_id', $trip->user_id)->first()->id;
        $trip->company_name = Company::where('id', $trip->company_id)->first()->name;
        $trip->save();


        // add destinations to trip
        $dests = isset($request->dests)? $request->dests : null;

        foreach ((array) $dests as $dest) {

            Destination::create([

                'trip_id' => $trip->id,
                // 'name' => 'glrgmv;lfdkvd',
                'arrival_date' => isset($dest['arrival_date']) ? $dest['arrival_date'] : null,
                'departure_date' => isset($dest['departure_date']) ? $dest['departure_date'] : null,
                'accomodation' => isset($dest['accomodation']) ? $dest['accomodation'] : null

            ]);

        }

        // add program to trip
        $programs = $request->programs;
       
        foreach ((array) $programs as $program) {
            
            $date = $program['date'];
            $times = $program['items'];
            
            foreach ((array) $times as $time) {
                
                TripProgram::create([

                    'trip_id' => $trip->id,
                    'trip_date' => $date,
                    'trip_time' => isset($time['time']) ? $time['time']:null,
                    'desc' => isset($time['desc']) ? $time['desc'] : null

                ]);

            }
        }

        $trip = $trip->with(['destinations', 'programs'])->find($trip->id);


        return response()->json([

            'status' => 'success',
            'data' => $trip

        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $trip = Trip::with(['destinations', 'programs'])->find($id);
        if(! $trip){
            return response()->json([

                'status' => 'error',
                'message' => 'Trip not found'

            ]);
        }


        foreach ($trip['destinations'] as $i => $dest) {

            $dest->name = $trip['Destinations'][$i];
            
        }

        return response()->json([

            'status' => 'success',
            'data' => $trip

        ]);
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
        $trip = Trip::with(['destinations', 'programs'])->find($id);
        if(! $trip){
            return response()->json([

                'status' => 'error',
                'message' => 'Trip not found'

            ]);
        }

        $rules = [

            'trip_type' => 'required',
            'group_name' => 'required',
            'group_type' => 'required',
            'group_desc' => 'required',
            'transportations' => 'required',
            'Destinations' => 'required',
            'guides' => 'required',
            'arrival_date' => 'required',
            'departure_date' => 'required',
            'capacity' => 'integer',
            'remain_chairs' => 'integer',
            'accomodations' => 'required_if:trip_type,groups',
            // 'dests.*.name' => 'required_if:trip_type,groups',
            'dests' => 'required_if:trip_type,groups',
            'dests.*.arrival_date' => 'required_if:trip_type,groups|date',
            'dests.*.departure_date' => 'required_if:trip_type,groups|date',
            'dests.*.accomodation' => 'required_if:trip_type,groups',
            'programs.*.date' => 'required|date',
            'programs.*.items.*.time' => 'required',
            'programs.*.items.*.desc' => 'required',


        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);
        }

        // update trip
        $trip->update($request->except(['dests', 'programs']));
        $trip->user_id = Auth::id();
        $trip->company_id = Company::where('user_id', $trip->user_id)->first()->id;
        $trip->company_name = Company::where('id', $trip->company_id)->first()->name;
        $trip->save();

        // add new destinations to trip
        $trip->destinations()->delete();

        $dests = isset($request->dests)? $request->dests : null;

        foreach ((array) $dests as $dest) {

            Destination::create([

                'trip_id' => $trip->id,
                'arrival_date' => isset($dest['arrival_date']) ? $dest['arrival_date'] : null,
                'departure_date' => isset($dest['departure_date']) ? $dest['departure_date'] : null,
                'accomodation' => isset($dest['accomodation']) ? $dest['accomodation'] : null

            ]);

        }


        // add new program to trip
        $trip->programs()->delete();

        $programs = isset($request->programs)? $request->programs : null;
       
        foreach ((array) $programs as $program) {
            
            $date = $program['date'];
            $times = $program['items'];
            
            foreach ((array) $times as $time) {
                
                TripProgram::create([

                    'trip_id' => $trip->id,
                    'trip_date' => $date,
                    'trip_time' => isset($time['time']) ? $time['time']:null,
                    'desc' => isset($time['desc']) ? $time['desc'] : null

                ]);

            }
        }

        $trip = $trip->with(['destinations', 'programs'])->find($trip->id);


        return response()->json([

            'status' => 'success',
            'data' => $trip

        ]);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $trip = Trip::find($id);
        if(! $trip){
            return response()->json([

                'status' => 'error',
                'message' => 'Trip not found'

            ]);
        }

        $trip->delete();

        return response()->json([

            'status' => 'success',
            'message' => 'Trip deleted Successfully'

        ]);
    }

    public function cooperate(Request $request, $id){

        $trip = Trip::find($id);

        if(! $trip){
            return response()->json([

                'status' => 'error',
                'message' => 'Trip not found'

            ]);
        }

        $user = new User;
        $rules = [

            'users.*.user' => 'exists:users,name'

        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);
        }

        $trip->update([

            'users' => $request->users

        ]);


        $trip = $trip->with(['destinations', 'programs'])->find($trip->id);

        
        return response()->json([

            'status' => 'success',
            'data' => $trip

        ]);


    }
}
