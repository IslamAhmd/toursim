<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Company;
use Illuminate\Support\Facades\Auth;
use Validator;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = Company::get();

        return response()->json([

            'status' => 'success',
            'data' => $companies

        ]);
    }


    // get companies id and name
    public function getCompany(){

        $companies = Company::get(['id', 'name']);

        return response()->json([

            'status' => 'success',
            'data' => $companies

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

            'name' => 'required|unique:companies',
            'address' => 'required',
            'telephone' => 'required',
            'mobile' => 'required',
            'fax' => 'required',
            'oficial_mail' => 'required',
            'finance_mail' => 'required',
            'operation_mail' => 'required',
            'tax_card' => 'required',
            'commercial_register' => 'required',
            'licence_num' => 'required',
            'manager' => 'required',
            'manager_phone' => 'required',

        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);
        }

        $company = Company::create($request->all());
        $company->user_id = Auth::id();
        $company->save();

        return response()->json([

            'status' => 'success',
            'data' => $company

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
        $company = Company::find($id);

        if(! $company){

            return response()->json([

                'status' => 'error',
                'message' => 'Company not found'

            ]);


        }

        return response()->json([

            'status' => 'success',
            'data' => $company

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
        $company = Company::find($id);

        if(! $company){

            return response()->json([

                'status' => 'error',
                'message' => 'Company not found'

            ]);


        }

        $rules = [

            'name' => "required|unique:companies,name,id",
            'address' => 'required',
            'telephone' => 'required',
            'mobile' => 'required',
            'fax' => 'required',
            'oficial_mail' => 'required|email',
            'finance_mail' => 'required|email',
            'operation_mail' => 'required|email',
            'tax_card' => 'required',
            'commercial_register' => 'required',
            'licence_num' => 'required',
            'manager' => 'required',
            'manager_phone' => 'required',

        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);
        }

        $company->update($request->all());
        $company->user_id = Auth::id();
        $company->save();

        return response()->json([

            'status' => 'success',
            'data' => $company

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
        $company = Company::find($id);
        
        if(! $company){

            return response()->json([

                'status' => 'error',
                'message' => 'Company not found'

            ]);


        }

        $company->delete();

        return response()->json([

            'status' => 'success',
            'message' => 'Company deleted Successfully'

        ]);


    }
}
