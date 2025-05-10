<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductDesc;
use App\Models\ProductPromotion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Gate;
class ProductPromotionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $list = ProductPromotion::with('product','product.productDesc')->get();
            $response = [
                'status' => true,
                'list' => $list
            ];
            return response()->json($response, 200);
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
        $productPromotion = new ProductPromotion();

        try {
            if($request->data != null)
            {
                foreach ($request->data as $id) {
                    $productPromotion = new ProductPromotion();
                    $product = Product::with('productDesc')->find($id);
                    $productPromotion->product_id = $id;
                    $productPromotion->price = $product->price;
                    $productPromotion->price_old = $product->price_old;
                    $productPromotion->discount_percent = 0;
                    $productPromotion->discount_price = 0;
                    $productPromotion->start_time = 0;
                    $productPromotion->end_time = 0;
                    $productPromotion->status = 1;
                    $productPromotion-> adminid = 1;
                    $productPromotion->save();
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
            $list = ProductPromotion::with('product','product.productDesc')
            ->where('product_id ',$id)->first();
              return response()->json([
                'status'=> true,
                'product' => $list
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

    }
}
