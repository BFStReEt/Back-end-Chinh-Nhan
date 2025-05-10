<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MailTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class MailTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            //title
            if(Gate::allows('HÌNH THỨC, NỘI DUNG.Quản lý mail template.manage')){
            $query=MailTemplate::select('mailtemp_id','title','name');
            if($request->data == 'undefined' || $request->data =="")
            {
                $mailTemplate = $query->get();
            }
            else{
                $mailTemplate = $query->where("title", 'like', '%' . $request->data . '%')->get();
            }


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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            if(Gate::allows('HÌNH THỨC, NỘI DUNG.Quản lý mail template.add')){
            $mailTemplate = new MailTemplate();
            $mailTemplate->title=$request->title;
            $mailTemplate->name=$request->name;
            $mailTemplate->description=$request->description;
            $mailTemplate->display=$request->display;
            $mailTemplate->date_post=strtotime( 'now' );
            $mailTemplate->date_update=strtotime( 'now' );
            $mailTemplate->save();
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
        try {
            if(Gate::allows('HÌNH THỨC, NỘI DUNG.Quản lý mail template.edit')){
            $mailTemplate = MailTemplate::where('mailtemp_id',$id)->first();
            $response = [
                'status' => 'success',
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            if(Gate::allows('HÌNH THỨC, NỘI DUNG.Quản lý mail template.update')){
            $mailTemplate = MailTemplate::where('mailtemp_id',$id)->first();
            $mailTemplate->title=$request->title??$mailTemplate->title;
            $mailTemplate->name=$request->name??$mailTemplate->name;
            $mailTemplate->description=$request->description??$mailTemplate->description;
            $mailTemplate->display=$request->display??$mailTemplate->display;
            $mailTemplate->date_post=strtotime( 'now' );
            $mailTemplate->date_update=strtotime( 'now' );
            $mailTemplate->save();
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            if(Gate::allows('HÌNH THỨC, NỘI DUNG.Quản lý mail template.del')){
                $mailTemplate = MailTemplate::where('mailtemp_id',$id)->first();
                $mailTemplate->delete();
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
                if($arr)
                {
                    foreach ($arr as $item) {
                        $mailTemplate = MailTemplate::where('mailtemp_id',$item)->first();
                        $mailTemplate->delete();
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
