<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\SettingLogo;
use App\Models\SettingSmtp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Gate;
class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            if(Gate::allows('THÔNG TIN HỆ THỐNG.Cấu hình hệ thống.manage')){
            $Setting=Setting::first();
            $SettingLogo=SettingLogo::first();
            $SettingSmtp=SettingSmtp::first();
            return response()->json([
                'status'=>true,
                'settingSystem'=> $Setting,
                'settingLogo'=>$SettingLogo,
                'settingSmtp'=>$SettingSmtp
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
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            if(Gate::allows('THÔNG TIN HỆ THỐNG.Cấu hình hệ thống.update')){
            $disPath = public_path();

            $Setting=Setting::where('id',$id)->first();
            $Setting->title=$request->title??$Setting->title;
            $Setting->meta_desc=$request->meta_desc??$Setting->meta_desc;
            $Setting->meta_extra=$request->meta_extra??$Setting->meta_extra;
            $Setting->script=$request->script??$Setting->script;
            $Setting->google_analytics_id=$request->google_analytics_id??$Setting->google_analytics_id;
            $Setting->google_maps_api_id=$request->google_maps_api_id??$Setting->google_maps_api_id;
            $Setting->charset=$request->charset??$Setting->charset;

            $filePath = '';
            $filePath1 = '';


            if ( $request->favicon != null && $request->favicon != $Setting->favicon )
            {
                //return $request->picture;
                $DIR = $disPath.'\uploads';
                $httpPost = file_get_contents( 'php://input' );
                $file_chunks = explode( ';base64,', $request->favicon[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];

                //return response()->json( $file_chunks );
                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                $file = $DIR .'\\'. $name . '.png';
                $filePath = ''.$name . '.png';

                file_put_contents( $file,  $base64Img );
            } else {
                $filePath = $Setting->favicon;
            }
            $Setting->favicon= $filePath??null;
            $Setting->save();

            $SettingLogo=SettingLogo::where('id',$id)->first();
            if ( $request->logo != null && $request->logo !=  $SettingLogo->logo )
            {
                //return $request->picture;
                $DIR = $disPath.'\uploads';
                $httpPost = file_get_contents( 'php://input' );
                $file_chunks = explode( ';base64,', $request->logo[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];

                //return response()->json( $file_chunks );
                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                $file = $DIR .'\\'. $name . '.png';
                $filePath1 = ''.$name . '.png';

                file_put_contents( $file,  $base64Img );
            } else {
                $filePath1 = $SettingLogo->logo;
            }
            $SettingLogo->logo=$filePath1;
            $SettingLogo->hotline=$request->hotline??$SettingLogo->hotline;
            $SettingLogo->email=$request->email??$SettingLogo->email;
            $SettingLogo->email_search=$request->email_search??$SettingLogo->email_search;
            $SettingLogo->address=$request->address??$SettingLogo->address;
            $SettingLogo->tool_search=$request->tool_search??$SettingLogo->tool_search;
            $SettingLogo->save();

            $SettingSmtp=SettingSmtp::where('id',$id)->first();
            $SettingSmtp->method=$request->method??$SettingSmtp->method;
            $SettingSmtp->host=$request->host??$SettingSmtp->host;
            $SettingSmtp->port=$request->port??$SettingSmtp->port;
            $SettingSmtp->username=$request->username??$SettingSmtp->username;
            $SettingSmtp->password=$request->password??$SettingSmtp->password;
            $SettingSmtp->from_name=$request->from_name??$SettingSmtp->from_name;
            $SettingSmtp->password_security=$request->password_security??$SettingSmtp->password_security;
            $SettingSmtp->time_cache=$request->time_cache??$SettingSmtp->time_cache;
            $SettingSmtp->save();
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
        //
    }
}
