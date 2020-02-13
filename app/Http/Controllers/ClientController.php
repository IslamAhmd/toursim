<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Client;
use App\ClientInfo;
use App\Trip;
use Validator;


class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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
            'total_rooms' => 'integer|required_unless:trip_type,dayuse',
            'adult' => 'integer',
            'child' => 'integer',
            'infant' => 'integer',
            'total_people' => 'integer',
            // 'seats_no' => 'integer|required_unless:trip_type,individual',
            'extra_seats' => 'integer|required_unless:trip_type,individual',
            // 'total_seats' => 'integer|required_unless:trip_type,individual',
            'seats_numbers.*.seat' => 'integer|required_unless:trip_type,individual',
            'booking' => 'required',
            'seats' => 'required',
            'status' => 'required',
            'invoice_num' => 'required|integer',
            'notes' => 'required',
            'dests.*.name' => 'required',
            'dests.*.arrival_date' => 'date',
            'dests.*.departure_date' => 'date',
            'dests.*.accomodation' => 'required',
            'dests.*.room_category' => 'required',
            'dests.*.meal_plan' => 'required',

        ];

        $messages = [

            'required' => 'هذا الحقل مطلوب',
            'exists' => 'هذه الرحله غير موجوده',
            'date' => 'قيمة هذا الحقل لابد ان تكون تاريخا',
            'integer' => 'قيمة هذا الحقل يجب ان تكون عددا صحيحا',
            'single.required_unless' => 'هذا الحقل مطلوب اذا كانت الرحله من هذا النوع',
            'double.required_unless' => 'هذا الحقل مطلوب اذا كانت الرحله من هذا النوع',
            'triple.required_unless' => 'هذا الحقل مطلوب اذا كانت الرحله من هذا النوع',
            'quad.required_unless' => 'هذا الحقل مطلوب اذا كانت الرحله من هذا النوع',
            'total_rooms.required_unless' => 'هذا الحقل مطلوب اذا كانت الرحله من هذا النوع',
            'extra_seats.required_unless' => 'هذا الحقل مطلوب اذا كانت الرحله من هذا النوع',
            'seats_numbers.*.seat.required_unless' => 'هذا الحقل مطلوب اذا كانت الرحله من هذا النوع',

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
        $client->trip_name = Trip::where('id', $client->trip_id)->first()->name;
        $client->save();

        $dests = $request->dests;
        foreach ($dests as $dest) {
            ClientInfo::create([

                'dest_name' => $dest['name'],
                'arrival_date' => isset($dest['arrival_date'])? $dest['arrival_date']:null,
                'departure_date' => isset($dest['departure_date'])? $dest['departure_date']:null,
                'accomodation' => $dest['accomodation'],
                'room_category' => $dest['room_category'],
                'meal_plan' => $dest['meal_plan']

            ]);
        }

        $client = $client->with('infos')->find($client->id);

        return response()->json([

            'status' => 'success',
            'data' => $client

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
            'total_rooms' => 'integer',
            'adult' => 'integer',
            'child' => 'integer',
            'infant' => 'integer',
            'total_people' => 'integer',
            'seats_no' => 'integer',
            'extra_seats' => 'integer',
            'total_seats' => 'integer',
            'seats_numbers.*.seat' => 'integer',
            'booking' => 'required',
            'seats' => 'required',
            'status' => 'required',
            'invoice_num' => 'required|integer',
            'notes' => 'required',
            'dests.*.name' => 'required',
            'dests.*.arrival_date' => 'date',
            'dests.*.departure_date' => 'date',
            'dests.*.accomodation' => 'required',
            'dests.*.room_category' => 'required',
            'dests.*.meal_plan' => 'required',

        ];

        $messages = [

            'required' => 'هذا الحقل مطلوب',
            'exists' => 'هذه الرحله غير موجوده',
            'date' => 'قيمة هذا الحقل لابد ان تكون تاريخا',
            'integer' => 'قيمة هذا الحقل يجب ان تكون عددا صحيحا',

        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);
        }

        $client->update($request->except('dests'));
        $client->single = 1 * $request->single;
        $client->double = 2 * $request->double;
        $client->triple = 3 * $request->triple;
        $client->quad = 4 * $request->quad;
        $client->total_rooms = $request->single + $request->double + $request->triple + $request->quad;
        $client->total_people = $client->single + $client->double + $client->triple + $client->quad + $client->child + $client->adult + $client->infant;
        $client->seats_no = $client->total_people - 1;
        $client->total_seats = $client->seats_no + $client->extra_seats;
        $client->trip_name = Trip::where('id', $client->trip_id)->first()->name;
        $client->save();

        $client->infos()->delete();
        $dests = $request->dests;
        foreach ($dests as $dest) {
            ClientInfo::create([

                'dest_name' => $dest['name'],
                'arrival_date' => isset($dest['arrival_date'])? $dest['arrival_date']:null,
                'departure_date' => isset($dest['departure_date'])? $dest['departure_date']:null,
                'accomodation' => $dest['accomodation'],
                'room_category' => $dest['room_category'],
                'meal_plan' => $dest['meal_plan']

            ]);
        }

        $client = $client->with('infos')->find($client->id);

        return response()->json([

            'status' => 'success',
            'data' => $client

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
