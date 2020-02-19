<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Client;
use App\ClientInfo;
use App\Trip;
use App\Bus;
use Validator;


class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(){

        // $this->middleware('SuperOrAdmin')->only(['index', 'getCompany', 'show']);
        $this->middleware('Admin');
        // $this->middleware('SuperAdmin')->only(['destroy', 'disable']);

    }


    public function getBuses($id){

        $buses = Bus::where('trip_id', $id)->get();

        return response()->json([

            'status' => 'success',
            'data' => $buses

        ]);

    }


    public function index()
    {
        $groupsClients = Client::with('infos')->where('trip_type', 'groups')->get();
        $dayuseClients = Client::with('infos')->where('trip_type', 'dayuse')->get();
        $individualClients = Client::with('infos')->where('trip_type', 'individual')->get();


        return response()->json([

            'status' => 'success',
            'data' => [

                'groups' => $groupsClients,
                'dayuse' => $dayuseClients,
                'individual' => $individualClients

            ]

        ]);
    }

    public function getseats(){

        $clients = Client::get(['id', 'name', 'seats_numbers']);

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

            'trip_type' => 'required',
            'trip_id' => 'exists:trips,id',
            'date' => 'required|date',
            'name' => 'required',
            'contact_num' => 'required|integer',
            'category' => 'required',
            'travel_agency' => 'required',
            'single' => 'integer',
            'double' => 'integer',
            'triple' => 'integer',
            'quad' => 'integer',
            // 'total_rooms' => 'integer|required_unless:trip_type,dayuse',
            'adult' => 'integer',
            'child' => 'integer',
            'infant' => 'integer',
            // 'total_people' => 'integer',
            // 'seats_no' => 'integer|required_unless:trip_type,individual',
            'extra_seats' => 'integer',
            // 'total_seats' => 'integer|required_unless:trip_type,individual',
            'seats_numbers' => 'required',
            'seats_numbers.*' => 'integer|exists:buses,num',
            'booking' => 'required',
            'seats' => 'required',
            'status' => 'required',
            'invoice_num' => 'required|integer',
            'notes' => 'required',
            'dests' => 'required_unless:trip_type,dayuse',
            'dests.*.name' => 'required_if:trip_type,individual',
            'dests.*.arrival_date' => 'date|required_if:trip_type,individual',
            'dests.*.departure_date' => 'date|required_if:trip_type,individual',
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

        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);
        }

    

        $client = Client::create($request->except('dests'));
        $client->single = 1 * $request->single;
        $client->double = 2 * $request->double;
        $client->triple = 3 * $request->triple;
        $client->quad = 4 * $request->quad;
        $client->total_rooms = $request->single + $request->double + $request->triple + $request->quad;
        $client->total_people = $client->single + $client->double + $client->triple + $client->quad + $client->child + $client->adult + $client->infant;

        $client->seats_no = $client->total_people - $client->infant;
        $client->total_seats = $client->seats_no + $client->extra_seats;
        // $client->trip_name = Trip::where('id', $request->trip_id)->first()->name;
        $client->save();


        if($client->trip_type == 'groups' || $client->trip_type == 'individual'){

            $trip = new Trip;

            if($client->trip_type == 'groups'){

                $tripName = $trip->where('id', $client->trip_id)->first()->name;

            }

            $dests = $request->dests;

            foreach ((array) $dests as $dest) {
                ClientInfo::create([

                    'dest_name' => isset($dest['name'])? $dest['name'] : $tripName,
                    'client_id' => $client->id,
                    'arrival_date' => isset($dest['arrival_date'])? $dest['arrival_date']:null,
                    'departure_date' => isset($dest['departure_date'])? $dest['departure_date']:null,
                    'accomodation' => $dest['accomodation'],
                    'room_category' => $dest['room_category'],
                    'meal_plan' => $dest['meal_plan']

                ]);
            }

        }

        $trip = new Trip;

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

            $bus = new Bus;

            
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
        

        $client = $client->with(['infos'])->find($client->id);

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
        $client = Client::with('infos')->find($id);

        if(! $client){

            return response()->json([

                'status' => 'error',
                'message' => 'Client Not Found'

            ]);

        }

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

        $rules = [

            'trip_type' => 'required',
            'trip_id' => 'exists:trips,id',
            'date' => 'required|date',
            'name' => 'required',
            'contact_num' => 'required|integer',
            'category' => 'required',
            'travel_agency' => 'required',
            'single' => 'integer',
            'double' => 'integer',
            'triple' => 'integer',
            'quad' => 'integer',
            // 'total_rooms' => 'integer|required_unless:trip_type,dayuse',
            'adult' => 'integer',
            'child' => 'integer',
            'infant' => 'integer',
            // 'total_people' => 'integer',
            // 'seats_no' => 'integer|required_unless:trip_type,individual',
            'extra_seats' => 'integer',
            // 'total_seats' => 'integer|required_unless:trip_type,individual',
            'seats_numbers' => 'required',
            'seats_numbers.*' => 'integer|exists:buses,num',
            'booking' => 'required',
            'seats' => 'required',
            'status' => 'required',
            'invoice_num' => 'required|integer',
            'notes' => 'required',
            'dests' => 'required_unless:trip_type,dayuse',
            'dests.*.name' => 'required_if:trip_type,individual',
            'dests.*.arrival_date' => 'date|required_if:trip_type,individual',
            'dests.*.departure_date' => 'date|required_if:trip_type,individual',
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
        }


        $client->update($request->except('dests'));
        $client->single = 1 * $request->single;
        $client->double = 2 * $request->double;
        $client->triple = 3 * $request->triple;
        $client->quad = 4 * $request->quad;
        $client->total_rooms = $request->single + $request->double + $request->triple + $request->quad;
        $client->total_people = $client->single + $client->double + $client->triple + $client->quad + $client->child + $client->adult + $client->infant;
        $client->seats_no = $client->total_people - $client->infant;
        $client->total_seats = $client->seats_no + $client->extra_seats;
        $client->save();

        // return $oldSeatsTotal;

        if($client->trip_type == 'groups' || $client->trip_type == 'individual'){


            if($client->trip_type == 'groups'){

                $tripName = $trip->where('id', $client->trip_id)->first()->name;

            }

            $client->infos()->delete();
            $dests = $request->dests;
            foreach ((array) $dests as $dest) {
                ClientInfo::create([

                    'dest_name' => isset($dest['name'])? $dest['name'] : $tripName,
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
        
        $client = $client->with(['infos'])->find($client->id);


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

        $client->delete();

        return response()->json([

            'status' => 'success',
            'message' => 'Client deleted Successfully'

        ]);

    }
}
