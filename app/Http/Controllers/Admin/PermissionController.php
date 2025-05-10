<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\GroupPermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class PermissionController extends Controller
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
                'action'=>'show all permission',
                'cat'=>'permission',
            ]);
            if(Gate::allows('THÔNG TIN QUẢN TRỊ.Quyền hạn.manage')){
                $permissions=Permission::all()->groupBy(function ($permission){
                    return explode('.',$permission->slug)[0];
                })->map(function ($group) {
                    return $group->groupBy(function ($permission) {
                        return explode('.', $permission->slug)[1];
                    });
                });
                return response()->json([
                    'status'=>true,
                    'permissions'=>$permissions
                ]);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }
        catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function showPermission(){
        $permissions=Permission::all()->groupBy(function ($permission){
            return explode('.',$permission->slug)[0];
        })->map(function ($group) {
            return $group->groupBy(function ($permission) {
                return explode('.', $permission->slug)[1];
            });
        });
        $groupPermission=[];
        foreach($permissions as $key=> $permission){

            $childPermission=[];
            foreach($permission as $key1=> $per){
                $childPermission[]=[
                    'keyChild'=>$key1,
                    'ChildPermission'=>$per

                ];
            }
            $groupPermission[]=[
                'keyGroup'=>$key,
                'groupPermission'=>$childPermission
            ];

        }
        return  $groupPermission;
        return response()->json([
            'status'=>true,
            'permissions'=>$permissions
        ]);

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
                'action'=>'add a permission',
                'cat'=>'permission',
            ]);
            if(Gate::allows('THÔNG TIN QUẢN TRỊ.Quyền hạn.add')){
                Permission::create([
                    'name'=>$request->input('permissionName'),
                    'slug'=>$request->input('parentCate').'.'.$request->input('childCate').'.'.$request->input('permissionName'),


                ]);
                return response()->json([
                    'status'=>true,
                    'message'=>'create Permission success'
                ]);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
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
    public function edit(Request $request,string $id)
    {
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'edit a permission',
                'cat'=>'permission',
            ]);

            // $permissions=Permission::all()->groupBy(function($permission){
            //     return explode('.', $permission->slug)[0];
            // });
            if(Gate::allows('THÔNG TIN QUẢN TRỊ.Quyền hạn.edit')){
                $permission=Permission::find($id);
                return response()->json([
                    'status'=>true,
                    // 'permissions'=>$permissions,
                    'permission'=>$permission,

                ]);
                return response()->json([
                    'status'=>true,
                    'message'=>'create Permission success'
                ]);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }
        catch(\Exception $e){
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
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'update a permission',
                'cat'=>'permission',
            ]);

            if(Gate::allows('THÔNG TIN QUẢN TRỊ.Quyền hạn.update')){
                Permission::where('id', $id)->update([
                    'name'=>$request->input('name'),
                    'slug'=>$request->input('slug'),
                    'description'=>$request->input('description'),
                    'groupPermission'=>$request->input('groupPermission')
                ]);
                return response()->json([
                    'status'=>true,
                    'message'=>'Update Permission success'
                ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
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
    public function destroy(Request $request,string $id)
    {
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'update a permission',
                'cat'=>'permission',
            ]);
            if(Gate::allows('THÔNG TIN QUẢN TRỊ.Quyền hạn.del')){
                Permission::where('id', $id)->delete();
                return response()->json([
                    'status'=>true,
                    'message'=>'Delete Permission success'
                ]);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }
        catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
