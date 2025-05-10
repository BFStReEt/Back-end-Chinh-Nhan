<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CateParentPer;
use Illuminate\Support\Facades\Auth;
use Gate;
class CateParentPerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $CateParentPer=CateParentPer::where('parentId',0)->orderBy('id','asc')->get();
            return response()->json([
                'status'=>true,
                'data'=>$CateParentPer
            ]);
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage,
            ];
            return response()->json($response, 500);
        }
    }
    public function showChildCategory($parent){
        try{
            $CateParentPer=CateParentPer::where('parentId',$parent)->orderBy('id','asc')->get();
            return response()->json([
                'status'=>true,
                'data'=>$CateParentPer
            ]);
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage,
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
        try{
            $CateParentPer=CateParentPer::create([
                'name'=>$request->input('name'),
            ]);
            return response()->json([
                'status'=>true,
                'message'=>'create CateParentPer success'
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
            $CateParentPer=CateParentPer::where('id',$id)->first();
            return response()->json([
                'status'=>true,
                'CateParentPer'=>$CateParentPer
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
            $CateParentPer=CateParentPer::find($id);
            $CateParentPer->update([
                'name'=>$request->input('name'),
            ]);

            return response()->json([
                'status'=>true,
                'message'=>'update Role success'
            ]);
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $CateParentPer=CateParentPer::find($id);
            $CateParentPer->delete();
            return response()->json([
                'status'=>true,
                'message'=>'Delete CateParentPer success'
            ]);
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
