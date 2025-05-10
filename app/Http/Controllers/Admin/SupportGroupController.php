<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class SupportGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        DB::table('adminlogs')->insert([
            'admin_id' => Auth::guard('admin')->user()->id,
            'time' =>  $stringTime,
            'ip'=> $request->ip(),
            'action'=>'show all supportGroup',
            'cat'=>'supportGroup',
        ]);
        if(Gate::allows('QUẢN LÝ SUPPORT.Quản lý nhóm.manage')){
            if($request->data == 'undefined' || $request->data =="")
            {
                $list = SupportGroup::get();
            }
            else{
                $list = SupportGroup::where("title", 'like', '%' . $request->data . '%')->get();
            }
            return response()->json([
                'status' => true,
                'data' => $list
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
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
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'add a supportGroup',
                'cat'=>'supportGroup',
            ]);
            if(Gate::allows('QUẢN LÝ SUPPORT.Quản lý nhóm.add')){
                $list =new SupportGroup();

                $list->title= $request->title;
                $list->name = $request->name;
                $list->is_default = 0;
                $list->menu_order = 0;
                $list->save();

                return response()->json([
                    'status' => true,
                    'data' => $list
                ]);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ]);
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
    public function edit(Request $request,string $id)
    {
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'add a supportGroup',
                'cat'=>'supportGroup',
            ]);
            if(Gate::allows('QUẢN LÝ SUPPORT.Quản lý nhóm.edit')){
                $supportGroupId = SupportGroup::find($id);
                return response()->json([
                    'status' => true,
                    'data' => $supportGroupId
                ]);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
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
                'action'=>'add a supportGroup',
                'cat'=>'supportGroup',
            ]);
            if(Gate::allows('QUẢN LÝ SUPPORT.Quản lý nhóm.update')){

                $data = $request->all();
                $supportId = SupportGroup::find($id);
                $supportId->title = $data['title'];
                $supportId->name = $data['name'];
                $supportId->save();
                return response()->json([
                    'status' => true,
                    'data' => $supportId
                ]);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status' => true,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,string $id)
    {
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'add a supportGroup',
                'cat'=>'supportGroup',
            ]);
            if(Gate::allows('QUẢN LÝ SUPPORT.Quản lý nhóm.del')){
                $supportId = SupportGroup::find($id)->delete();
                return response()->json([
                    'status' => true
                ]);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status' => true,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function deleteAll(Request $request)
    {
        $arr =$request->data;
        //return $arr;
        try {
            if(Gate::allows('QUẢN LÝ SUPPORT.Quản lý nhóm.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        $supportId = SupportGroup::find($item)->delete();
                    }
                }
                else
                {
                    return response()->json([
                    'status'=>false,
                    ],422);
                }
                return response()->json([
                    'status'=>true,
                ],200);
            }else {
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
}
