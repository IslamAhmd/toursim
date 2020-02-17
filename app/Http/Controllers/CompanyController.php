<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use App\Hr;
use App\Company;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\File;
use Image;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct(){

        // $this->middleware('SuperOrAdmin')->only(['index', 'getCompany', 'show']);
        $this->middleware('Admin')->only(['store', 'update']);
        $this->middleware('SuperAdmin')->only(['destroy', 'disable']);

    }


    public function index()
    {
        $companies = Company::with('hr')->get();

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
            'UserHR' => 'required|unique:users,name',
            'PasswordHr' => ['required', 'string'],
            'logo' => 'required|image|mimes:jpg,png,jpeg',
            'cover' => 'required|image|mimes:jpg,png,jpeg',

        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);
        }

        $company = Company::create($request->except(['UserHR', 'PasswordHr']));

        \File::makeDirectory(public_path('/data/' . $company->name), 0775, true);

        if($request->hasFile('logo')){
            $photo = $request->file('logo');
            $filename = time() . '-' . $photo->getClientOriginalName();
            $location = public_path('data/' . $company->name . '/' . $filename);

            Image::make($photo)->save($location);
            $company->logo = $filename; 
        }

        if($request->hasFile('cover')){
            $pic = $request->file('cover');
            $fileName = time() . '-' . $pic->getClientOriginalName();
            $loc = public_path('data/' . $company->name . '/' . $fileName);

            Image::make($pic)->save($loc);
            $company->cover = $fileName; 
        }
        $company->user_id = Auth::id();
        $company->save();


        $user = new User;
        $user->name = $request->UserHR;
        $user->password = bcrypt($request->PasswordHR);
        $user->role_id = Role::where('name', 'hr')->first()->id;
        $user->role_name = Role::where('name', 'hr')->first()->name;
        $user->save();

        Hr::create([

            'name' => $user->name,
            'company_id' => $company->id,
            'company_name' => $company->name,
            'user_id' => $user->id

        ]);

        $company = $company->with('hr')->find($company->id);

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
        $company = Company::with('hr')->find($id);

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


        $hrUserId = Hr::where('company_id', $company->id)->first()->user_id;
        // return $hrUserId;
        $user = User::where('id', $hrUserId)->first();

        $userId = $user->id;

        // return $userId;

        $rules = [

            'name' => "required|unique:companies,name,$id",
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
            'logo' => 'required|image|mimes:jpg,png,jpeg',
            'cover' => 'required|image|mimes:jpg,png,jpeg',
            'UserHR' => "required|unique:users,name,$userId",
            'PasswordHr' => ['required', 'string']
        ];

        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){

            return response()->json([
              "status" => "error",
              "errors" => $validator->errors()
            ]);
        }

        $oldName = $company->name;

        $path = public_path() . "/data/" . $oldName . '/' . $company->logo;
        unlink($path);

        $path1 = public_path() . "/data/" . $oldName . '/' . $company->cover;
        unlink($path1);

        $company->update($request->except(['UserHR', 'PasswordHr']));

        \File::move(public_path('/data/' . $oldName), public_path('/data/' . $company->name));

        if($request->hasFile('logo')){

            $photo = $request->file('logo');
            $filename = time() . '-' . $photo->getClientOriginalName();
            $location = public_path('data/' . $company->name . '/' . $filename);

            Image::make($photo)->save($location);
            $company->logo = $filename; 
        }

        if($request->hasFile('cover')){

            $pic = $request->file('cover');
            $fileName = time() . '-' . $pic->getClientOriginalName();
            $loc = public_path('data/' . $company->name . '/' . $fileName);

            Image::make($pic)->save($loc);
            $company->cover = $fileName; 
        }
        $company->user_id = Auth::id();
        $company->save();



        $user->name = $request->UserHR;
        $user->password = bcrypt($request->PasswordHR);
        $user->role_id = Role::where('name', 'hr')->first()->id;
        $user->role_name = Role::where('name', 'hr')->first()->name;
        $user->update();


        $company->hr()->delete();

        Hr::create([

            'name' => $user->name,
            'company_id' => $company->id,
            'company_name' => $company->name,
            'user_id' => $user->id

        ]);

        $company = $company->with('hr')->find($company->id);

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

        
        if(! auth()->user()->role->name == 'super_admin'){

            return response()->json([

                'status' => 'error',
                'message' => "You can't delete the Company"

            ]);


        } 

        $path = public_path() . "/data/" . $company->name . '/' . $company->logo;
        unlink($path);

        $path1 = public_path() . "/data/" . $company->name . '/' . $company->cover;
        unlink($path1);


        $hrUserId = Hr::where('company_id', $company->id)->first()->user_id;
        // return $hrUserId;
        User::where('id', $hrUserId)->delete();
        // User::where('id', $company->user_id)->delete();

        File::deleteDirectory(public_path('/data/' . $company->name));


        $company->delete();

        return response()->json([

        'status' => 'success',
        'message' => 'Company deleted Successfully'

        ]);

        

    }

    public function disable(Request $request, $id){


        $company = Company::find($id);

        if(! $company){

            return response()->json([

                'status' => 'error',
                'message' => 'Company not found'

            ]);


        }

        $company->update([

            'status' => $request->status

        ]);

        $company = $company->with('hr')->find($company->id);

        return response()->json([

            'status' => 'success',
            'data' => $company

        ]);


    }
}
