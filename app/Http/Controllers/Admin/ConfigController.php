<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Gate;
class ConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            $config=new Config();
            $config->title=$request->title;
            $config->metaKeywords=$request->metaKeywords;
            $config->metaDescription=$request->metaDescription;
            $config->priceOfPoint=$request->priceOfPoint;
            $config->productOfPage=$request->productOfPage;
            $config->width=$request->width;
            $config->displayPicture=$request->displayPicture;
            $config->valueOfPoint=$request->valueOfPoint;
            //$disPath = public_path();
            $filePath = '';
            if ( $request->picture != null ) {

                //return $request->picture;
                // $DIR = $disPath.'\uploads';
                $DIR = 'uploads';
                $httpPost = file_get_contents( 'php://input' );
                $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];

                //return response()->json( $file_chunks );
                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                // $file = $DIR .'\\'. $name . '.png';
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = $name . '.png';

                file_put_contents( $file,  $base64Img );
            }

            $config->picture=$filePath;

            $config->save();
            return response()->json([
                'status'=>true
            ]);

        }catch(\Throwable $th){
            return response()->json([
              'status' => false,
              'message' => $th->getMessage()
            ]);
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

            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Cấu hình sản phẩm.edit')){
                $now = date('d-m-Y H:i:s');
                $stringTime = strtotime($now);
                DB::table('adminlogs')->insert([
                    'admin_id' => Auth::guard('admin')->user()->id,
                    'time' =>  $stringTime,
                    'ip'=> $request->ip(),
                    'action'=>'edit config',
                    'cat'=>'config',
                ]);
                $config=Config::find($id);
                return response()->json([
                    'status'=>true,
                    'data'=>$config
                ]);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }catch(\Throwable $th){
            return response()->json([
              'status' => false,
              'message' => $th->getMessage()
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            if(Gate::allows('QUẢN LÝ SẢN PHẨM.Cấu hình sản phẩm.update')){
                $now = date('d-m-Y H:i:s');
                $stringTime = strtotime($now);
                DB::table('adminlogs')->insert([
                    'admin_id' => Auth::guard('admin')->user()->id,
                    'time' =>  $stringTime,
                    'ip'=> $request->ip(),
                    'action'=>'update a config',
                    'cat'=>'config',
                ]);
                //$disPath = public_path();
                $config=Config::find($id);

                $config->title=$request->title??$config->title;
                $config->metaKeywords=$request->metaKeywords??$config->metaKeywords;
                $config->metaDescription=$request->metaDescription??$config->metaDescription;

                $config->priceOfPoint=$request->priceOfPoint??$config->priceOfPoint;
                $config->productOfPage=$request->productOfPage?? $config->productOfPage;
                $config->width=$request->width??$config->width;
                $config->displayPicture=$request->displayPicture??$config->displayPicture;
                $config->valueOfPoint=$request->valueOfPoint??$config->valueOfPoint;



                if ( $request->picture != null && $config->picture != $request->picture ) {
                    $DIR = 'uploads';
                    $httpPost = file_get_contents( 'php://input' );
                    $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                    $fileType = explode( 'image/', $file_chunks[ 0 ] );
                    $image_type = $fileType[ 0 ];

                    //return response()->json( $file_chunks );
                    $base64Img = base64_decode( $file_chunks[ 1 ] );
                    $data = iconv( 'latin5', 'utf-8', $base64Img );
                    $name = uniqid();
                    $file = public_path($DIR) . '/' . $name . '.png';
                    $filePath = $name . '.png';

                    file_put_contents( $file,  $base64Img );

                } else {
                    $filePath =  $config->picture;
                }
                $config->picture=$filePath;


                $config->save();
                return response()->json([
                    'status'=>true
            ]);
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }

        }catch(\Throwable $th){
            return response()->json([
              'status' => false,
              'message' => $th->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
