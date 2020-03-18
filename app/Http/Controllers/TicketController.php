<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Ticket;
use App\TicketDestination;
use App\TicketPassenger;
use Auth;
use App\User;

class TicketController extends Controller
{


    public function __construct(){

        $this->middleware('Admin');

    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($userId)
    {
        $user = User::findOrFail($userId);
        $tickets = Ticket::with(['passenger', 'destination'])->where('user_id', $user->id)->get();

        return response()->json([

            'status' => 'success',
            'data' => $tickets

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

            'issue_date' => 'required|date',
            'issuing_airline' => 'required',
            'issuing_agent' => 'required',
            'business_type' => 'required',
            'booking_reference' => 'required',
            'booking_status' => 'required',
            'ticket_number' => 'required|numeric',
            'ticket_type' => 'required',
            'flight_type' => 'required',
            'flight_direction' => 'required',
            'dests' => 'required',
            'dests.*.from' => 'required',
            'dests.*.to' => 'required',
            'dests.*.departure_date' => 'required|date',
            'first_name' => 'required',
            'last_name' => 'required',
            'natonality' => 'required',
            'mobile' => 'required',
            'base_fare' => 'required|numeric',
            'tax' => 'required|numeric',
            'commission' => 'required|numeric',
            'profite' => 'required|numeric',
            'rate' => 'numeric',
            'status' => 'required',
            'invoice_no' => 'required|numeric',
            'notes' => 'required'

        ];

        $messages = [

            'dests.*.from.required' => 'the destination from is required',
            'dests.*.to.required' => 'the destination to is required',            
            'dests.*.departure_date.required' => 'the destination departure date is required',
            'dests.*.departure_date.date' => 'the destination departure date must be date',

        ];

        if($request->flight_direction === "Round Trip"){

            $rules['dests.*.return_date'] = 'required_if:flight_direction,Round Trip|date|after:dests.*.departure_date';

            $messages['dests.*.return_date.required_if'] = 'the destination return date is required if the Flight Direction is Round Trip';
            $messages['dests.*.return_date.date'] = 'the destination return date must be date';
            $messages['dests.*.return_date.after'] = 'the destination return date must be after departure date';

        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);

        }

        $ticket = Ticket::create($request->only(['issue_date', 'issuing_airline', 'issuing_agent', 'business_type', 'booking_reference', 'booking_status', 'ticket_number', 'ticket_type', 'flight_type', 'flight_direction']));
        $ticket->user_id = Auth::id();
        $ticket->save();

        $dests = $request->dests;
        foreach ((array) $dests as $dest) {
            
            TicketDestination::create([

                'ticket_id' => $ticket->id,
                'from' => $dest['from'],
                'to' => $dest['to'],
                'departure_date' => $dest['departure_date'],
                'return_date' => isset($dest['return_date']) ? $dest['return_date']:null

            ]);

        }

        $passenger = TicketPassenger::create($request->except(['dests', 'issue_date', 'issuing_airline', 'issuing_agent', 'business_type', 'booking_reference', 'booking_status', 'ticket_number', 'ticket_type', 'flight_type', 'flight_direction']));
        $passenger->ticket_id = $ticket->id;
        $passenger->total = $passenger->base_fare + $passenger->tax;
        $passenger->net = $passenger->total - $passenger->commission;
        $passenger->rate = isset($request->rate) ? $request->rate:$passenger->profite + $passenger->net;
        $passenger->save();


        $ticket = $ticket->with(['destination', 'passenger'])->find($ticket->id);

        return response()->json([

            'status' => 'success',
            'data' => $ticket

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
        $ticket = Ticket::with(['passenger', 'destination'])->where('user_id', Auth::id())->findOrFail($id);

        return response()->json([

            'status' => 'success',
            'data' => $ticket

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
        $ticket = Ticket::with(['passenger', 'destination'])->where('user_id', Auth::id())->findOrFail($id);


        $rules = [

            'issue_date' => 'required|date',
            'issuing_airline' => 'required',
            'issuing_agent' => 'required',
            'business_type' => 'required',
            'booking_reference' => 'required',
            'booking_status' => 'required',
            'ticket_number' => 'required|numeric',
            'ticket_type' => 'required',
            'flight_type' => 'required',
            'flight_direction' => 'required',
            'dests' => 'required',
            'dests.*.from' => 'required',
            'dests.*.to' => 'required',
            'dests.*.departure_date' => 'required|date',
            'first_name' => 'required',
            'last_name' => 'required',
            'natonality' => 'required',
            'mobile' => 'required',
            'base_fare' => 'required|numeric',
            'tax' => 'required|numeric',
            'commission' => 'required|numeric',
            'profite' => 'required|numeric',
            'rate' => 'numeric',
            'status' => 'required',
            'invoice_no' => 'required|numeric',
            'notes' => 'required'

        ];

        $messages = [

            'dests.*.from.required' => 'the destination from is required',
            'dests.*.to.required' => 'the destination to is required',            
            'dests.*.departure_date.required' => 'the destination departure date is required',
            'dests.*.departure_date.date' => 'the destination departure date must be date',

        ];

        if($request->flight_direction === "Round Trip"){

            $rules['dests.*.return_date'] = 'required_if:flight_direction,Round Trip|date|after:dests.*.departure_date';

            $messages['dests.*.return_date.required_if'] = 'the destination return date is required if the Flight Direction is Round Trip';
            $messages['dests.*.return_date.date'] = 'the destination return date must be date';
            $messages['dests.*.return_date.after'] = 'the destination return date must be after departure date';

        }


        $validator = Validator::make($request->all(), $rules, $messages);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);

        }

        $ticket->update($request->only(['issue_date', 'issuing_airline', 'issuing_agent', 'business_type', 'booking_reference', 'booking_status', 'ticket_number', 'ticket_type', 'flight_type', 'flight_direction']));
        $ticket->user_id = Auth::id();
        $ticket->save();


        $ticket->destination()->delete();
        $dests = $request->dests;
        foreach ((array) $dests as $dest) {
            
            TicketDestination::create([

                'ticket_id' => $ticket->id,
                'from' => $dest['from'],
                'to' => $dest['to'],
                'departure_date' => $dest['departure_date'],
                'return_date' => isset($dest['return_date']) ? $dest['return_date']:null

            ]);

        }


        $ticket->passenger()->delete();
        $passenger = TicketPassenger::create($request->except(['dests', 'issue_date', 'issuing_airline', 'issuing_agent', 'business_type', 'booking_reference', 'booking_status', 'ticket_number', 'ticket_type', 'flight_type', 'flight_direction']));
        $passenger->ticket_id = $ticket->id;
        $passenger->total = $passenger->base_fare + $passenger->tax;
        $passenger->net = $passenger->total - $passenger->commission;
        $passenger->rate = isset($request->rate) ? $request->rate:$passenger->profite + $passenger->net;
        $passenger->save();


        $ticket = $ticket->with(['destination', 'passenger'])->find($ticket->id);

        return response()->json([

            'status' => 'success',
            'data' => $ticket

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
        $ticket = Ticket::with(['passenger', 'destination'])->where('user_id', Auth::id())->findOrFail($id);
        
        $ticket->delete();

        return response()->json([

            'status' => 'success',
            'message' => 'Ticket Deleted Successfully'

        ]);

    }
}
