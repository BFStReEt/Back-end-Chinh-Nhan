<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class OrderStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{
            // $orderStatus=OrderStatus::orderBy('status_id','desc')->get();
            // return response()->json([
            //     'status'=>true,
            //     'orderStatus'=>$orderStatus
            // ]);
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'show all orderStatus',
                'cat'=>'orderStatus',
            ]);
            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Trạng thái đơn hàng.manage')){
                if($request->data == 'undefined' || $request->data =="")
                {
                        $orderStatus=OrderStatus::orderBy('status_id','asc')->paginate(10);
                }
                else{
                        $orderStatus=OrderStatus::where("title", 'like', '%' . $request->data . '%')
                        ->orderBy('status_id','asc')->paginate(10);
                }
                return response()->json([
                    'status'=>true,
                    'orderStatus'=>$orderStatus
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
                'action'=>'add a orderStatus',
                'cat'=>'orderStatus',
            ]);
            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Trạng thái đơn hàng.add')){

                $orderStatus=new OrderStatus();
                $orderStatus->title=$request->title;
                $orderStatus->keyStatus=$request->keyStatus;
                $orderStatus->color=$request->color??'#000000';
                $orderStatus->is_default=$request->is_default??0;
                $orderStatus->is_payment=$request->is_payment??0;
                $orderStatus->is_complete=$request->is_complete??0;
                $orderStatus->is_cancel=$request->is_cancel??0;

                $orderStatus->is_customer=$request->is_cusomer??0;
                $orderStatus->menu_order=$request->menu_order??0;

                $orderStatus->display=$request->display??0;
                $orderStatus->save();
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
                'action'=>'edit a orderStatus',
                'cat'=>'orderStatus',
            ]);

            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Trạng thái đơn hàng.edit')){
                $orderStatus=OrderStatus::where('status_id',$id)->first();
                return response()->json([
                    'status'=>true,
                    'orderStatus'=>$orderStatus
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

            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'update a orderStatus',
                'cat'=>'orderStatus',
            ]);
            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Trạng thái đơn hàng.update')){

                $orderStatus=OrderStatus::where('status_id',$id)->first();
                $orderStatus->title=$request->title;
                $orderStatus->keyStatus=$request->keyStatus;
                $orderStatus->display=$request->display;
                $orderStatus->color=$request->color;
                $orderStatus->is_default=$request->is_default;
                $orderStatus->is_payment=$request->is_payment;
                $orderStatus->is_complete=$request->is_complete;
                $orderStatus->is_cancel=$request->is_cancel;

                $orderStatus->is_customer=$request->is_customer;
                $orderStatus->menu_order=$request->menu_order;
                $orderStatus->save();
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
                'action'=>'delete a orderStatus',
                'cat'=>'orderStatus',
            ]);
            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Trạng thái đơn hàng.del')){
                $orderStatus=OrderStatus::where('status_id',$id)->first();
                $orderStatus->delete();
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
    public function deleteAll(Request $request){
        try{

            $arr =$request->data;

            if(Gate::allows('QUẢN LÝ ĐƠN HÀNG.Trạng thái đơn hàng.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        $orderStatus=OrderStatus::where('status_id',$item)->first();
                        $orderStatus->delete();
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
}
