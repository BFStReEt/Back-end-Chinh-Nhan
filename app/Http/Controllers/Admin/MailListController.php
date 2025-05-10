<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MailList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class MailListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            if(Gate::allows('QUẢN LÝ NEWSLETTER.Quản lý newsletter.manage')){
                $mailTemplate = MailList::select('id','email','g_name','date_send','status')->get();
                $response = [
                    'status' => true,
                    'list' => $mailTemplate,

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
    public function addGmail(Request $request){
        try{


            $MailList=null;
            //return $request->email;
            //$listGmail=explode(",", $request->email);


                    $MailList=new MailList();
                    // $MailList->g_name=$request->g_name;
                    // $MailList->name=$request->name;

                    $MailList->email=$request[0];

                    $MailList->display=1;

                    $MailList->date_send=strtotime( 'now' );
                    $MailList->date_update=strtotime( 'now' );
                    $MailList->save();



            return response()->json([
                'status'=>true,
                'data'=>$MailList
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            if(Gate::allows('QUẢN LÝ NEWSLETTER.Quản lý newsletter.add')){
            $MailList=null;
            $listGmail=explode(",", $request->email);
            if(count( $listGmail)!=0){
                foreach( $listGmail as $item){
                    $MailList=new MailList();
                    // $MailList->g_name=$request->g_name;
                    // $MailList->name=$request->name;

                    $MailList->email=$item;

                    $MailList->display=1;

                    $MailList->date_send=strtotime( 'now' );
                    $MailList->date_update=strtotime( 'now' );
                    $MailList->save();

                }
            }


            return response()->json([
                'status'=>true,
                'data'=>$MailList
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
            if(Gate::allows('QUẢN LÝ NEWSLETTER.Quản lý newsletter.edit')){
                $MailList=MailList::where('id',$id)->first();
                if($MailList->status==0){
                    $MailList->status=1;
                    $MailList->save();
                }
                return response()->json([
                    'status'=>true,
                    'data'=>$MailList
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
            if(Gate::allows('QUẢN LÝ NEWSLETTER.Quản lý newsletter.update')){
            $MailList=MailList::where('id',$id)->first();
            $MailList->g_name=$request->g_name?? $MailList->g_name;
            $MailList->name=$request->name??$MailList->name;

            $MailList->email=$request->email?? $MailList->email;

            $MailList->display=$request->display??$MailList->display;

            $MailList->date_send=strtotime( 'now' );
            $MailList->date_update=strtotime( 'now' );
            $MailList->save();
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            if(Gate::allows('QUẢN LÝ NEWSLETTER.Quản lý newsletter.del')){
                $MailList=MailList::where('id',$id)->first();
                $MailList->delete();
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
    public function deleteAll(Request $request)
    {
        $arr =$request->data;

        try {
            if(Gate::allows('QUẢN LÝ NEWSLETTER.Quản lý newsletter.del')){
                if($arr)
                {
                    foreach ($arr as $item) {
                        $MailList=MailList::where('id',$item)->first();
                        $MailList->delete();
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
