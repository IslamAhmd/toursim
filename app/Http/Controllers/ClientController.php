<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Client;
use App\Company;
use App\ClientInfo;
use App\Trip;
use App\Bus;
use App\Companion;
use Validator;
use Illuminate\Support\Facades\Auth;
use \stdClass;


class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(){

        
        $this->middleware('Admin');
        
    }

    // public function getNames($tripId){

    //     $clients = Client::where('trip_id', $tripId)->get(['id', 'name', 'passport_num']);

    //     $this->authorize('getNames', $clients);

    //     return response()->json([

    //         'status' => 'success',
    //         'data' => $clients

    //     ]);

    // }

    public function getBuses($tripId){

        $trip = Trip::where('user_id', Auth::id())->findOrFail($tripId);

        $buses = Bus::where('trip_id', $trip->id)->get();

        return response()->json([

            'status' => 'success',
            'data' => $buses

        ]);

    }

    public function getNationality($tripId){

        $tripNationality = Trip::where('user_id', Auth::id())
                    ->where('id', $tripId)
                    ->firstOrFail()->nationality;


        $nationality = [];
        foreach ((array) $tripNationality as $value) {

            $obj = new stdClass;

            $obj->name = $value;

            $nationality[] = $obj;

        }


        return response()->json([

            'status' => 'success',
            'data' => $nationality

        ]);

    }

    public function index($companyId)
    {

        $trips = Trip::where('user_id', Auth::id())
                    ->where('company_id', $companyId)
                    ->get();

        $groupsClients = Client::with(['infos', 'companion'])->whereIn('trip_id', $trips->pluck('id'))
                                ->where('trip_type', 'groups')
                                ->where('trip_genre', 'domestic')->get();

        $dayuseClients = Client::with(['infos', 'companion'])->whereIn('trip_id', $trips->pluck('id'))
                                ->where('trip_type', 'dayuse')
                                ->where('trip_genre', 'domestic')->get();

        $individualClients = Client::with(['infos', 'companion'])
                                    ->where('trip_type', 'individual')
                                    ->where('trip_genre', 'domestic')->get();

        $outgoinggroupsClients = Client::with(['infos', 'companion'])->whereIn('trip_id', $trips->pluck('id'))
                                ->where('trip_type', 'groups')
                                ->where('trip_genre', 'outgoing')->get();

        $outgoingindividualClients = Client::with(['infos', 'companion'])
                                    ->where('trip_type', 'individual')
                                    ->where('trip_genre', 'outgoing')->get();


        $inComingGroupsClients = Client::with(['infos', 'companion'])->whereIn('trip_id', $trips->pluck('id'))
                                ->where('trip_type', 'groups')
                                ->where('trip_genre', 'incoming')->get();

        $incomingDayuseClients = Client::with(['infos', 'companion'])->whereIn('trip_id', $trips->pluck('id'))
                                ->where('trip_type', 'dayuse')
                                ->where('trip_genre', 'incoming')->get();

        return response()->json([

            'status' => 'success',
            'data' => [

                'groups' => $groupsClients,
                'dayuse' => $dayuseClients,
                'individual' => $individualClients,
                'outgoingGroups' => $outgoinggroupsClients,
                'outgoingIndividual' => $outgoingindividualClients,
                'incomingGroups' => $inComingGroupsClients,
                'incomingDayuse' => $incomingDayuseClients

            ]

        ]);
    }

    public function getseats($companyId){

        $trip = Trip::where('user_id', Auth::id())
                    ->where('company_id', $companyId)
                    ->firstOrFail();


        $clients = Client::where('trip_id', $trip->id)
                        ->get(['id', 'name', 'seats_numbers']);

        return response()->json([

            'status' => 'success',
            'data' => $clients

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
            'trip_id' => 'exists:trips,id',
            'date' => 'required|date',
            'name' => 'required',
            'contact_num' => 'required_unless:trip_genre,incoming|integer',
            'passport_num' => 'required_if:trip_genre,outgoing|required_if:trip_genre,incoming',
            'nationality' => 'required_if:trip_genre,outgoing|required_if:trip_genre,incoming',
            'category' => 'required',
            'travel_agency' => 'required',
            'single' => 'integer',
            'double' => 'integer',
            'triple' => 'integer',
            'quad' => 'integer',
            'adult' => 'integer|required_if:trip_type,dayuse',
            'child' => 'integer',
            'infant' => 'integer',
            'extra_seats' => 'integer',
            'seats_numbers' => 'required_unless:trip_type,individual',
            'seats_numbers.*' => 'integer|exists:buses,num',
            'booking' => 'required',
            'seats' => 'required',
            'status' => 'required',
            'invoice_num' => 'required|integer',
            'notes' => 'required',
            'dests' => 'required_unless:trip_type,dayuse',
            'dests.*.name' => 'required_if:trip_type,individual',
            'dests.*.arrival_date' => 'date|required_if:trip_type,individual',
            'dests.*.departure_date' => 'date|required_if:trip_type,individual|after_or_equal:dests.*.arrival_date',
            'dests.*.accomodation' => 'required_unless:trip_type,dayuse',
            'dests.*.room_category' => 'required_unless:trip_type,dayuse',
            'dests.*.meal_plan' => 'required_unless:trip_type,dayuse',

        ];

        $messages = [

            'dests.required_unless' => 'the destination is required if the trip type is individual or groups',
            'dests.*.accomodation.required_unless' => 'The destination accomodation field is required if the trip type is individual or groups',
            'dests.*.room_category.required_unless' => 'The destination room category field is required if the trip type is individual or groups',
            'dests.*.meal_plan.required_unless' => 'The destination meal plan field is required if the trip type is individual or groups',
            'dests.*.name.required_if' => 'the destination name field is required if the trips type is individual',
            'dests.*.arrival_date.required_if' => 'the destination arrival date field is required if the trips type is individual',
            'dests.*.departure_date.required_if' => 'the destination departure date field is required if the trips type is individual',
            'seats_numbers.*.exists' => 'this seat number does not exist in the bus',
            'seats_numbers.*.integer' => 'this seat must be number',
            'passport_num.required_if' => 'the passport number is required if the trip is outgoing or incoming',
            'nationality.required_if' => 'the nationality is required if the trip is outgoing or incoming',
            'dests.*.departure_date.after_or_equal' => "the destination's departure date must be after or equal it's destination's arrival date"


        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);
        }

        $trip = new Trip;
        $bus = new Bus;

        if($request->trip_type != 'individual'){

            if($bus->checkSeats($request->seats_numbers, $request->trip_id)){

                return response()->json([

                    'status' => 'error',
                    'message' => 'This seat is booked, please enter valid seats'

                ]);

            }

        }

        $client = Client::create($request->except(['dests', 'comps']));
        $client->user_id = Auth::id();
        $client->single = 1 * $request->single;
        $client->double = 2 * $request->double;
        $client->triple = 3 * $request->triple;
        $client->quad = 4 * $request->quad;
        $client->total_rooms = $request->single + $request->double + $request->triple + $request->quad;
        $client->adult = isset($request->adult)? $request->adult : $client->single + $client->double + $client->triple + $client->quad;
        $client->total_people = $client->adult + $client->child + $client->infant;

        $client->seats_no = $client->total_people - $client->infant;
        $client->total_seats = $client->seats_no + $client->extra_seats;
        $client->save();


        if($client->trip_type == 'groups' || $client->trip_type == 'individual'){

            $trip = new Trip;

            if($client->trip_type == 'groups'){

                $destName = $trip->where('id', $client->trip_id)->first()->Destinations;

            }

            $dests = $request->dests;

            foreach ((array) $dests as $i => $dest) {
                ClientInfo::create([

                    'dest_name' => isset($dest['name'])? $dest['name'] : $destName[$i],
                    'client_id' => $client->id,
                    'arrival_date' => isset($dest['arrival_date'])? $dest['arrival_date']:null,
                    'departure_date' => isset($dest['departure_date'])? $dest['departure_date']:null,
                    'accomodation' => $dest['accomodation'],
                    'room_category' => $dest['room_category'],
                    'meal_plan' => $dest['meal_plan']

                ]);
            }

        }


        if($client->trip_type != 'individual'){

            $trip = $trip->where('id', $client->trip_id)->first();

            $seatsArr = $client->seats_numbers;


            foreach ((array) $seatsArr as $seat) {
                
                Bus::where('num', $seat)
                    ->where('trip_id', $trip->id)->update([

                    'client_id' => $client->id,
                    'client_name' => $client->name,
                    'accomodation' => $client->infos()->pluck('accomodation')

                ]);

            }

            
            if(count($seatsArr) > (49 - $bus->countSeats($trip->id))){

                return response()->json([
                  "status" => "error",
                  "message" => "There is no enough chairs"

                ]);


            }


            $remain_chairs = $trip->capacity - $bus->countSeats($trip->id);


            $trip->update([

                'remain_chairs' => $remain_chairs

            ]);

        }   
        

        $comps = isset($request->comps)? $request->comps : null;
        foreach ((array) $comps as $comp) {
            
            Companion::create([

                'client_id' => $client->id,
                'companion' => $comp['companion'],
                'passport_no' => $comp['passport_no'],
                'comp_nationality' => $comp['comp_nationality']

            ]);

        }

        $client = $client->with(['infos', 'companion'])->find($client->id);

        return response()->json([

            'status' => 'success',
            'data' => [

                'client' => $client,
                'remain chairs' => $trip->remain_chairs

            ]

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
        $client = Client::with(['infos', 'companion'])->find($id);

        if(! $client){

            return response()->json([

                'status' => 'error',
                'message' => 'Client Not Found'

            ]);

        }

        $this->authorize('view', $client);
        
        $client->double = $client->double / 2;
        $client->triple = $client->triple / 3;
        $client->quad = $client->quad / 4;

        return response()->json([

            'status' => 'success',
            'data' => $client

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
        $client = Client::find($id);

        if(! $client){

            return response()->json([

                'status' => 'error',
                'message' => 'Client Not Found'

            ]);

        }

        $this->authorize('update', $client);

        $rules = [

            'trip_genre' => 'required',
            'trip_type' => 'required',
            'trip_id' => 'exists:trips,id',
            'date' => 'required|date',
            'name' => 'required',
            'contact_num' => 'required_unless:trip_genre,incoming|integer',
            'passport_num' => 'required_if:trip_genre,outgoing|required_if:trip_genre,incoming',
            'nationality' => 'required_if:trip_genre,outgoing|required_if:trip_genre,incoming',
            'category' => 'required',
            'travel_agency' => 'required',
            'single' => 'integer',
            'double' => 'integer',
            'triple' => 'integer',
            'quad' => 'integer',
            'adult' => 'integer|required_if:trip_type,dayuse',
            'child' => 'integer',
            'infant' => 'integer',
            'extra_seats' => 'integer',
            'seats_numbers' => 'required_unless:trip_type,individual',
            'seats_numbers.*' => 'integer|exists:buses,num',
            'booking' => 'required',
            'seats' => 'required',
            'status' => 'required',
            'invoice_num' => 'required|integer',
            'notes' => 'required',
            'dests' => 'required_unless:trip_type,dayuse',
            'dests.*.name' => 'required_if:trip_type,individual',
            'dests.*.arrival_date' => 'date|required_if:trip_type,individual',
            'dests.*.departure_date' => 'date|required_if:trip_type,individual|after_or_equal:dests.*.arrival_date',
            'dests.*.accomodation' => 'required_unless:trip_type,dayuse',
            'dests.*.room_category' => 'required_unless:trip_type,dayuse',
            'dests.*.meal_plan' => 'required_unless:trip_type,dayuse',

        ];

        $messages = [

            'dests.required_unless' => 'the destination is required if the trip type is individual or groups',
            'dests.*.accomodation.required_unless' => 'The destination accomodation field is required if the trip type is individual or groups',
            'dests.*.room_category.required_unless' => 'The destination room category field is required if the trip type is individual or groups',
            'dests.*.meal_plan.required_unless' => 'The destination meal plan field is required if the trip type is individual or groups',
            'dests.*.name.required_if' => 'the destination name field is required if the trips type is individual',
            'dests.*.arrival_date.required_if' => 'the destination arrival date field is required if the trips type is individual',
            'dests.*.departure_date.required_if' => 'the destination departure date field is required if the trips type is individual',
            'seats_numbers.*.exists' => 'this seat number does not exist in the bus',
            'seats_numbers.*.integer' => 'this seat must be number',
            'passport_num.required_if' => 'the passport number is required if the trip is outgoing or incoming',
            'nationality.required_if' => 'the nationality is required if the trip is outgoing or incoming',
            'dests.*.departure_date.after_or_equal' => "the destination's departure date must be after or equal it's destination's arrival date"

        ];



        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);
        }


        $trip = new Trip;
        $bus = new Bus;

        if($client->trip_type != 'individual'){


            $trip = $trip->where('id', $client->trip_id)->first();

            $seatsArr = $client->seats_numbers;

            foreach ((array) $seatsArr as $seat) {
                
                $bus->where('num', $seat)
                    ->where('trip_id', $trip->id)->update([

                    'client_id' => null,
                    'client_name' => null,
                    'accomodation' => null

                ]);

            }

            if($bus->checkSeats($request->seats_numbers, $request->trip_id)){

                return response()->json([

                    'status' => 'error',
                    'message' => 'This seat is booked, please enter valid seats'

                ]);

            }

        }

        
        $client->update($request->except(['dests', 'comps']));
        $client->user_id = Auth::id();
        $client->single = 1 * $request->single;
        $client->double = 2 * $request->double;
        $client->triple = 3 * $request->triple;
        $client->quad = 4 * $request->quad;
        $client->total_rooms = $request->single + $request->double + $request->triple + $request->quad;
        $client->adult = isset($request->adult)? $request->adult : $client->single + $client->double + $client->triple + $client->quad;
        $client->total_people = $client->adult + $client->child + $client->infant;
        $client->seats_no = $client->total_people - $client->infant;
        $client->total_seats = $client->seats_no + $client->extra_seats;
        $client->save();


        if($client->trip_type == 'groups' || $client->trip_type == 'individual'){


            if($client->trip_type == 'groups'){

                $destName = $trip->where('id', $client->trip_id)->first()->Destinations;

            }

            $client->infos()->delete();
            $dests = $request->dests;
            foreach ((array) $dests as $i => $dest) {
                ClientInfo::create([

                    'dest_name' => isset($dest['name'])? $dest['name'] : $destName[$i],
                    'client_id' => $client->id,
                    'arrival_date' => isset($dest['arrival_date'])? $dest['arrival_date']:null,
                    'departure_date' => isset($dest['departure_date'])? $dest['departure_date']:null,
                    'accomodation' => $dest['accomodation'],
                    'room_category' => $dest['room_category'],
                    'meal_plan' => $dest['meal_plan']

                ]);
            }
        
        }



        if($client->trip_type != 'individual'){


            $trip = $trip->where('id', $client->trip_id)->first();

            $seatsArr = $client->seats_numbers;

            foreach ((array) $seatsArr as $seat) {
                
                $bus->where('num', $seat)
                    ->where('trip_id', $trip->id)->update([

                    'client_id' => $client->id,
                    'client_name' => $client->name,
                    'accomodation' => $client->infos()->pluck('accomodation')

                ]);

            }


            if(count($seatsArr) > (49 - $bus->countSeats($trip->id))){

                return response()->json([
                  "status" => "error",
                  "message" => "There is no enough chairs"

                ]);


            }

            $remain_chairs = $trip->capacity - $bus->countSeats($trip->id);        


            $trip->update([

                'remain_chairs' => $remain_chairs

            ]);

        }

        $client->companion()->delete();
        $comps = isset($request->comps)? $request->comps : null;
        foreach ((array) $comps as $comp) {
            
            Companion::create([

                'client_id' => $client->id,
                'companion' => $comp['companion'],
                'passport_no' => $comp['passport_no'],
                'comp_nationality' => $comp['comp_nationality']

            ]);

        }
        
        $client = $client->with(['infos', 'companion'])->find($client->id);


        return response()->json([

            'status' => 'success',
            'data' => [

                'client' => $client,
                'remain chairs' => $trip->remain_chairs

            ]

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
        $client = Client::find($id);

        if(! $client){

            return response()->json([

                'status' => 'error',
                'message' => 'Client Not Found'

            ]);

        }

        $this->authorize('delete', $client);

        $client->delete();

        return response()->json([

            'status' => 'success',
            'message' => 'Client deleted Successfully'

        ]);

    }
}
