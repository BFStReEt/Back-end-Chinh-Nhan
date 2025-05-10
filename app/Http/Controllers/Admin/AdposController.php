<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Adpos;
use App\Models\Advertise;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class AdposController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            if(Gate::allows('QUẢN LÝ ADVERTISE.Quản lý vị trí.manage')){
                if($request->data == 'undefined' || $request->data =="")
                {
                    $list = Adpos::get();
                }
                else{
                    $list = Adpos::where("title", 'like', '%' . $request->data . '%')->get();
                }
                $response = [
                    'status' => 'success',
                    'list' => $list
                ];
                return response()->json($response, 200);
            }else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
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
        try {
            if(Gate::allows('QUẢN LÝ ADVERTISE.Quản lý vị trí.add')){
                $adpos = new Adpos();
                $adpos->fill([
                    'name' => $request->input('name'),
                    'title' => $request->input('title'),
                    'width' => $request->input('width'),
                    'height' => $request->input('height'),
                    'n_show' => $request->input('show'),
                    'description' => $request->input('description'),
                    'display' => $request->input('display'),
                    'menu_order' => 0
                ])->save();
                $response = [
                    'status' => true,
                    'adpos' => $adpos
                ];
                return response()->json($response, 200);
            }else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
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
            if(Gate::allows('QUẢN LÝ ADVERTISE.Quản lý vị trí.edit')){
                $list = Adpos::find($id);
                return response()->json([
                    'status'=> true,
                    'list' => $list
                ]);
            }else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
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
        if(Gate::allows('QUẢN LÝ ADVERTISE.Quản lý vị trí.update')){
            $listAdpos = Adpos::Find($id);
            if( $listAdpos){
                $Advertises=Advertise::where('pos',$listAdpos->name)->get();
                foreach ($Advertises as $advertise) {
                    $advertise->pos = $request->input('name');
                    $advertise->save();
                }
            }
            $listAdpos->fill([
                'name' => $request->input('name'),
                'cat_id' =>$request->input('cat_id'),
                'title' => $request->input('title'),
                'width' => $request->input('width'),
                'height' => $request->input('height'),
                'n_show' => $request->input('show'),
                'description' => $request->input('description'),
                'display'  => $request->input('display'),
                'menu_order' => 1
            ])->save();
            return response()->json([
                'status'=>true
            ]);
        }else {
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
            if(Gate::allows('QUẢN LÝ ADVERTISE.Quản lý vị trí.del')){
                $Adpos=Adpos::where('id_pos',$id)->first();
                if($Adpos){
                    Advertise::where('pos',$Adpos->name)->delete();
                }
                $list = Adpos::Find($id)->delete();
            
                return response()->json([
                    'status'=> true,
                ]);
            }else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage
            ];

            return response()->json($response, 500);
        }
    }
    public function deleteAll(Request $request)
    {
        $arr =$request->data;
        try {
            if(Gate::allows('QUẢN LÝ ADVERTISE.Quản lý vị trí.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        $Adpos=Adpos::where('id_pos',$item)->first();
                        if($Adpos){
                            Advertise::where('pos',$Adpos->name)->delete();
                        }
                        Adpos::Find($item)->delete();
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
