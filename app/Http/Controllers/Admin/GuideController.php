<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Guide;
use App\Models\GuideDesc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class GuideController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showAllGuide(){
        try{

            $listData=DB::table('guide')
            ->where('display', 1)
            ->join('guide_desc', 'guide_desc.guide_id', '=', 'guide.guide_id')
            ->select('guide.*','guide_desc.title','guide_desc.friendly_url','guide_desc.friendly_title')
            ->orderBy('guide.guide_id','desc')
            ->paginate(6);



            // $guide = Guide::with('guideDesc')
            //         ->orderBy('guide_id','desc')
            //         ->get();
            $response = [
                'status' => 'success',
                'list' => $listData,

            ];

            return response()->json( $response, 200 );
        }catch ( \Exception $e ) {
            $errorMessage = $e->getMessage();
            $response = [
                'status' => 'false',
                'error' => $errorMessage
            ];

            return response()->json( $response, 500 );
        }
    }
    public function showDetailGuide(Request $request,$slug){
        try{
            $GuideDesc=GuideDesc::with('guide')->where('friendly_url',$slug)->first();
            return response()->json([
                'status'=>true,
                'data'=> $GuideDesc
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
            //$guide = Guide::with('guideDesc')->paginate(10);
            if(Gate::allows('QUẢN LÝ HƯỚNG DẪN.Quản lý hướng dẫn.manage')){
                if($request->data == 'undefined' || $request->data =="")
                {
                    $guide = Guide::with('guideDesc')
                    ->orderBy('guide_id','desc')
                    ->paginate(10);
                }
                else{
                    $guide = Guide::with('guideDesc')->whereHas('guideDesc', function ($query) use ($request) {
                        $query->where("title", 'like', '%' . $request->data . '%');})
                        ->orderBy('guide_id','desc')
                        ->paginate(10);
                }

                // $listData=DB::table('guide')
                // ->where('display', 1)

                // ->join('guide_desc', 'guide_desc.guide_id', '=', 'guide.guide_id')
                // ->select('guide.*','guide_desc.title','guide_desc.friendly_url','guide_desc.friendly_title')
                // ->orderBy('guide.guide_id','desc')
                // ->paginate(6);
                // return  $listData;
                $response = [
                    'status' => 'success',
                    'list' => $guide,

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
        //$disPath = public_path();
        $guide = new Guide();
        $guideDesc = new GuideDesc();
        try {

            if(Gate::allows('QUẢN LÝ HƯỚNG DẪN.Quản lý hướng dẫn.add')){
                $filePath = '';
                if ( $request->picture != null ) {

                    $DIR ='uploads/guide';
                    $httpPost = file_get_contents( 'php://input' );
                    $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                    $fileType = explode( 'image/', $file_chunks[ 0 ] );
                    $image_type = $fileType[ 0 ];
                    //return response()->json( $file_chunks );
                    $base64Img = base64_decode( $file_chunks[ 1 ] );
                    $data = iconv( 'latin5', 'utf-8', $base64Img );
                    $name = uniqid();
                    $file = public_path($DIR) . '/' . $name . '.png';
                    $filePath = 'guide/'.$name . '.png';

                    file_put_contents( $file,  $base64Img );
                }

                $guide->fill([
                    'picture' => $filePath,
                    'views'=> $request->input('views'),
                    'display' => $request->input('display'),
                    'menu_order' => $request->input('menu_order'),
                    'adminid' => $request->input('adminid')
                ])->save();
                $guideDesc->guide_id = $guide->guide_id;
                $guideDesc->title = $request->input('title');
                $guideDesc->description = $request->input('description');
                $guideDesc->short = $request->input('short');
                $guideDesc->friendly_url = $request->input('friendly_url');
                $guideDesc->friendly_title = $request->input('friendly_title');
                $guideDesc->metakey = $request->input('metakey');
                $guideDesc->metadesc = $request->input('metadesc');
                $guideDesc->lang = $request->input('lang');
                $guideDesc->save();

                $response = [
                    'status' => 'success',
                    'guide' => $guide,
                    'guideDesc' => $guideDesc,
                ];
                return response()->json($response, 200);
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
            if(Gate::allows('QUẢN LÝ HƯỚNG DẪN.Quản lý hướng dẫn.edit')){
                $listGuideDesc = Guide::with('guideDesc')->find($id);
                return response()->json([
                'status'=> true,
                'guide' => $listGuideDesc
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
        try{
            if(Gate::allows('QUẢN LÝ HƯỚNG DẪN.Quản lý hướng dẫn.update')){
                //$disPath = public_path();
                $guide = new Guide();
                $guideDesc = new GuideDesc();
                $listGuide = Guide::Find($id);

                if ( $request->picture != null && $listGuide->picture != $request->picture ) {
                    $filePath = '';
                    $DIR ='uploads/guide';
                    $httpPost = file_get_contents( 'php://input' );
                    $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                    $fileType = explode( 'image/', $file_chunks[ 0 ] );
                    $image_type = $fileType[ 0 ];

                    //return response()->json( $file_chunks );
                    $base64Img = base64_decode( $file_chunks[ 1 ] );
                    $data = iconv( 'latin5', 'utf-8', $base64Img );
                    $name = uniqid();
                    $file = public_path($DIR) . '/' . $name . '.png';
                    $filePath = 'guide/'.$name . '.png';

                    file_put_contents( $file,  $base64Img );
                } else {
                    $filePath = $listGuide->picture;

                }

                $listGuide->picture = $filePath;
                $listGuide->views = $request->input('views');
                $listGuide->display = $request->input('display');
                $listGuide->menu_order = $request->input('menu_order');
                $listGuide->adminid = $request->input('adminid');
                $listGuide->save();

                $guideDesc = GuideDesc::where('guide_id', $id)->first();
                if ($guideDesc) {
                    $guideDesc->title = $request->input('title');
                    $guideDesc->description = $request->input('description');
                    $guideDesc->short = $request->input('short');
                    $guideDesc->friendly_url = $request->input('friendly_url');
                    $guideDesc->friendly_title = $request->input('friendly_title');
                    $guideDesc->metakey = $request->input('metakey');
                    $guideDesc->metadesc = $request->input('metadesc');
                    $guideDesc->lang = $request->input('lang');
                    $guideDesc->save();
                }
                return response()->json([
                    'status'=>true,
                    'data'=>$guideDesc
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            if(Gate::allows('QUẢN LÝ HƯỚNG DẪN.Quản lý hướng dẫn.del')){
                $list = Guide::Find($id)->delete();
                return response()->json([
                    'status'=>true
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
    public function deleteAll(Request $request)
    {
        $arr =$request->data;
        try {
            if(Gate::allows('QUẢN LÝ GIỚI THIỆU.Quản lý giới thiệu.del')){
            if($arr)
            {
                foreach ($arr as $item) {
                    Guide::Find($item)->delete();
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
