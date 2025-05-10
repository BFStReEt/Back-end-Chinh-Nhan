<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PromotionDesc;
use App\Models\Promotion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use DateTime;
use Carbon\Carbon;
use Gate;
class PromotionController extends Controller
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
                    'action'=>'show all promotion',
                    'cat'=>'promotion',
                ]);
                if(Gate::allows('QUẢN LÝ KHUYẾN MÃI.Quản lý tin khuyến mãi.manage')){
                    if($request->data == 'undefined' || $request->data =="")
                    {
                        $promotion = Promotion::with('promotionDesc')
                        ->orderBy('promotion_id','desc')
                        ->paginate(10);
                    }
                    else{
                        $promotion = Promotion::with('promotionDesc')->whereHas('promotionDesc', function ($query) use ($request) {
                        $query->where("title", 'like', '%' . $request->data . '%');})
                        ->orderBy('promotion_id','desc')
                        ->paginate(10);
                    }
                    $response = [
                        'status' => true,
                        'list' => $promotion,

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
            'action'=>'add a promotion',
            'cat'=>'promotion',
        ]);
        //return $request->selectedFile;
        $dataStart = strtotime( $request->date_start_promotion );
        $dataEnd = strtotime( $request->date_end_promotion );
        //$disPath = public_path();
        $promotion = new Promotion();
        $promotionDesc = new PromotionDesc();
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        try {
            if(Gate::allows('QUẢN LÝ KHUYẾN MÃI.Quản lý tin khuyến mãi.add')){
                $filePath = '';
                if ( $request->selectedFile != null ) {

                    $DIR = 'uploads/promotion';
                    $httpPost = file_get_contents( 'php://input' );
                    $file_chunks = explode( ';base64,', $request->selectedFile[ 0 ] );
                    $fileType = explode( 'image/', $file_chunks[ 0 ] );
                    $image_type = $fileType[ 0 ];

                    //return response()->json( $file_chunks );
                    $base64Img = base64_decode( $file_chunks[ 1 ] );
                    $data = iconv( 'latin5', 'utf-8', $base64Img );
                    $name = uniqid();
                    $file = public_path($DIR) . '/' . $name . '.png';
                    $filePath = 'promotion/'.$name . '.png';

                    file_put_contents( $file,  $base64Img );
                }
                $promotion->fill( [
                    'picture' => $filePath,
                    'focus'=> 0,
                    'focus_order' => 0,
                    'views' => 0,
                    'display' => $request->input( 'display' ),
                    'menu_order' => 0,
                    'adminid' => 1,
                'date_post'=>$stringTime,
                'date_update'=>$stringTime,
                    'date_start_promotion' => $dataStart,
                    'date_end_promotion' => $dataEnd,
                    'status' => 1,
                ] )->save();
                $promotionDesc->promotion_id = $promotion->promotion_id;
                $promotionDesc->title = $request->input( 'title' );
                $promotionDesc->description = $request->input( 'description' );
                $promotionDesc->short = '';
                $promotionDesc->friendly_url = $request->input( 'friendly_url' );
                $promotionDesc->friendly_title = $request->input( 'friendly_title' );
                $promotionDesc->metakey = $request->input( 'metakey' );
                $promotionDesc->metadesc = $request->input( 'metadesc' );
                $promotionDesc->lang = 'vi';
                $promotionDesc->save();

                $date = Carbon::now('Asia/Ho_Chi_Minh');

                $data = [
                    'name' => $promotionDesc->title,
                    'link'=> $promotionDesc->friendly_url,
                    'status'=> 2
                ];
                $dataSocket =[
                    'type'=>'promotion',
                    'socketId'=>rand(9,9999)
                    .Carbon::now('Asia/Ho_Chi_Minh')->isoFormat('DDMMYYYY'),
                    'titlePromotion'=>$promotionDesc->title,
                    'linkPromotion'=> $promotionDesc->friendly_url,
                    'date'=>$date,
                    'seen'=>false
                ];
                try {
                    $message=json_encode($dataSocket);
                    //$message=$dataSocket;
                    // return $message;


                    $endpoint = 'http://192.168.245.190:3003/api/notifies';
                    $endpoint .= '?message='. urlencode($message);

                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])
                        ->withoutVerifying()
                        ->get($endpoint);

                    if ($response->successful()) {
                            $responseData = $response->json(); // Assuming response is JSON
                            // Process $responseData if needed
                        } else {
                            $error = $response->toPsrResponse()->getReasonPhrase();
                            echo "cURL Error: " . $error;
                        }

                } catch(Exception $e) {
                    return ['error' => $e->getMessage()];
                }



                $response = [
                    'status' => true,
                    'promotion' => $promotion,
                    'promotionDesc' => $promotionDesc,
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
                'status' => false,
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
                'action'=>'edit a promotion',
                'cat'=>'promotion',
            ]);
            
            if(Gate::allows('QUẢN LÝ KHUYẾN MÃI.Quản lý tin khuyến mãi.edit')){
                $promotion = Promotion::with('promotionDesc')->find($id);
                return response()->json([
                'status'=> true,
                'promotion' => $promotion
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
                'status' => false,
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
            'action'=>'update a promotion',
            'cat'=>'promotion',
        ]);

        //$disPath = public_path();
        $promotion = Promotion::Find( $id );
        $dataStart = strtotime( $request->date_start_promotion );
        $dataEnd =strtotime( $request->date_end_promotion );
        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        try {
            if(Gate::allows('QUẢN LÝ KHUYẾN MÃI.Quản lý tin khuyến mãi.update')){
                if ( $request->selectedFile != null && $promotion->picture != $request->selectedFile ) {
                    $filePath = '';
                    $DIR = 'uploads/promotion';
                    $httpPost = file_get_contents( 'php://input' );
                    $file_chunks = explode( ';base64,', $request->selectedFile[ 0 ] );
                    $fileType = explode( 'image/', $file_chunks[ 0 ] );
                    $image_type = $fileType[ 0 ];

                    $base64Img = base64_decode( $file_chunks[ 1 ] );
                    $data = iconv( 'latin5', 'utf-8', $base64Img );
                    $name = uniqid();
                    $file = public_path($DIR) . '/' . $name . '.png';
                    $filePath = 'promotion/'.$name . '.png';

                    file_put_contents( $file,  $base64Img );
                } else {
                    $filePath = $promotion->picture;
                }

                $promotion->fill( [
                    'picture' => $filePath,
                    'display' => $request->input( 'display' ),
                    'date_update'=>$stringTime,
                    'date_start_promotion' => $dataStart,
                    'date_end_promotion' => $dataEnd,
                ] )->save();

                $promotionDesc = PromotionDesc::where( 'promotion_id', $id )->first();
                $promotionDesc->promotion_id = $promotion->promotion_id;
                $promotionDesc->title = $request->input( 'title' );
                $promotionDesc->description = $request->input( 'description' );
                $promotionDesc->friendly_url = $request->input( 'friendly_url' );
                $promotionDesc->friendly_title = $request->input( 'friendly_title' );
                $promotionDesc->metakey = $request->input( 'metakey' );
                $promotionDesc->metadesc = $request->input( 'metadesc' );
                $promotionDesc->lang = 'vi';
                $promotionDesc->save();
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
                'status' => false,
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
                'action'=>'delete a promotion',
                'cat'=>'promotion',
            ]);
            if(Gate::allows('QUẢN LÝ KHUYẾN MÃI.Quản lý tin khuyến mãi.del')){
                $list = Promotion::Find($id)->delete();
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
                'status' => false,
                'error' => $errorMessage
            ];

            return response()->json( $response, 500 );
        }
    }
    public function deleteAllPromotion(Request $request){


        try{
            if(Gate::allows('QUẢN LÝ KHUYẾN MÃI.Quản lý tin khuyến mãi.del')){
                $arr =$request->data;
                //$arr = explode(",",$id);
                if($arr )
                {
                    foreach ($arr as $item) {
                        $list = Promotion::Find($item)->delete();
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
                'status' => false,
                'error' => $errorMessage
            ];
            return response()->json( $response, 500 );
        }
    }
}
