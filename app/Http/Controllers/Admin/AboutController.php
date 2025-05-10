<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\About;
use App\Models\AboutDesc;
use Illuminate\Support\Facades\DB;
use Gate;
class AboutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function showAbout(){
        try{
            $listData=DB::table('about')
            ->where('display', 1)
            ->join('about_desc', 'about_desc.about_id', '=', 'about.about_id')
            ->select('about.*','about_desc.title','about_desc.friendly_url','about_desc.friendly_title')
            ->orderBy('about.about_id','desc')
            ->paginate(6);

            return response()->json([
                "status"=>true,
                "services"=>$listData
            ]);
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function showDetailAbout(Request $request, $slug){
        try{
            $listAbout = AboutDesc::with('about')->where('friendly_url', $slug)->first();
            return response()->json([
              'status'=> true,
              'data' => $listAbout
          ]);
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function index(Request $request)
    {
        try{
            //$about = About::with('aboutDesc')->get();

            if(Gate::allows('QUẢN LÝ GIỚI THIỆU.Quản lý giới thiệu.manage')){
                if($request->data == 'undefined' || $request->data =="")
                {
                    $about = About::with('aboutDesc')
                    ->orderBy('about_id','desc')
                    ->get();
                }
                else{
                    $about =About::with('aboutDesc')->whereHas('aboutDesc', function ($query) use ($request) {
                        $query->where("title", 'like', '%' . $request->data . '%');
                    })->orderBy('about_id','desc')
                        ->get();
                }
                $response = [
                    'status' => true,
                    'list' => $about,

                ];
                return response()->json( $response, 200 );
            }else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
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
            $about=new About();
            $aboutDesc=new AboutDesc();
            // $disPath = public_path();

            if(Gate::allows('QUẢN LÝ GIỚI THIỆU.Quản lý giới thiệu.add')){
                $filePath = '';
                if ( $request->picture != null ) {

                    $DIR ='uploads/about';
                    $httpPost = file_get_contents( 'php://input' );
                    $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                    $fileType = explode( 'image/', $file_chunks[ 0 ] );
                    $image_type = $fileType[ 0 ];
                    //return response()->json( $file_chunks );
                    $base64Img = base64_decode( $file_chunks[ 1 ] );
                    $data = iconv( 'latin5', 'utf-8', $base64Img );
                    $name = uniqid();
                    $file = public_path($DIR) . '/' . $name . '.png';
                    $filePath = 'about/'.$name . '.png';

                    file_put_contents( $file,  $base64Img );
                }
                $about->fill([
                    'picture' => $filePath,
                    'views'=>0,
                    'display' => $request->input('display'),
                    'menu_order' =>0,
                    'adminid' => 1,
                    'date_post'=>strtotime( 'now' ),
                    'date_update'=>strtotime( 'now' )
                ])->save();
                $aboutDesc->about_id= $about->about_id;
                $aboutDesc->title = $request->input('title');
                $aboutDesc->description = $request->input('description');
                $aboutDesc->friendly_url = $request->input('friendly_url');
                $aboutDesc->friendly_title = $request->input('friendly_title');
                $aboutDesc->metakey = $request->input('metakey');
                $aboutDesc->metadesc = $request->input('metadesc');
                $aboutDesc->lang = "vi";
                $aboutDesc->save();
                return response()->json( [
                    'status'=>true,
                ] );
            }else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }





        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
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
            if(Gate::allows('QUẢN LÝ GIỚI THIỆU.Quản lý giới thiệu.edit')){
                $about= About::with('aboutDesc')->find($id);
                return response()->json([
                'status'=> true,
                'data' =>  $about
            ]);
        }else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // $disPath = public_path();
        $about = About::where( 'about_id', $id )->first();
        //return $news;
        $aboutDesc = AboutDesc::where( 'about_id', $id )->first();
        try{
            if(Gate::allows('QUẢN LÝ GIỚI THIỆU.Quản lý giới thiệu.update')){
                if ( $request->picture != null && $about->picture != $request->picture ) {
                    $filePath = '';
                    $DIR ='uploads/about';
                    $httpPost = file_get_contents( 'php://input' );
                    $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                    $fileType = explode( 'image/', $file_chunks[ 0 ] );
                    $image_type = $fileType[ 0 ];
                    //return response()->json( $file_chunks );
                    $base64Img = base64_decode( $file_chunks[ 1 ] );
                    $data = iconv( 'latin5', 'utf-8', $base64Img );
                    $name = uniqid();
                    $file = public_path($DIR) . '/' . $name . '.png';
                    $filePath = 'about/'.$name . '.png';

                    file_put_contents( $file,  $base64Img );
                } else {
                    $filePath = $about->picture;

                }
                $about->fill([
                    'picture' => $filePath,
                    'views'=>0,
                    'display' => $request->input('display'),
                    'menu_order' =>0,
                    'adminid' => 1,
                    'date_post'=>strtotime( 'now' ),
                    'date_update'=>strtotime( 'now' )
                ])->save();

                $aboutDesc->title = $request->input('title');
                $aboutDesc->description = $request->input('description');
                $aboutDesc->friendly_url = $request->input('friendly_url');
                $aboutDesc->friendly_title = $request->input('friendly_title');
                $aboutDesc->metakey = $request->input('metakey');
                $aboutDesc->metadesc = $request->input('metadesc');
                $aboutDesc->lang = "vi";
                $aboutDesc->save();
                return response()->json([
                    'status'=>true
                ]);
        }else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            if(Gate::allows('QUẢN LÝ GIỚI THIỆU.Quản lý giới thiệu.del')){
                $list = About::Find($id)->delete();
                return response()->json([
                    'status'=>true
                ]);
            }else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
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
                        About::Find($item)->delete();
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
