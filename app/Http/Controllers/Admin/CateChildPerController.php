<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CateChildPer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class CateChildPerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $CateChildPer=CateChildPer::orderBy('id','asc')->get();
            return response()->json([
                'status'=>true,
                'data'=>$CateChildPer
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
            $CateChildPer=CateChildPer::create([
                'name'=>$request->input('name'),
            ]);
            return response()->json([
                'status'=>true,
                'message'=>'create CateChildPer success'
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
            $CateChildPer=CateChildPer::where('id',$id)->first();
            return response()->json([
                'status'=>true,
                'CateChildPer'=>$CateChildPer
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
            $CateChildPer=CateChildPer::find($id);
            $CateChildPer->update([
                'name'=>$request->input('name'),
            ]);

            return response()->json([
                'status'=>true,
                'message'=>'update CateChildPer success'
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
            $CateChildPer=CateChildPer::find($id);
            $CateChildPer->delete();
            return response()->json([
                'status'=>true,
                'message'=>'Delete CateChildPer success'
            ]);
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
