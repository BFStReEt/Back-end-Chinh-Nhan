<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductDesc;
use App\Models\ProductFlashSale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class ProductFlashSaleController extends Controller
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
                'action'=>'show all productFlashSale',
                'cat'=>'productFlashSale',
            ]);
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Sản phẩm hot deal.manage')){
                $list = ProductFlashSale::with('product.productDesc')->get();
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
    public function showAllProductFlashSale(Request $request)
    {
        try{

            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);

            $list = ProductFlashSale::with('product.productDesc')
            // ->where('start_time','<=', $stringTime)
            // ->where('end_time','>=', $stringTime)
            ->get();

            $response = [
                'status' => true,
                'list' => $list
            ];
            return response()->json($response);
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
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        DB::table('adminlogs')->insert([
            'admin_id' => Auth::guard('admin')->user()->id,
            'time' =>  $stringTime,
            'ip'=> $request->ip(),
            'action'=>'add a productFlashSale',
            'cat'=>'productFlashSale',
        ]);
        $product = new Product();
        $productFlashSale = new ProductFlashSale();

        try {
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Sản phẩm hot deal.add')){
            if($request->data != null)
            {
                foreach ($request->data as $id) {
                    $checkExist=ProductFlashSale::where('product_id',$id)->first();
                    if(!$checkExist){
                    $productFlashSale = new ProductFlashSale();
                    $product = Product::with('productDesc')->find($id);

                    $product->status=5;
                    $product->save();

                    $productFlashSale->product_id = $id;
                    $productFlashSale->price = $product->price;
                    $productFlashSale->price_old = $product->price_old;
                    $productFlashSale->discount_percent = 0;
                    $productFlashSale->discount_price = 0;
                    $productFlashSale->start_time = null;
                    $productFlashSale->end_time = null;
                    $productFlashSale-> adminid = 1;
                    $productFlashSale->save();
                    }
                    else{
                        return response()->json([
                            'status'=>false,
                            'message'=>'product is exist'
                        ]);
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
    public function edit(Request $request,string $id)
    {
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'edit a productFlashSale',
                'cat'=>'productFlashSale',
            ]);
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Sản phẩm hot deal.edit')){
            $list = ProductFlashSale::with('product','product.productDesc')
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
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'update a productFlashSale',
                'cat'=>'productFlashSale',
            ]);
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Sản phẩm hot deal.update')){
            $productFlashSale = ProductFlashSale::where('product_id',$id)->first();
            $productFlashSale->discount_price = $request->discount_price;
            $productFlashSale->start_time =strtotime($request->start_time);
            $productFlashSale->end_time = strtotime($request->end_time);
            $productFlashSale->save();
            return response()->json([
                'status'=> true,
                'product' => $productFlashSale
            ]);
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
     * Remove the specified resource from storage.
     */

     public function deleteAll(Request $request)
     {
         $arr =$request->data;
         try {
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Sản phẩm hot deal.del')){
             if($arr)
             {
                 foreach ($arr as $item) {
                    $list = ProductFlashSale::where('product_id',$item)->delete();
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

    public function destroy(Request $request,string $id)
    {
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        DB::table('adminlogs')->insert([
            'admin_id' => Auth::guard('admin')->user()->id,
            'time' =>  $stringTime,
            'ip'=> $request->ip(),
            'action'=>'delete a productFlashSale',
            'cat'=>'productFlashSale',
        ]);


        $arr = explode(",",$id);
        try {
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Sản phẩm hot deal.del')){
                if($id)
                {
                    foreach ($arr as $item) {
                        $list = ProductFlashSale::Find($item)->delete();
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
            ProductFlashSale::query()->update(['end_time' => $request->data]);
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
