<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Trip;
use App\User;
use App\Company;
use App\Bus;
use App\Nationality;
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

        $this->middleware('Admin')->only(['store', 'update', 'destroy', 'cooperate']);

    }

    public function nationality(){

        $nationalities = Nationality::get();

        return response()->json([

            'status' => 'success',
            'data' => $nationalities

        ]);

    }


    public function index($companyId)
    {
        $company = Company::where('user_id', Auth::id())->findOrFail($companyId);

        $Daytrips = Trip::with(['destinations', 'programs'])
                        ->where('company_id', $company->id)
                        ->where('trip_genre', 'domestic')
                        ->where('trip_type', 'dayuse')->get();

        $Groupstrips = Trip::with(['destinations', 'programs'])
                            ->where('company_id', $company->id)
                            ->where('trip_type', 'groups')
                            ->where('trip_genre', 'domestic')->get();

        $outGoingtrips = Trip::with(['destinations', 'programs'])
                            ->where('company_id', $company->id)
                            ->where('trip_genre', 'outgoing')->get();

        $inComingGroups = Trip::with(['destinations', 'programs'])
                                ->where('company_id', $company->id)
                                ->where('trip_genre', 'incoming')
                                ->where('trip_type', 'groups')->get();

        $inComingDay = Trip::with(['destinations', 'programs'])
                                ->where('company_id', $company->id)
                                ->where('trip_genre', 'incoming')
                                ->where('trip_type', 'dayuse')->get();



        return response()->json([

            'status' => 'success',
            'data' => [

                'dayuse' => $Daytrips,
                'groups' => $Groupstrips,
                'outgoing' => $outGoingtrips,
                'incomingGroups' => $inComingGroups,
                'inComingDayUse' => $inComingDay

            ]

        ]);
    }

    public function getTrips($companyId){

        $company = Company::where('user_id', Auth::id())->findOrFail($companyId);

        $trips = Trip::with(['destinations', 'programs'])
                     ->where('company_id', $company->id)->get();


        return response()->json([

            'status' => 'success',
            'data' => $trips

        ]);
    }

    public function accomodations($tripId, $destId){

        $trip = Trip::where('user_id', Auth::id())
                    ->findOrFail($tripId);


        $tripsAcc = Destination::where('trip_id', $trip->id)
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

            'trip_genre' => 'required',
            'trip_type' => 'required',
            'group_name' => 'required',
            'group_type' => 'required',
            'group_desc' => 'required',
            'country' => 'required_if:trip_genre,outgoing',
            'nationality' => 'required_if:trip_genre,incoming',
            'domestic_trans' => 'required_if:trip_genre,outgoing|required_if:trip_genre,incoming',
            'transportations' => 'required_unless:trip_genre,incoming',
            'Destinations' => 'required',
            'guides' => 'required',
            'leaders' => 'required_if:trip_genre,incoming',
            'arrival_date' => 'required',
            'arrival_port' => 'required_if:trip_genre,incoming',
            'arrival_time' => 'required_if:trip_genre,incoming',
            'departure_date' => 'required',
            'departure_port' => 'required_if:trip_genre,incoming',
            'departure_time' => 'required_if:trip_genre,incoming',
            'capacity' => 'integer',
            'remain_chairs' => 'integer',
            'accomodations' => 'required_if:trip_type,groups',
            'dests' => 'required_if:trip_type,groups',
            'dests.*.arrival_date' => 'required_if:trip_type,groups|date',
            'dests.*.departure_date' => 'required_if:trip_type,groups|date',
            'dests.*.accomodation' => 'required_if:trip_type,groups',
            'programs' => 'required',
            'programs.*.items.*.date' => 'required|date',
            'programs.*.items.*.time' => 'required',
            'programs.*.items.*.desc' => 'required',


        ];

        $messages = [


            'dests.required_if' => 'destinations are required if the trip is groups',
            'dests.*.arrival_date.required_if' => 'the destination arrival date is required if the trip is groups',
            'dests.*.arrival_date.date' => 'the destination arrival date must be date',
            'dests.*.departure_date' => 'the destination arrival date is required if the trip is groups',
            'dests.*.departure_date' => 'the destination departure date must be date',
            'dests.*.accomodation' => 'the destination accomodation is required if the trip is groups',
            'programs.required' => 'the trip program is required',
            'programs.*.items.*.date.date' => 'the program date must be date',
            'programs.*.items.*.date.required' => 'the program date is required',
            'programs.*.items.*.time.required' => 'the program time is required',
            'programs.*.items.*.desc.required' => 'the program description is required',
            'country.required_if' => 'the country field is required if trip type is outgoing',
            'domestic_trans.required_if' => 'the domestic transportations field is required if trip type is outgoing',

        ];
        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);
        }

        if(! Company::where('user_id', Auth::id())->exists()){

            return response()->json([
              "status" => "error",
              "message" => "Please Create Your Company First"
            ]);

        }
        // add new trip
        $trip = Trip::create($request->except(['dests', 'programs']));
        $trip->user_id = Auth::id();
        $trip->company_id = Company::where('user_id', $trip->user_id)->first()->id;
        $trip->save();


        // add destinations to trip
        $dests = isset($request->dests)? $request->dests : null;

        foreach ((array) $dests as $dest) {

            Destination::create([

                'trip_id' => $trip->id,
                'arrival_date' => isset($dest['arrival_date']) ? $dest['arrival_date'] : null,
                'departure_date' => isset($dest['departure_date']) ? $dest['departure_date'] : null,
                'accomodation' => isset($dest['accomodation']) ? $dest['accomodation'] : null

            ]);

        }

        // add program to trip
        $programs = $request->programs;
       
        foreach ((array) $programs as $program) {
            
            $destName = $program['dest_name'];
            $times = $program['items'];

            
            foreach ((array) $times as $time) {
                
                TripProgram::create([

                    'trip_id' => $trip->id,
                    'trip_date' => isset($time['date']) ? $time['date']:null,
                    'trip_time' => isset($time['time']) ? $time['time']:null,
                    'dest_name' => $destName,
                    'desc' => isset($time['desc']) ? $time['desc'] : null

                ]);

            }
        }




        $busArr = range(1, 49);

        foreach ($busArr as $b) {
                
            Bus::create([


                'num' => $b,
                'trip_id' => $trip->id              


            ]);

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


        $this->authorize('view', $trip);

        foreach ($trip['destinations'] as $i => $dest) {



            $dest->name = $trip['Destinations'][$i];

            $accomodationsArr = [];

            foreach ($dest['accomodation'] as $acc) {

                $obj = new stdClass();

                $obj->acc = $acc;
                $accomodationsArr[] = $obj;

            }

            $dest['accomodation'] = $accomodationsArr; 

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


        $this->authorize('update', $trip);

        $rules = [

            'trip_genre' => 'required',
            'trip_type' => 'required',
            'group_name' => 'required',
            'group_type' => 'required',
            'group_desc' => 'required',
            'country' => 'required_if:trip_genre,outgoing',
            'nationality' => 'required_if:trip_genre,incoming',
            'domestic_trans' => 'required_if:trip_genre,outgoing|required_if:trip_genre,incoming',
            'transportations' => 'required_unless:trip_genre,incoming',
            'Destinations' => 'required',
            'guides' => 'required',
            'leaders' => 'required_if:trip_genre,incoming',
            'arrival_date' => 'required',
            'arrival_port' => 'required_if:trip_genre,incoming',
            'arrival_time' => 'required_if:trip_genre,incoming',
            'departure_date' => 'required',
            'departure_port' => 'required_if:trip_genre,incoming',
            'departure_time' => 'required_if:trip_genre,incoming',
            'capacity' => 'integer',
            'remain_chairs' => 'integer',
            'accomodations' => 'required_if:trip_type,groups',
            'dests' => 'required_if:trip_type,groups',
            'dests.*.arrival_date' => 'required_if:trip_type,groups|date',
            'dests.*.departure_date' => 'required_if:trip_type,groups|date',
            'dests.*.accomodation' => 'required_if:trip_type,groups',
            'programs' => 'required',
            'programs.*.date' => 'required|date',
            'programs.*.time' => 'required',
            'programs.*.desc' => 'required',

        ];

        $messages = [


            'dests.required_if' => 'destinations are required if the trip is groups',
            'dests.*.arrival_date.required_if' => 'the destination arrival date is required if the trip is groups',
            'dests.*.arrival_date.date' => 'the destination arrival date must be date',
            'dests.*.departure_date' => 'the destination arrival date is required if the trip is groups',
            'dests.*.departure_date' => 'the destination departure date must be date',
            'dests.*.accomodation' => 'the destination accomodation is required if the trip is groups',
            'programs.required' => 'the trip program is required',
            'programs.*.date.date' => 'the program date must be date',
            'programs.*.date.required' => 'the program date is required',
            'programs.*.time.required' => 'the program time is required',
            'programs.*.desc.required' => 'the program description is required',
            'country.required_if' => 'the country field is required if trip type is outgoing',
            'domestic_trans.required_if' => 'the domestic transportations field is required if trip type is outgoing',

        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);
        }

        if(! Company::where('user_id', Auth::id())->exists()){

            return response()->json([
              "status" => "error",
              "message" => "Please Create Your Company First"
            ]);

        }


            // update trip
        $trip->update($request->except(['dests', 'programs']));
        $trip->user_id = Auth::id();
        $trip->company_id = Company::where('user_id', $trip->user_id)->first()->id;
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
                    
                                    
            TripProgram::create([

                'trip_id' => $trip->id,
                'trip_date' => isset($program['date']) ? $program['date']:null,
                'dest_name' => isset($program['dest_name']) ? $program['dest_name']:null,
                'trip_time' => isset($program['time']) ? $program['time']:null,
                'desc' => isset($program['desc']) ? $program['desc'] : null

            ]);

            
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

        $this->authorize('delete', $trip);


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

        $this->authorize('cooperate', $trip);

        $user = new User;
        $rules = [

            'users.*' => 'exists:users,name'

        ];

       $messages = [

          'users.*.exists' => 'the selected user does not exist'
    
       ];

        $validator = Validator::make($request->all(), $rules, $messages);

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
