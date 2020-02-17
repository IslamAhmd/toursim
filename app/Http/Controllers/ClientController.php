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

    public function getBuses($id){

        $buses = Bus::where('trip_id', $id)->get(['client_name', 'num', 'accomodation']);

        return response()->json([

            'status' => 'success',
            'data' => $buses

        ]);

    }


    public function index()
    {
        $clients = Client::with('infos')->get();

        return response()->json([

            'status' => 'success',
            'data' => $clients

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
            'single' => 'integer|required_unless:trip_type,dayuse',
            'double' => 'integer|required_unless:trip_type,dayuse',
            'triple' => 'integer|required_unless:trip_type,dayuse',
            'quad' => 'integer|required_unless:trip_type,dayuse',
            // 'total_rooms' => 'integer|required_unless:trip_type,dayuse',
            'adult' => 'integer',
            'child' => 'integer',
            'infant' => 'integer',
            // 'total_people' => 'integer',
            // 'seats_no' => 'integer|required_unless:trip_type,individual',
            'extra_seats' => 'integer|required_unless:trip_type,individual',
            // 'total_seats' => 'integer|required_unless:trip_type,individual',
            'seats_numbers.*' => 'integer|required_unless:trip_type,individual',
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


        $validator = Validator::make($request->all(), $rules);

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
        $client->trip_name = Trip::where('id', $request->trip_id)->first()->name;
        $client->save();

        $dests = $request->dests;

        foreach ((array) $dests as $dest) {
            ClientInfo::create([

                'dest_name' => isset($dest['name'])? $dest['name'] : null,
                'client_id' => $client->id,
                'arrival_date' => isset($dest['arrival_date'])? $dest['arrival_date']:null,
                'departure_date' => isset($dest['departure_date'])? $dest['departure_date']:null,
                'accomodation' => $dest['accomodation'],
                'room_category' => $dest['room_category'],
                'meal_plan' => $dest['meal_plan']

            ]);
        }

        $trip = Trip::where('id', $client->trip_id)->first();


        $seatsArr = $client->seats_numbers;
        foreach ((array) $seatsArr as $seat) {
            
            Bus::create([

                'client_id' => $client->id,
                'trip_id' => $trip->id,
                'client_name' => $client->name,
                'num' => $seat,
                'accomodation' => $client->infos()->pluck('accomodation')

            ]);

        }

        $bus = new Bus;


        if($bus->countSeats($trip->id) > $trip->capacity){

            return response()->json([
              "status" => "error",
              "message" => "There is no enough chairs"

            ]);


        }

        $remain_chairs = $trip->capacity - $bus->countSeats($trip->id);


        $trip->update([

            'remain_chairs' => $remain_chairs

        ]);
        

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
            'single' => 'integer|required_unless:trip_type,dayuse',
            'double' => 'integer|required_unless:trip_type,dayuse',
            'triple' => 'integer|required_unless:trip_type,dayuse',
            'quad' => 'integer|required_unless:trip_type,dayuse',
            // 'total_rooms' => 'integer|required_unless:trip_type,dayuse',
            'adult' => 'integer',
            'child' => 'integer',
            'infant' => 'integer',
            // 'total_people' => 'integer',
            // 'seats_no' => 'integer|required_unless:trip_type,individual',
            'extra_seats' => 'integer|required_unless:trip_type,individual',
            // 'total_seats' => 'integer|required_unless:trip_type,individual',
            'seats_numbers.*' => 'integer|required_unless:trip_type,individual',
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


        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);
        }

        $oldSeatsTotal = $client->total_seats;


        $client->update($request->except('dests'));
        $client->single = 1 * $request->single;
        $client->double = 2 * $request->double;
        $client->triple = 3 * $request->triple;
        $client->quad = 4 * $request->quad;
        $client->total_rooms = $request->single + $request->double + $request->triple + $request->quad;
        $client->total_people = $client->single + $client->double + $client->triple + $client->quad + $client->child + $client->adult + $client->infant;
        $client->seats_no = $client->total_people - 1;
        $client->total_seats = $client->seats_no + $client->extra_seats;
        $client->save();

        // return $oldSeatsTotal;


        $client->infos()->delete();
        $dests = $request->dests;
        foreach ((array) $dests as $dest) {
            ClientInfo::create([

                'dest_name' => isset($dest['name'])? $dest['name'] : null,
                'client_id' => $client->id,
                'arrival_date' => isset($dest['arrival_date'])? $dest['arrival_date']:null,
                'departure_date' => isset($dest['departure_date'])? $dest['departure_date']:null,
                'accomodation' => $dest['accomodation'],
                'room_category' => $dest['room_category'],
                'meal_plan' => $dest['meal_plan']

            ]);
        }
        

        $trip = Trip::where('id', $client->trip_id)->first();

        Bus::where('client_id', $client->id)->delete();

        $seatsArr = $client->seats_numbers;
        foreach ((array) $seatsArr as $seat) {
            
            Bus::create([

                'client_id' => $client->id,
                'trip_id' => $trip->id,
                'client_name' => $client->name,
                'num' => $seat,
                'accomodation' => $client->infos()->pluck('accomodation')

            ]);

        }

        $bus = new Bus;

        if($bus->countSeats($trip->id) > $trip->capacity){

            return response()->json([
              "status" => "error",
              "message" => "There is no enough chairs"

            ]);


        }


        $remain_chairs = $trip->capacity - $bus->countSeats($trip->id);        


        $trip->update([

            'remain_chairs' => $remain_chairs

        ]);

        
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
