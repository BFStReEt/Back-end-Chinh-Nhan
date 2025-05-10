<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Adpos;
use App\Models\Advertise;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class AdvertiseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showAdvertise(Request $request,$pos = null){
        try{

        $query=Advertise::where('display', 1)
        ->get()->groupBy('pos');
        return response()->json([
            'status'=>true,
            'data'=> $query
           ]);

        }catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => false,
                'error' => $errorMessage
            ];

            return response()->json($response, 500);
        }
    }
    public function index(Request $request)
    {
        try {
            if(Gate::allows('QUẢN LÝ ADVERTISE.Quản lý advertise.manage')){
                $pos=$request['pos'];
                $module_show=$request['module_show'];
                $query=Advertise::orderBy('id','desc');
                if(empty($request->input('data'))||$request->input('data')=='undefined' ||$request->input('data')=='')
                {
                    $list = $query;
                }
                else{
                    $list = $query->where("title", 'like', '%' . $request->input('data') . '%');
                }
                if(isset($pos)){
                    $list = $query->where("pos",$pos);
                }
                if(isset($module_show)){
                    $list = $query->where("module_show",$module_show);
                }
                $listAdvertise=$list->paginate(10);
                $response = [
                    'status' => true,
                    'list' => $listAdvertise
                ];
                return response()->json($response, 200);
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
        //$module = implode( ',', $request->module );
        //$disPath = public_path();
        $advertise = new Advertise();
        try {
            if(Gate::allows('QUẢN LÝ ADVERTISE.Quản lý advertise.add')){
            $filePath = '';
            if ( $request->picture != null ) {

                $DIR = 'uploads/advertise';
                $httpPost = file_get_contents( 'php://input' );
                $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];

                //return response()->json( $file_chunks );
                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = 'advertise/'.$name . '.png';

                file_put_contents( $file,  $base64Img );
            }
            $advertise-> title = $request->title;
            $advertise-> picture = $filePath;
            $advertise-> pos = $request->pos;
            $advertise-> width = $request->width;
            $advertise-> height = $request->height;
            $advertise-> link = $request->link?$request->link:'#';
            $advertise-> target = $request->target?$request->target:'_self';
            // $advertise-> module_show = $module;
            $advertise-> description = $request->description?$request->description:0;
            $advertise-> menu_order = $request->menu;
            $advertise-> display = $request->display;
            $advertise-> lang = 'vi';
            $advertise->save();
            return response()->json( [
                'status'=>true,
            ] );
        }else {
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
    public function edit(string $id)
    {
        try{
            if(Gate::allows('QUẢN LÝ ADVERTISE.Quản lý advertise.edit')){
            $list = Advertise::find($id);
            return response()->json([
                'status'=> true,
                'list' => $list
            ]);
        }else {
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
       // $disPath = public_path();
        $advertise = new Advertise();
        $listAdvertise = Advertise::Find( $id );
        try {
            if(Gate::allows('QUẢN LÝ ADVERTISE.Quản lý advertise.update')){
            if ( $request->picture != null && $listAdvertise->picture != $request->picture ) {
                $filePath = '';
                $DIR = 'uploads/advertise';
                $httpPost = file_get_contents( 'php://input' );
                $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];

                //return response()->json( $file_chunks );
                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = 'advertise/'.$name . '.png';

                file_put_contents( $file,  $base64Img );
            } else {
                $filePath = $listAdvertise->picture;
            }

            $listAdvertise-> title = $request->title;
            $listAdvertise-> picture = $filePath;
            $listAdvertise-> pos = $request->pos;
            $listAdvertise-> width = $request->width;
            $listAdvertise-> height = $request->height;
            $listAdvertise-> link = $request->link?$request->link:'#';
            $listAdvertise-> target = $request->target?$request->target:'_self';
            // $listAdvertise-> module_show = implode( ',', $request->module );
            $listAdvertise-> description = $request->description?$request->description:0;
            $listAdvertise-> menu_order = $request->menu;
            $listAdvertise-> display = $request->display;
            $listAdvertise-> lang = 'vi';
            $listAdvertise->save();
            return response()->json( [
                'status'=>true,
            ] );
        }else {
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
    public function destroy(string $id)
    {
        try {
            if(Gate::allows('QUẢN LÝ ADVERTISE.Quản lý advertise.del')){
            $list = Advertise::Find($id)->delete();
            return response()->json([
                'status'=>true,
            ]);
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
    public function deleteAll(Request $request)
    {
        $arr =$request->data;
        try {
            if(Gate::allows('QUẢN LÝ ADVERTISE.Quản lý advertise.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        Advertise::Find($item)->delete();
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
