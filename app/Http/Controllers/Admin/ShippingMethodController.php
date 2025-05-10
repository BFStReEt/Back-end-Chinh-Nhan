<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class ShippingMethodController extends Controller
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
                'action'=>'show all ShippingMethod',
                'cat'=>'shippingMethod',
            ]);
            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Phương thức vận chuyển.manage')){
                if($request->data == 'undefined' || $request->data =="")
                {
                    $shippingMethod=ShippingMethod::orderBy('shipping_id','asc')->paginate(10);
                }
                else{
                    $shippingMethod=ShippingMethod::where("title", 'like', '%' . $request->data . '%')
                        ->orderBy('shipping_id','asc')->paginate(10);
                }

                return response()->json([
                    'status'=>true,
                    'data'=>$shippingMethod
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
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        DB::table('adminlogs')->insert([
            'admin_id' => Auth::guard('admin')->user()->id,
            'time' =>  $stringTime,
            'ip'=> $request->ip(),
            'action'=>'add a ShippingMethod',
            'cat'=>'shippingMethod',
        ]);

        try{
            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Phương thức vận chuyển.add')){
                $shippingMethod=new ShippingMethod();
                $shippingMethod->title=$request->title??null;
                $shippingMethod->name=$request->name??null;
                $shippingMethod->description=$request->description??null;
                $shippingMethod->price=$request->price??0;
                $shippingMethod->discount=$request->discount??0;

                $shippingMethod->display=$request->display??0;
                $shippingMethod->s_type=$request->s_type??0;
                $shippingMethod->s_time=$request->s_time??0;
                $shippingMethod->menu_order=$request->menu_order??0;

                $shippingMethod->date_post=strtotime('now');
                $shippingMethod->date_update=strtotime('now');
                $shippingMethod->save();
                return response()->json([
                        'status'=>true,
                        'data'=>$shippingMethod
                    ]
                );
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }

        }catch(\Exception $e){
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
                'action'=>'show all ShippingMethod',
                'cat'=>'shippingMethod',
            ]);
            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Phương thức vận chuyển.edit')){
                $shippingMethod=ShippingMethod::where('shipping_id',$id)->first();
                return response()->json([
                    'status'=>true,
                    'data'=> $shippingMethod
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
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'update a ShippingMethod',
                'cat'=>'shippingMethod',
            ]);
        try{
            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Phương thức vận chuyển.update')){
                $shippingMethod=ShippingMethod::where('shipping_id',$id)->first();
                $shippingMethod->title=$request->title;
                $shippingMethod->name=$request->name;
                $shippingMethod->description=$request->description;
                $shippingMethod->price=$request->price;
                $shippingMethod->discount=$request->discount;

                $shippingMethod->display=$request->display;
                $shippingMethod->s_type=$request->s_type??0;
                $shippingMethod->s_time=$request->s_time??0;
                $shippingMethod->menu_order=$request->menu_order??0;

                $shippingMethod->date_update=strtotime('now');
                $shippingMethod->save();
                return response()->json([
                    'status'=>true,
                    'data'=>$shippingMethod
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
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,string $id)
    {
        $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'delete a ShippingMethod',
                'cat'=>'shippingMethod',
            ]);
        try{
            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Phương thức vận chuyển.del')){
                $shippingMethod=ShippingMethod::where('shipping_id',$id)->first();
                $shippingMethod->delete();
                return response()->json([
                    'status'=>true,
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
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function deleteAll(Request $request)
    {
        $arr =$request->data;
        //return $arr;
        try {
            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Phương thức vận chuyển.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        $shippingMethod=ShippingMethod::where('shipping_id',$item)->first();
                        $shippingMethod->delete();
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
