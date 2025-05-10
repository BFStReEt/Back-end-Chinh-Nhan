<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Collaborator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class CollaboratorController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function login(Request $request){
        $val = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);
        if ($val->fails()) {
            return response()->json($val->errors(), 202);
        }
        $collaborators = Collaborator::where('username',$request->username)->first();

        if(isset($collaborators)!=1)
        {
            return response()->json([
                'status' => false,
                'mess' => 'username'
            ]);
        }
        $check =  $collaborators->makeVisible('password');
        if(Hash::check($request->password,$check->password)){

            $success= $collaborators->createToken('Collaborator')->accessToken;
            return response()->json([
                'status' => true,
                'token' => $success,
                'username'=>$collaborators->username
            ]);
        }else {
            return response()->json([
                'status' => false,
                'mess' => 'pass'
            ]);
        }
    }
    public function information(){
        try{
            $id = Auth::guard('collaborator')->user()->id;
            $collaborator = Collaborator::where('id',$id)->first();
            return response()->json([
               'status'=>true,
               'data'=> $collaborator,
            ]);
         }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function index(Request $request)
    {
        try{
            $query=Collaborator::orderBy('id','desc');
            if($request->data == 'undefined' || $request->data =="")
            {
                $list=$query;
            }
            else{
                $list=$query->where('username','like', '%' . $request->data . '%')
                ->orWhere('email','like', '%' . $request->data . '%');
            }
            $collaboratorList=$list->paginate(5);
            return response()->json([
                'status'=>true,
                'collaboratorList'=>$collaboratorList,
            ]);
         }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(),[
                'username' => 'required',
                'password' => 'required',
            ]);
            if($validator->fails()){
                return response()->json([
                    'message'=>'Vui lòng nhập tên đăng nhập và mật khẩu',
                    'errors'=>$validator->errors()
                ],422);
            }
            $check = Collaborator::where('username',$request->username)->first();
            if($check != '')
            {
               return response()->json([
                   'message'=>'Tên đăng nhập bị trùng ,vui lòng nhập lại',
                   'status'=>'false'
               ],202);
            }
            $Collaborators = new Collaborator();
            $Collaborators -> username = $request['username'];
            $Collaborators -> password = Hash::make($request['password']);
            $Collaborators-> email = $request['email'];
            $Collaborators-> display_name = $request['display_name'];
            $Collaborators-> avatar = isset($request['avatar']) ? $request['avatar'] : null;

            $Collaborators-> phone = $request['phone'];
            $Collaborators-> status = $request['status'];
            $Collaborators-> save();
            return response()->json([
                'status' => true,
                ' Collaborators' => $Collaborators,
            ]);
       }
       catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try{
            $id = Auth::guard('collaborator')->user()->id;
            $collaborator = Collaborator::where('id',$id)->first();
            return response()->json([
               'status'=>true,
               'data'=> $collaborator,
            ]);
         }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            $Collaborator =  Collaborator::where('id',$id)->first();
            if(!isset($Collaborator)){
                return response()->json([
                    'message'=>'name',
                    'status'=>'false'
                ],202);
            }


            $Collaborator->email = $request['email'] ? $request['email']: $Collaborator->email ;
            $Collaborator->display_name = $request['name'] ? $request['name']: $Collaborator->display_name ;
            $Collaborator->phone = $request['phone'] ? $request['phone']:$Collaborator->phone;
            $Collaborator->status = $request['status'] ? $request['status']:$Collaborator->status;

            $Collaborator->save();
            return response()->json([
                'status' => true,
                'userAdmin' => $Collaborator,
            ]);
        }
        catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try{
            Collaborator::where("id", $id)->delete();
            return response()->json([
                'status' => true
            ]);
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
