<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductAdvertiseSpecial;
class ProductAdvertiseSpecialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showAllProductAdvertiseSpecial(Request $request){
        try{
            $ProductAdvertiseSpecial=ProductAdvertiseSpecial::orderBy('id','desc')->get();
            return response()->json([
                'status'=>true,
                'data'=> $ProductAdvertiseSpecial
            ]);
        }catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json( $response, 500 );
        }
    }
    public function index(Request $request)
    {
        try{
            $pos=$request['cat'];

            $query=ProductAdvertiseSpecial::orderBy('id','desc');
            if ( $request->data == 'undefined' || $request->data == '' ) {
                $query = $query;
            } else {
                $query = $query->where( 'title', 'like', '%' . $request->data . '%' );
            }
            if(isset($pos)){
                $query=$query->where('pos',$pos);
            }
            $productAdvertise=$query->paginate(10);
            return response()->json( [
                'status'=>true,
                'data'=>$productAdvertise,
            ] );
        } catch ( \Exception $e ) {
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

        
        $productAdvertise = new ProductAdvertiseSpecial();
        try{
            $filePath = '';
            $filePath1='';

            if ( $request->picture != null ) {

                $DIR = 'uploads/productAdvertise';

                $httpPost = file_get_contents('php://input');

                $file_chunks = explode( ';base64,', $request->picture[ 0 ] );

                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];


                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = 'productAdvertise/'.$name . '.png';

                file_put_contents( $file,  $base64Img );
            }
            $productAdvertise->	itemID = 0;
            $productAdvertise-> type = $request->page;
            $productAdvertise->pos = $request->pos;
            $productAdvertise->cat_id = $request->cat_id;
            $productAdvertise->	picture = $filePath;

            if ( $request->background != null ) {

                $DIR = 'uploads/productAdvertise';

                $httpPost = file_get_contents('php://input');

                $file_chunks = explode( ';base64,', $request->background[ 0 ] );

                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];


                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath1 = 'productAdvertise/'.$name . '.png';

                file_put_contents( $file,  $base64Img );
            }

            $productAdvertise->	background = $filePath1;
            $productAdvertise-> link = $request->link?$request->link:'#';
            $productAdvertise-> title = $request->title?$request->title:'';
            $productAdvertise-> description = $request->description?$request->description:'';
            $productAdvertise-> target = $request->target??'_self';
            $productAdvertise-> height = $request->height?$request->height:0;
            $productAdvertise-> width = $request->width?$request->width:0;
            $productAdvertise-> display = $request->display;
            $productAdvertise-> status = $request->status;
            $productAdvertise-> menu_order = 0;
            $productAdvertise-> date_post = 0;
            $productAdvertise -> date_update = 0;
            $productAdvertise -> lang = 'vi';
            $productAdvertise->adminid = 1;
            $productAdvertise->save();
            return response()->json( [
                'status'=>true,
            ] );
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
        try{
            $productAdvertise = ProductAdvertiseSpecial::where('id',$id)->first();
            return response()->json([
                'status'=>true,
                'data'=>$productAdvertise
            ]);
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //$disPath = public_path();
        $filePath = '';
        $filePath1='';
        try{
            $productAdvertise = ProductAdvertiseSpecial::Find( $id );
            if ( $request->picture != null && $productAdvertise->picture != $request->picture ) {

                $DIR = 'uploads/productAdvertise';

                $httpPost = file_get_contents('php://input');

                $file_chunks = explode( ';base64,', $request->picture[ 0 ] );

                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];


                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = 'productAdvertise/'.$name . '.png';

                file_put_contents( $file,  $base64Img );
            } else {

                $filePath = $productAdvertise->picture;

            }

            $productAdvertise->	itemID = 0;
            $productAdvertise-> type = $request->page;
            $productAdvertise->pos = $request->pos;
            $productAdvertise->cat_id = $request->cat_id;
            $productAdvertise->	picture = $filePath;

            if ( $request->background != null && $productAdvertise->background != $request->background ) {

                $DIR = 'uploads/productAdvertise';

                $httpPost = file_get_contents('php://input');

                $file_chunks = explode( ';base64,', $request->background[ 0 ] );

                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];


                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath1 = 'productAdvertise/'.$name . '.png';

                file_put_contents( $file,  $base64Img );
            }else{
                $filePath1 = $productAdvertise->background;
            }

            $productAdvertise->	background = $filePath1;
            $productAdvertise-> link = $request->link?$request->link:'#';
            $productAdvertise-> title = $request->title?$request->title:'';
            $productAdvertise-> description = $request->description?$request->description:'';
            $productAdvertise-> target = '_self';
            $productAdvertise-> height = $request->height?$request->height:0;
            $productAdvertise-> width = $request->width?$request->width:0;
            $productAdvertise-> display = $request->display;
            $productAdvertise-> status = $request->status;
            $productAdvertise-> menu_order = 0;
            $productAdvertise-> date_post = 0;
            $productAdvertise -> date_update = 0;
            $productAdvertise -> lang = 'vi';
            $productAdvertise->adminid = 1;
            $productAdvertise->save();
            return response()->json( [
                'status'=>true,
            ] );

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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $productAdvertise = ProductAdvertiseSpecial::find( $id );
            $productAdvertise->delete();
            return response()->json( [
                'status' => true
            ] );
        }catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json( $response, 500 );
        }
    }
    public function deleteAll(Request $request){
        try{

            $arr =$request->data;
                if($arr)
                {
                    foreach ($arr as $item) {
                        $productAdvertise = ProductAdvertiseSpecial::find( $item );
                        $productAdvertise->delete();
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
