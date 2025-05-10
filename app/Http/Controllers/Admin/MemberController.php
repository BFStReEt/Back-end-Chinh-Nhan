<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\OrderSum;
use App\Models\OrderAddress;
use App\Models\OrderDetail;
use App\Models\OrderStatus;
use Gate;
class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{


            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'show all member',
                'cat'=>'member',
            ]);
            $offset = $request->page ? $request->page : 1 ;
            // $idAdmin = Auth::guard('admin')->user()->id;

            // $AdminDepartment = Admin::find($idAdmin);

            if(Gate::allows('QUẢN LÝ THÀNH VIÊN.Quản lý thành viên.manage')){
            $query=Member::with('orderSum')->orderBy('id','DESC');
            if(empty($request->input('data'))||$request->input('data')=='undefined' ||$request->input('data')==''){

                $query=$query;
            }
            else{
                $query=$query->where("username", 'like', '%' . $request->input('data') . '%')
                    ->orWhere('email', 'like', '%' . $request->input('data') . '%');
            }
            $countMember=count($query->get());

            $members=$query->limit(10)
            ->offset(($offset-1)*10)->get();
            //return $members;
            $listMember=[];
            foreach($members as $member){
                $orderPoints=OrderSum::where('mem_id',$member->id)->sum('accumulatedPoints');
                $accumulatedPoints=OrderSum::where('mem_id',$member->id)->sum('accumulatedPoints_1');
                $listMember[]=[
                    'id'=>$member->id,
                    'username'=>$member->username,
                    'email'=>$member->email,
                    'full_name'=>$member->full_name,
                    'phone'=>$member->phone,
                    'gender'=>$member->gender,
                    'dateOfBirth'=>$member->dateOfBirth,
                    'order_sum'=>count($member->orderSum)>0 ? 1:0,
                    'orderPoints'=>$orderPoints,
                    'accumulatedPoints'=>$accumulatedPoints,
                    'created_at'=>$member->created_at
                ];
            }

            return response()->json([
                'status'=>true,
                'data'=>$listMember,
                'countMember'=>$countMember
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];

            return response()->json($response, 500);
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
        //
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
    public function edit(Request $request,string $id)
    {
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'edit all member',
                'cat'=>'member',
            ]);
            if(Gate::allows('QUẢN LÝ THÀNH VIÊN.Quản lý thành viên.edit')){
            $member = Member::select('id','username','email','full_name','provider','phone','gender','dateOfBirth','status','address')->find($id);
            $orderPoints=OrderSum::where('mem_id',$id)->sum('accumulatedPoints');
            $accumulatedPoints=OrderSum::where('mem_id',$id)->sum('accumulatedPoints_1');
            $OrderSum=OrderSum::where('mem_id',$member->id)->get();
            return response()->json([
                'status'=> true,
                'member' => $member,
                'orderPoints'=>$orderPoints,
                'accumulatedPoints'=> $accumulatedPoints,
                //'orderList'=>$OrderSum
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];

            return response()->json($response, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'update a member',
                'cat'=>'member',
            ]);
            if(Gate::allows('QUẢN LÝ THÀNH VIÊN.Quản lý thành viên.update')){
            $listMember = Member::find($id);
            $listMember ->username= $request->username;
            $listMember -> email = $request->email;
            $listMember ->address=$request->address;
            $listMember -> gender = $request->gender;
            $listMember -> phone = $request->phone;
            $listMember ->dateOfBirth=$request->dateOfBirth;
            $listMember -> full_name = $request->fullname;
            $listMember->save();

            return response()->json([
                'status'=> true,
                'member' => $listMember
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];

            return response()->json($response, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            if(Gate::allows('QUẢN LÝ THÀNH VIÊN.Quản lý thành viên.del')){
                $member = Member::Find($id)->delete();
                return response()->json([
                    'status'=>true
                ]);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];

            return response()->json($response, 500);
        }
    }
}
