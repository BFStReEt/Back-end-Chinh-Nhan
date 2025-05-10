<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Support;
use App\Models\SupportGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class SupportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showAllSupport(){
        try{
            $leagues = DB::table('support_group')
            ->join('support', 'support_group.name', '=', 'support.group')
            ->where('support.display',1)
            ->select('support_group.*','support.*')
            ->get();
            if(count($leagues) >= 1){
                foreach($leagues as $support){
                    if(strlen(strstr($support->group, ".")) > 0)
                    {
                        $support->group=str_replace(".",'',$support->group);
                    }
                    if($support->group){
                        $data[$support->group][] = [
                            'title' => $support->title,
                            'email' => $support->email,
                            'phone' => $support->phone
                        ];
                    }
                }
            }
            return response()->json([
                'status' => true,
                'data' => $data ?? []
            ]);
        }catch(Exception $e){
              return response()->json([
                'status' => false,
                'error' => $e->getMessage(),
              ]);
        }

    }
    public function index(Request $request)
    {
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        DB::table('adminlogs')->insert([
            'admin_id' => Auth::guard('admin')->user()->id,
            'time' =>  $stringTime,
            'ip'=> $request->ip(),
            'action'=>'show all support',
            'cat'=>'support',
        ]);
        if(Gate::allows('QUẢN LÝ SUPPORT.Quản lý support.manage')){
        $group=$request['group'];
        $query=Support::orderBy('id','desc');
        if($request->data == 'undefined' || $request->data =="")
        {
            $list = $query;
        }
        else{
            $list = $query->where("title", 'like', '%' . $request->data . '%');
        }
        if(isset($group)){
            $list=$query->where("group",$group);
        }
        $listSupport=$list->paginate(10);
        $SupportGroup = SupportGroup::get();
        return response()->json([
            'status' => true,
            'data' => $listSupport,
            'SupportGroup'=> $SupportGroup
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
                'action'=>'add a support',
                'cat'=>'support',
            ]);
            if(Gate::allows('QUẢN LÝ SUPPORT.Quản lý support.add')){
            $support = new Support();

            $support->	title = $request->title;
            $support->group =  $request->group;
            $support->email = $request->email;
            $support->phone = $request->phone;
            $support->name = $request->name;
            $support->type = $request->type;
            $support->menu_order = 0;
            $support->display = 1;
            $support->adminid = 1;
            $support->lang = "vi";
            $support->save();
            return response()->json([
                'status' => true,
                'data' => $support,
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        }catch(\Throwable $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

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
                'action'=>'edit a support',
                'cat'=>'support',
            ]);
            if(Gate::allows('QUẢN LÝ SUPPORT.Quản lý support.edit')){

            $supportId = Support::find($id);
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
        }catch(\Throwable $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
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
                'action'=>'update a support',
                'cat'=>'support',
            ]);
            if(Gate::allows('QUẢN LÝ SUPPORT.Quản lý support.update')){

            $support = Support::Find($id);
            $support->title = $request->title;
            $support->group =  $request->group;
            $support->email = $request->email;
            $support->phone = $request->phone;
            $support->name = $request->name;
            $support->type = $request->type;
            $support->menu_order = 0;
            $support->display = 1;
            $support->adminid =1;
            $support->lang = "vi";
            $support->save();
            return response()->json([
                'status' => true,
                'data' => $support,
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        }catch(\Throwable $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
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
                'action'=>'delete a support',
                'cat'=>'support',
            ]);

            if(Gate::allows('QUẢN LÝ SUPPORT.Quản lý support.del')){
                $list = Support::Find($id)->delete();
                return response()->json([
                    'status' => true,
                ]);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
       }catch(\Throwable $e){
        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
        ]);
    }
    }
    public function deleteAll(Request $request)
    {
        $arr =$request->data;
        //return $arr;
        try {
            if(Gate::allows('QUẢN LÝ SUPPORT.Quản lý support.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        $list = Support::Find($item)->delete();
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
