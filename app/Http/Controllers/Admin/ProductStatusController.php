<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ProductStatus;
use App\Models\ProductStatusDesc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class ProductStatusController extends Controller
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
                'action'=>'show all productStatus',
                'cat'=>'productStatus',
            ]);

            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Trạng thái sản phẩm.manage')){
            if($request->data == 'undefined' || $request->data =="")
                {
                    $productStatus=ProductStatus::with('productStatusDesc')
                    ->orderBy('status_id','desc')
                    ->paginate(10);
                }
                else{
                    $productStatus=ProductStatus::with('productStatusDesc')->whereHas('productStatusDesc', function ($query) use ($request) {
                    $query->where("title", 'like', '%' . $request->data . '%');})
                    ->orderBy('status_id','desc')
                    ->paginate(10);
                }
                $response = [
                    'status' => 'success',
                    'list' => $productStatus,

                ];

            return response()->json( $response, 200 );
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
            'action'=>'add a productStatus',
            'cat'=>'productStatus',
        ]);
        //$disPath = public_path();
        $productStatus = new ProductStatus();
        $productStatusDesc = new ProductStatusDesc();

        try {

            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Trạng thái sản phẩm.add')){
            if ( $request->picture != null ) {

                $filePath = '';
                $DIR ='uploads/product/status';
                $httpPost = file_get_contents( 'php://input' );
                $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];


                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                // $file = $DIR .'\\'. $name . '.png';
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = 'product/status/'.$name . '.png';

                file_put_contents( $file,  $base64Img );

                // $file = $request->file('picture');
                // $path = public_path('uploads/productStatus'); // Đường dẫn đến thư mục public/uploads

                // $fileName = uniqid(). '.png';
                // //return $file;
                // $name = $file->getClientOriginalName();

                // $filePath = 'productStatus/'.$fileName;
                // $file->move($path, $fileName);

            }


            $productStatus->fill( [
                'picture' => $filePath,
                'name' => $request->input( 'name' ),
                'display' => $request->input( 'display' ),
                'width' => $request->input( 'width' ),
                'height' => $request->input( 'height' ),
                'view' => 0,
                'menu_order' =>  $request->input( 'menu_order'),
            ] )->save();


            $productStatusDesc->status_id = $productStatus->status_id;
            $productStatusDesc->title = $request->input( 'title' );
            $productStatusDesc->description = $request->input( 'description' );
            $productStatusDesc->friendly_url = $request->input( 'friendly_url' );
            $productStatusDesc->friendly_title = $request->input( 'friendly_title' );
            $productStatusDesc->metakey = $request->input( 'metakey' );
            $productStatusDesc->metadesc = $request->input( 'metadesc' );
            $productStatusDesc->lang = 'vi';
            $productStatusDesc->save();
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
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'edit a productStatus',
                'cat'=>'productStatus',
            ]);
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Trạng thái sản phẩm.edit')){

            $productStatus=ProductStatus::with('productStatusDesc')->find($id);
            return response()->json([
              'status'=> true,
              'productStatus' =>$productStatus
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
            'action'=>'update a productStatus',
            'cat'=>'productStatus',
        ]);

        //$disPath = public_path();
        $productStatus=ProductStatus::Find($id);

        try {
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Trạng thái sản phẩm.update')){

            if ( $request->picture != null && $productStatus->picture != $request->picture) {

                $filePath = '';
                $DIR = 'uploads/product/status';
                $httpPost = file_get_contents( 'php://input' );
                $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];

                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                // $file = $DIR .'\\'. $name . '.png';
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = 'product/status/'.$name . '.png';

                file_put_contents( $file,  $base64Img );

                // $file = $request->file('picture');
                // $path = public_path('uploads/productStatus'); // Đường dẫn đến thư mục public/uploads

                // $fileName = uniqid(). '.png';
                // //return $file;
                // $name = $file->getClientOriginalName();

                // $filePath = 'productStatus/'.$fileName;
                // $file->move($path, $fileName);
            } else {
                $filePath =  $productStatus->picture;
            }


            $productStatus->fill( [
                'picture' => $filePath,
                'name' => $request->input( 'name' ),
                'display' => $request->input( 'display' ),

                'width' => $request->input( 'width' ),
                'height' => $request->input( 'height' ),
                'view' => 0,
                'menu_order' =>  $request->input( 'menu_order'),
            ] )->save();


            $productStatusDesc = ProductStatusDesc::where('status_id',$id)->first();

            $productStatusDesc->status_id = $productStatus->status_id;

            $productStatusDesc->title = $request->input( 'title' );
            $productStatusDesc->description = $request->input( 'description' );
            $productStatusDesc->friendly_url = $request->input( 'friendly_url' );
            $productStatusDesc->friendly_title = $request->input( 'friendly_title' );
            $productStatusDesc->metakey = $request->input( 'metakey' );
            $productStatusDesc->metadesc = $request->input( 'metadesc' );
            $productStatusDesc->lang = 'vi';

            $productStatusDesc->save();


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
        try{
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' =>  $stringTime,
                'ip'=> $request->ip(),
                'action'=>'delete a productStatus',
                'cat'=>'productStatus',
            ]);

            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Trạng thái sản phẩm.del')){

            $list = ProductStatus::Find($id)->delete();
            return response()->json([
                'status'=>true
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
    public function deleteAllProduct(Request $request){
        try{

            $arr =$request->data;

            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Trạng thái sản phẩm.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        $list = ProductStatus::where('status_id',$item)->delete();
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
