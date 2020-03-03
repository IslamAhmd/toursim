<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Visa;
use App\VisaAccept;
use Validator;
use App\Company;
use Illuminate\Support\Facades\Auth;



class VisaAcceptController extends Controller
{

    public function __construct(){

        $this->middleware('Admin');

    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = Company::where('user_id', Auth::id())->firstOrFail();
        $accepts = VisaAccept::where('company_id', $company->id)->get();

        return response()->json([

            'status' => 'success',
            'data' => $accepts

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

            'visa_id' => 'required|exists:visas,id',
            'issue_date' => 'required|date',
            'validity_from' => 'required|date',
            'validity_to' => 'required|date',
            'number' => 'required|integer',
            'payment' => 'required|integer',
            'status' => 'required',
            'invoice_no' => 'required|integer'

        ];

        $messages = [

            'visa_id.exists' => 'this visa does not exist'

        ];
 
        $validator = Validator::make($request->all(), $rules, $messages);
        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);

        }

        if(Visa::where('id', $request->visa_id)->where('status', 'accepted')->exists()){

            $accept = VisaAccept::create($request->all());
            $accept->company_id = Company::where('user_id', Auth::id())->first()->id;
            $accept->save();

            return response()->json([

                'status' => 'success',
                'data' => $accept

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
        $company = Company::where('user_id', Auth::id())->firstOrFail();
        $accept = VisaAccept::where('company_id', $company->id)->find($id);

        return response()->json([

            'status' => 'success',
            'data' => $accept

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
        $accept = VisaAccept::where('company_id', $company->id)->find($id);
                
        $rules = [

            'visa_id' => 'required|exists:visas,id',
            'issue_date' => 'required|date',
            'validity_from' => 'required|date',
            'validity_to' => 'required|date',
            'number' => 'required|integer',
            'payment' => 'required|integer',
            'status' => 'required',
            'invoice_no' => 'required|integer'

        ];

        $messages = [

            'visa_id.exists' => 'this visa does not exist'

        ];
 
        $validator = Validator::make($request->all(), $rules, $messages);
        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);

        }

        if(Visa::where('id', $request->visa_id)->where('status', 'accepted')->exists()){

            $accept->update($request->all());
            $accept->company_id = $company->id;
            $accept->save();

            return response()->json([

                'status' => 'success',
                'data' => $accept

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
        $company = Company::where('user_id', Auth::id())->firstOrFail();
        $accept = VisaAccept::where('company_id', $company->id)->find($id);
        
        $accept->delete();

        return response()->json([

            'status' => 'success',
            'message' => 'Visa Accept deleted successfully'

        ]);
    }
}
