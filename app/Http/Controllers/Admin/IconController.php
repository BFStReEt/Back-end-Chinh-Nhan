<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Icon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class IconController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showIcon(){
        try{
            $Icon = Icon::where('display',1)->orderBy('icon_id','desc')->get();
            return response()->json([
                'status'=>true,
                'data'=>$Icon
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
        try {

            if(Gate::allows('SEO-MẠNG XÃ HỘI.Quản lý Icon MXH.manage')){
                $query = Icon::orderBy('icon_id','desc');

                if($request->data == 'undefined' || $request->data =="")
                {
                    $Icon = $query->get();
                }
                else{
                    $Icon = $query->where("title", 'like', '%' . $request->data . '%')->get();
                }
                $response = [
                    'status' => true,
                    'list' => $Icon,
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
        try{
            if(Gate::allows('SEO-MẠNG XÃ HỘI.Quản lý Icon MXH.add')){
            //$disPath = public_path();
            $Icon =new Icon();
            $Icon->type=$request->type;

            $filePath = '';
            if ( $request->picture != null ) {

                $DIR = 'uploads/File';
                $httpPost = file_get_contents( 'php://input' );
                $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];
                //return response()->json( $file_chunks );
                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                //$file = $DIR .'\\'. $name . '.png';
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = 'File/'.$name . '.png';

                file_put_contents( $file,  $base64Img );
            }
            $Icon->picture=$filePath;
            $Icon->color=$request->color;
            $Icon->link=$request->link;
            $Icon->title=$request->title;
            $Icon->font_icon=$request->font_icon;
            $Icon->target=$request->target;

            $Icon->description=$request->description;
            $Icon->date_post=strtotime( 'now' );
            $Icon->date_update=strtotime( 'now' );
            $Icon->display=$request->display;
            $Icon->save();
            return response()->json([
                'status'=>true,
                'data'=>$Icon
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
            if(Gate::allows('SEO-MẠNG XÃ HỘI.Quản lý Icon MXH.edit')){
                $Icon =Icon::where('icon_id',$id)->first();

                return response()->json([
                    'status'=>true,
                    'data'=>$Icon
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
        try{
            if(Gate::allows('SEO-MẠNG XÃ HỘI.Quản lý Icon MXH.update')){
            //$disPath = public_path();
            $Icon =Icon::where('icon_id',$id)->first();

            $Icon->type=$request->type??$Icon->type;

            $filePath = '';
            if ( $request->picture != null &&  $Icon->picture != $request->picture ) {

                $DIR = 'uploads/File';
                $httpPost = file_get_contents( 'php://input' );
                $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];
                //return response()->json( $file_chunks );
                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = 'File/'.$name . '.png';

                file_put_contents( $file,  $base64Img );
            }else{
                $filePath = $Icon->picture;
            }

            $Icon->picture=$filePath;
            $Icon->color=$request->color??$Icon->color;
            $Icon->link=$request->link?? $Icon->link;
            $Icon->title=$request->title??$Icon->title;
            $Icon->font_icon=$request->font_icon??$Icon->font_icon;
            $Icon->target=$request->target??$Icon->target;

            $Icon->description=$request->description??$Icon->description;
            $Icon->date_post=strtotime( 'now' );
            $Icon->date_update=strtotime( 'now' );
            $Icon->display=$request->display;
            $Icon->save();
            return response()->json([
                'status'=>true,
                'data'=>$Icon
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            if(Gate::allows('SEO-MẠNG XÃ HỘI.Quản lý Icon MXH.del')){
            $Icon =Icon::where('icon_id',$id)->first();
            $Icon->delete();
            return response()->json([
                'status'=>true,
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
    public function deleteAll(Request $request)
    {
        $arr =$request->data;

        try {
            if(Gate::allows('SEO-MẠNG XÃ HỘI.Quản lý Icon MXH.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        $Icon =Icon::where('icon_id',$item)->first();
                        $Icon->delete();
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

}
