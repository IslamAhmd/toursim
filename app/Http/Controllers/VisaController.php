<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Visa;
use App\Company;
use App\Country;
use Illuminate\Support\Facades\Auth;

class VisaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(){

        $this->middleware('Admin');

    }

    public function countries(){

        $countries = Country::get();

        return response()->json([

            'status' => 'success',
            'data' => $countries

        ]);

    }

    public function index()
    {
        $company = Company::where('user_id', Auth::id())->firstOrFail();

        $visas = Visa::with('accepts')->where('company_id', $company->id)->get();

        return response()->json([

            'status' => 'success',
            'data' => $visas

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

            "date" => 'required|date',
            'name' => "required",
            'passport_num' => "required",
            'nationality' => "required",
            'contact_num' => "required",
            'travel_agency' => 'required',
            'country' => 'required',
            'type' => 'required',
            'entries_num' => 'required',
            "submitting" => 'required|date',
            "collecting" => 'required|date',
            'duration_stay' => 'required',
            'company_id' => 'exists:companies,id'

        ];

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);

        }

        $visa = Visa::create($request->all());

        return response()->json([

            'status' => 'success',
            'data' => $visa

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
        $company = Company::where('user_id', Auth::id())->firstOrFail();

        $visa = Visa::where('company_id', $company->id)->find($id);

        if(! $visa){
            return response()->json([

                'status' => 'error',
                'message' => 'Visa no found'

            ]);
        }

        if($visa->status == "accepted"){

            $visa->status = "done";

        }

        return response()->json([

            'status' => 'success',
            'data' => $visa

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
        $company = Company::where('user_id', Auth::id())->firstOrFail();

        $visa = Visa::where('company_id', $company->id)->find($id);

        if(! $visa){
            return response()->json([

                'status' => 'error',
                'message' => 'Visa no found'

            ]);
        }

        $rules = [

            "date" => 'required|date',
            'name' => "required",
            'passport_num' => "required",
            'nationality' => "required",
            'contact_num' => "required",
            'travel_agency' => 'required',
            'country' => 'required',
            'type' => 'required',
            'entries_num' => 'required',
            "submitting" => 'required|date',
            "collecting" => 'required|date',
            'duration_stay' => 'required',
            'company_id' => 'exists:companies,id'

        ];

        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);

        }

        $visa->update($request->all());
        

        return response()->json([

            'status' => 'success',
            'data' => $visa

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
        $company = Company::where('user_id', Auth::id())->firstOrFail();

        $visa = Visa::where('company_id', $company->id)->find($id);

        if(! $visa){
            return response()->json([

                'status' => 'error',
                'message' => 'Visa no found'

            ]);
        }

        $visa->delete();


        return response()->json([

            'status' => 'success',
            'message' => 'Visa Deleted Successfully'

        ]);
    }
}
