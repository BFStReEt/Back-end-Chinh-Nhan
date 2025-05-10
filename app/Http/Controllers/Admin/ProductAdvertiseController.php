<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductAdvertise;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class ProductAdvertiseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'show all productAdvertise',
                'cat'=>'productAdvertise',
            ]);
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Banner sản phẩm.manage')){
                $pos=$request['cat'];

                $query=ProductAdvertise::orderBy('id','desc');
                if ( $request->data == 'undefined' || $request->data == '' ) {
                    $list = $query;
                } else {
                    $list = $query->where( 'title', 'like', '%' . $request->data . '%' );
                }
                if(isset($pos)){
                    $list=$query->where('pos',$pos);
                }
                $productAdvertise=$list->paginate(10);
                return response()->json( [
                    'status'=>true,
                    'data'=>$productAdvertise,
                ] );
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        } catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];
            return response()->json( $response, 500 );
        }
    }
    public function showAdvertise(Request $request){
        try{

            $pos=$request->pos;

            $list=ProductAdvertise::where('display',1)->orderBy('id','desc');
            if(isset($pos)){
                $list=$list->where('pos',$pos);
            }
            $productAdvertise=$list->get();
            return response()->json( [
                'status'=>true,
                'data'=>$productAdvertise,
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
            'action'=>'add a productAdvertise',
            'cat'=>'productAdvertise',
        ]);
        //$disPath = public_path();
        $productAdvertise = new ProductAdvertise();
        try {
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Banner sản phẩm.add')){
            $filePath = '';

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
            $productAdvertise-> link = $request->link?$request->link:'#';
            $productAdvertise-> title = $request->title?$request->title:'';
            $productAdvertise-> description = $request->description?$request->description:'';
            $productAdvertise-> target = $request->target??'_self';
            $productAdvertise-> height = $request->height?$request->height:0;
            $productAdvertise-> width = $request->width?$request->width:0;
            $productAdvertise-> display = $request->display;
            $productAdvertise-> menu_order = 0;
            $productAdvertise-> date_post = 0;
            $productAdvertise -> date_update = 0;
            $productAdvertise -> lang = 'vi';
            $productAdvertise->adminid = 1;
            $productAdvertise->save();
            return response()->json( [
                'status'=>true,
            ] );
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }

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

        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        DB::table('adminlogs')->insert([
            'admin_id' => Auth::guard('admin')->user()->id,
            'time' =>  $stringTime,
            'ip'=> $request->ip(),
            'action'=>'edit a productAdvertise',
            'cat'=>'productAdvertise',
        ]);
        try {
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Banner sản phẩm.edit')){
                $productAdvertise = ProductAdvertise::find($id);
                return response()->json( [
                    'status'=> true,
                    'data' => $productAdvertise
                ] );
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
            }
        catch ( \Exception $e ) {
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
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        DB::table('adminlogs')->insert([
            'admin_id' => Auth::guard('admin')->user()->id,
            'time' =>  $stringTime,
            'ip'=> $request->ip(),
            'action'=>'update a productAdvertise',
            'cat'=>'productAdvertise',
        ]);
        //$disPath = public_path();
        $productAdvertise = new ProductAdvertise();
        $listProductAdvertise = ProductAdvertise::Find( $id );
        try {
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Banner sản phẩm.update')){
                if ( $request->picture != null && $listProductAdvertise->picture != $request->picture ) {
                    $filePath = '';
                    $DIR = 'uploads/productAdvertise';
                    $httpPost = file_get_contents( 'php://input' );
                    $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                    $fileType = explode( 'image/', $file_chunks[ 0 ] );
                    $image_type = $fileType[ 0 ];

                    //return response()->json( $file_chunks );
                    $base64Img = base64_decode( $file_chunks[ 1 ] );
                    $data = iconv( 'latin5', 'utf-8', $base64Img );
                    $name = uniqid();
                    $file = public_path($DIR) . '/' . $name . '.png';
                    $filePath = 'productAdvertise/'.$name . '.png';

                    file_put_contents( $file,  $base64Img );
                } else {

                    $filePath = $listProductAdvertise->picture;

                }


                $listProductAdvertise->	itemID = 0;
                $listProductAdvertise-> type = $request->page;
                $listProductAdvertise-> pos = $request->pos;
                $listProductAdvertise->cat_id = $request->cat_id;
                $listProductAdvertise->	picture = $filePath;
                $listProductAdvertise-> link = $request->link?$request->link:'#';
                $listProductAdvertise-> title = $request->title?$request->title:'';
                $listProductAdvertise-> description = $request->description?$request->description:'';
                $listProductAdvertise-> target = $request->target??'_self';
                $listProductAdvertise-> height = $request->height?$request->height:0;
                $listProductAdvertise-> width = $request->width?$request->width:0;
                $listProductAdvertise-> display = $request->display;
                $listProductAdvertise-> menu_order = 0;
                $listProductAdvertise-> date_post = 0;
                $listProductAdvertise -> date_update = 0;
                $listProductAdvertise -> lang = 'vi';
                $listProductAdvertise->adminid = 1;
                $listProductAdvertise->save();
                return response()->json( [
                    'status'=>true,
                ] );
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }

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
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,string $id)
    {
        try {
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'delete a productAdvertise',
                'cat'=>'productAdvertise',
            ]);
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Banner sản phẩm.del')){
                $productAdvertise = ProductAdvertise::find( $id );
                $productAdvertise->delete();
                return response()->json( [
                    'status' => true
                ] );
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }
        catch ( \Exception $e ) {
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

            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Banner sản phẩm.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        $productAdvertise = ProductAdvertise::find( $item );
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
