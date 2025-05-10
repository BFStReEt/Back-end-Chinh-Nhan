<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ServiceDesc;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{
            if(Gate::allows('QUẢN LÝ DỊCH VỤ.Quản lý dịch vụ.manage')){
                if($request->data == 'undefined' || $request->data =="")
                {
                    $service = Service::with('serviceDesc')
                    ->orderBy('service_id','desc')
                    ->paginate(10);
                }
                else{
                    $service = Service::with('serviceDesc')->whereHas('serviceDesc', function ($query) use ($request) {
                        $query->where("title", 'like', '%' . $request->data . '%');})
                        ->orderBy('service_id','desc')
                        ->paginate(10);
                }
                return response()->json(
                    [
                    'status' => true,
                    'list' => $service,
                    ]
                );
            } else {
                return response()->json([
                    'status'=>false,
                    'mess' => 'no permission',
                ]);
            }
        } catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function showServiceUser(){
        try{
           // $service = Service::with('serviceDesc')->orderBy('service_id','desc')->paginate(6);
            $listData=DB::table('service')
            ->where('display', 1)
            ->join('service_desc', 'service_desc.service_id', '=', 'service.service_id')
            ->select('service.*','service_desc.title','service_desc.friendly_url','service_desc.friendly_title')
            ->orderBy('service.service_id','desc')
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
    public function showDetailService(Request $request, $slug){
        try{
            $listServiceDesc = ServiceDesc::with('service')->where('friendly_url', $slug)->first();
            return response()->json([
              'status'=> true,
              'service' => $listServiceDesc
          ]);
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

        //$disPath = public_path();
        $service = new Service();
        $serviceDesc = new ServiceDesc();
        try {
            if(Gate::allows('QUẢN LÝ DỊCH VỤ.Quản lý dịch vụ.add')){
            $filePath = '';
            if ( $request->picture != null ) {

                $DIR = 'uploads/service';
                $httpPost = file_get_contents( 'php://input' );
                $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];
                //return response()->json( $file_chunks );
                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = 'service/'.$name . '.png';

                file_put_contents( $file,  $base64Img );
            }
            $service->fill([
                'picture' => $filePath,
                'views'=>0,
                'display' => $request->input('display'),
                'menu_order' =>0,
                'adminid' => 1,
                'date_post'=>strtotime( 'now' ),
                'date_update'=>strtotime( 'now' )
            ])->save();
            $serviceDesc->service_id = $service->service_id;
            $serviceDesc->title = $request->input('title');
            $serviceDesc->description = $request->input('description');
            $serviceDesc->friendly_url = $request->input('friendly_url');
            $serviceDesc->friendly_title = $request->input('friendly_title');
            $serviceDesc->metakey = $request->input('metakey');
            $serviceDesc->metadesc = $request->input('metadesc');
            $serviceDesc->lang = "vi";
            $serviceDesc->save();
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
    public function edit(string $id)
    {
        try{
            if(Gate::allows('QUẢN LÝ DỊCH VỤ.Quản lý dịch vụ.edit')){
                $listServiceDesc = Service::with('serviceDesc')->find($id);
                return response()->json([
                'status'=> true,
                'service' => $listServiceDesc
                ]);
            } else {
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
        //$disPath = public_path();
        $service = new Service();
        $listService = Service::Find( $id );
        try {
            if(Gate::allows('QUẢN LÝ DỊCH VỤ.Quản lý dịch vụ.update')){
                if ( $request->picture != null && $listService->picture != $request->picture ) {
                    $filePath = '';
                    $DIR ='uploads/service';
                    $httpPost = file_get_contents( 'php://input' );
                    $file_chunks = explode( ';base64,', $request->picture[ 0 ] );
                    $fileType = explode( 'image/', $file_chunks[ 0 ] );
                    $image_type = $fileType[ 0 ];

                    //return response()->json( $file_chunks );
                    $base64Img = base64_decode( $file_chunks[ 1 ] );
                    $data = iconv( 'latin5', 'utf-8', $base64Img );
                    $name = uniqid();
                    $file = public_path($DIR) . '/' . $name . '.png';
                    $filePath = 'service/'.$name . '.png';

                    file_put_contents( $file,  $base64Img );
                } else {
                    $filePath = $listService->picture;

                }
                $listService->fill([
                    'picture' => $filePath,
                    'views'=>0,
                    'display' => $request->input('display'),
                    'menu_order' =>0,
                    'adminid' => 1,
                    'date_post'=>strtotime( 'now' ),
                    'date_update'=>strtotime( 'now' )
                ])->save();

                $serviceDesc = ServiceDesc::where( 'service_id', $id )->first();
                $serviceDesc->title = $request->input('title');
                $serviceDesc->description = $request->input('description');
                $serviceDesc->friendly_url = $request->input('friendly_url');
                $serviceDesc->friendly_title = $request->input('friendly_title');
                $serviceDesc->metakey = $request->input('metakey');
                $serviceDesc->metadesc = $request->input('metadesc');
                $serviceDesc->lang = "vi";
                $serviceDesc->save();
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
    public function destroy(string $id)
    {
        try{
            if(Gate::allows('QUẢN LÝ DỊCH VỤ.Quản lý dịch vụ.del')){
                $list = Service::Find($id)->delete();
                return response()->json([
                    'status'=>true
                ]);
            } else {
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
            if(Gate::allows('QUẢN LÝ DỊCH VỤ.Quản lý dịch vụ.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        Service::Find($item)->delete();
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
