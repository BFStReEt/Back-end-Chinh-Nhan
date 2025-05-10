<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductDesc;
use App\Models\ProductHot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class ProductHotController extends Controller
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
                'action'=>'show all productHot',
                'cat'=>'productHot',
            ]);
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Sản phẩm nổi bật.manage')){
                $list = Product::with('productDesc')->where('status',4)->get();
                $response = [
                    'status' => true,
                    'list' => $list
                ];
                return response()->json($response, 200);
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
        $product = new Product();
        $ProductHot = new ProductHot();

        try {
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'add a productHot',
                'cat'=>'productHot',
            ]);
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Sản phẩm nổi bật.add')){
                if($request->data != null)
                {
                    foreach ($request->data as $id) {
                        //$ProductHot = new ProductHot();
                        $product = Product::with('productDesc')->find($id);
                        $product->status=4;
                        $product->save();

                        // $ProductHot->product_id = $id;
                        // $ProductHot->price = $product->price;
                        // $ProductHot->price_old = $product->price_old;
                        // $ProductHot->discount_percent = 0;
                        // $ProductHot->discount_price = 0;
                        // $ProductHot->start_time = 0;
                        // $ProductHot->end_time = 0;
                        // $ProductHot->status = 4;
                        // $ProductHot-> adminid =1;
                        // $ProductHot->save();
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
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'edit a productHot',
                'cat'=>'productHot',
            ]);
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Sản phẩm nổi bật.edit')){
                $list = Product::with('productDesc')
                ->where('product_id ',$id)->first();
                return response()->json([
                    'status'=> true,
                    'product' => $list
                ]);
            } else {
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

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
            'action'=>'delete a productHot',
            'cat'=>'productHot',
        ]);

        $arr = explode(",",$id);
        try {
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Sản phẩm nổi bật.del')){
                if($id)
                {
                    foreach ($arr as $item) {
                        $list = ProductHot::Find($item)->delete();
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

        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        DB::table('adminlogs')->insert([
            'admin_id' => Auth::guard('admin')->user()->id,
            'time' =>  $stringTime,
            'ip'=> $request->ip(),
            'action'=>'delete a productHot',
            'cat'=>'productHot',
        ]);
        $arr =$request->data;
        try {
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Sản phẩm nổi bật.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                    //    $list = ProductHot::where('product_id',$item)->delete();
                    $product=Product::where('product_id',$item)->first();
                    $product->status=0;
                    $product->save();
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
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }
    }
    public function updatedate(Request $request)
    {
        try{
            ProductHot::query()->update(['end_time' => $request->data]);
            return response()->json([
                'status' => true,
                'message' => 'Cập nhật thành công'
            ]);
        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json($response, 500);
        }
    }
}
