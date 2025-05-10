<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class PaymentMethodController extends Controller
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
                'action'=>'show all paymentMethod',
                'cat'=>'paymentMethod',
            ]);
            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Phương thức thanh toán.manage')){
                if($request->data == 'undefined' || $request->data =="")
                {
                    $paymentMethod=PaymentMethod::orderBy('payment_id','asc')->paginate(10);
                }
                else{
                    $paymentMethod=PaymentMethod::where("title", 'like', '%' . $request->data . '%')
                        ->orderBy('payment_id','asc')->paginate(10);
                }

                return response()->json([
                    'status'=>true,
                    'data'=>$paymentMethod
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
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'add a paymentMethod',
                'cat'=>'paymentMethod',
            ]);
            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Phương thức thanh toán.add')){
                $paymentMethod=new PaymentMethod();
                $paymentMethod->title=$request->title;
                $paymentMethod->name=$request->name;
                $paymentMethod->description=$request->description;
                $paymentMethod->display=$request->display??0;

                // $paymentMethod->option=$request->option;
                $paymentMethod->is_config=$request->is_config;
                $paymentMethod->menu_order=$request->menu_order;
                $paymentMethod->lang='vi';

                $paymentMethod->date_post=strtotime('now');
                $paymentMethod->date_update=strtotime('now');
                $paymentMethod->save();
                return response()->json([
                    'status'=>true,
                    'data'=>$paymentMethod
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
                'action'=>'edit a paymentMethod',
                'cat'=>'paymentMethod',
            ]);
            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Phương thức thanh toán.edit')){

            $paymentMethod=PaymentMethod::where('payment_id',$id)->first();
            return response()->json([
                'status'=>true,
                'data'=>$paymentMethod
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
        try{
            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Phương thức thanh toán.update')){
            $paymentMethod=PaymentMethod::where('payment_id',$id)->first();
            $paymentMethod->title=$request->title;
            $paymentMethod->name=$request->name;
            $paymentMethod->description=$request->description;
            $paymentMethod->display=$request->display;

            $paymentMethod->is_config=$request->is_config;
            $paymentMethod->menu_order=$request->menu_order;
            $paymentMethod->lang='vi';
            $paymentMethod->date_update=strtotime('now');

            $paymentMethod->save();
            return response()->json([
                'status'=>true,
                'data'=>$paymentMethod
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
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'delete a paymentMethod',
                'cat'=>'paymentMethod',
            ]);
            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Phương thức thanh toán.del')){
                $paymentMethod=PaymentMethod::where('payment_id',$id)->first();
                $paymentMethod->delete();
                return response()->json([
                    'status'=>true
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
            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Phương thức thanh toán.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        $paymentMethod=PaymentMethod::where('payment_id',$item)->first();
                        $paymentMethod->delete();
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
