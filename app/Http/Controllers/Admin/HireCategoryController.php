<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HireCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class HireCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function showHireCategory(){
        try {
            $hireCategory=HireCategory::where('status',1)->orderBy('id','desc')->get();
            return response()->json([
                'status'=>true,
                'data'=> $hireCategory
            ]);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }
    }
    public function index(Request $request)
    {
        try{
            if(Gate::allows('Quản lý tuyển dụng.Danh mục tuyển dụng.manage')){
                $query=HireCategory::orderBy('id','desc');
                if($request->data == 'undefined' || $request->data =="")
                {
                    $list = $query;
                }
                else{
                    $list = $query->where("title", 'like', '%' . $request->data . '%');
                }
                $hireCategory=$list->get();
                return response()->json([
                    'status'=>true,
                    'data'=> $hireCategory
                ]);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];

            return response()->json( $response, 500 );
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
            if(Gate::allows('Quản lý tuyển dụng.Danh mục tuyển dụng.add')){
            $hireCategory=new HireCategory();
            $hireCategory->title=$request->title;
            $hireCategory->name=$request->name;
            $hireCategory->slug=$request->slug;
            $hireCategory->status=$request->status;
            $hireCategory->save();
            return response()->json([
                'status'=>true,
                'mess'=>'success hireCategory'
            ]);
        }else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];

            return response()->json( $response, 500 );
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
        try {
            if(Gate::allows('Quản lý tuyển dụng.Danh mục tuyển dụng.edit')){
            $hireCategory=HireCategory::where('id',$id)->first();
            return response()->json([
                'status'=>true,
                'data'=>$hireCategory
            ]);
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            if(Gate::allows('Quản lý tuyển dụng.Danh mục tuyển dụng.update')){
            $hireCategory=HireCategory::where('id',$id)->first();
            $hireCategory->title=$request->title;
            $hireCategory->name=$request->name;
            $hireCategory->slug=$request->slug;
            $hireCategory->status=$request->status;
            $hireCategory->save();
            return response()->json([
                'status'=>true,
                'mess'=>'update HireCategory'
            ]);
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

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            if(Gate::allows('Quản lý tuyển dụng.Danh mục tuyển dụng.del')){
                $hireCategory=HireCategory::where('id',$id)->first();
                if($hireCategory) {
                    $hireCategory->delete();
                }
                return response()->json([
                    'status'=>true,
                    'mess'=>'delete HireCategory'
                ]);
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
    public function deleteAll(Request $request)
    {
        $arr =$request->data;
        try {

            if(Gate::allows('Quản lý tuyển dụng.Danh mục tuyển dụng.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        $hireCategory=HireCategory::where('id',$item)->first();
                        if($hireCategory) {
                            $hireCategory->delete();
                        }
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
