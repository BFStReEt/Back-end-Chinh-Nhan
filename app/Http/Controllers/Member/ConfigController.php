<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\SettingLogo;
use App\Models\SettingSmtp;
class ConfigController extends Controller
{
    //
    public function showConfig(){
        try{
            $Setting=Setting::first();
            $SettingLogo=SettingLogo::first();
            return response()->json([
                'status'=>true,
                'Setting'=>$Setting,
                'SettingLogo'=>$SettingLogo
            ]);
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }

    }
}
