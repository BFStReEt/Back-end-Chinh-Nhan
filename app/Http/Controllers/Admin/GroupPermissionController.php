<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GroupPermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class GroupPermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        try{
            $groupPermission=GroupPermission::orderBy('id','asc')->get();
            return response()->json([
                'status'=>true,
                'groupPermission'=>$groupPermission
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
            GroupPermission::create([
                'name'=>$request->input('name'),
                'slug'=>$request->input('slug'),
                'description'=>$request->input('description'),
                'parentId'=>$request->input('parentId')

            ]);
            return response()->json([
                'status'=>true,
                'message'=>'create Permission success'
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
