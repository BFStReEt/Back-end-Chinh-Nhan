<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Member;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Twilio\Rest\Client;
use Infobip\Configuration;
use Infobip\ApiException;
use Infobip\Api\SmsApi;
use Infobip\Model\SmsAdvancedTextualRequest;
use Infobip\Model\SmsDestination;
use Infobip\Model\SmsTextualMessage;

class SocialController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function handleProviderCallback($provider)
    {

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
            $user = Member::where('email', $socialUser->getEmail())->first();
            if(!$user){
                $user=new Member;
                $user->	username=$socialUser->getName();
                $user->	password=Hash::make(Str::random(24));
                $user->	provider=$provider;
                $user->	provider_id=$socialUser->getId();
                $user->	avatar=$socialUser->getAvatar();
                $user->	email=$socialUser->getEmail();
                $user->	status=0;
                $user->save();

            }
            $token = $user->createToken('LaravelSocialite')->accessToken;
            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function loginSocial(Request $request){
        try {


            //return $request->all();
            $email= $request->email;
            $provider=$request->provider;

            $condition= Member::where('email', $email)
            ->where('provider','!=', $provider)->first();
            $condition1= Member::where('email', $email)
            ->where('provider', $provider)->first();

            if($condition){
                return response()->json([
                    'status'=>false,
                    'message'=>'emailExist not provider'
                ]);
            }else if($condition1){
                return response()->json([
                    'status'=>true,

                ]);

            }else if(!$condition && !$condition1){
                $user=new Member;
                $user->user_id=$request->userId;
                $user->	username=$request->username;
                $user->	provider=$request->provider;
                $user->	email=$email;
                $user->	status=0;
                $user->save();
                return response()->json([
                    'status' => true,
                ]);
            }
            // if(!$user){
            //     $user=new Member;
            //     $user->user_id=$request->userId;
            //     $user->	username=$request->username;
            //     $user->	provider=$request->provider;
            //     $user->	email=$email;
            //     $user->	status=0;
            //     $user->save();
            // }


        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }

    }
    public function getInfoUser(Request $request){
        try{
            $provider=$request->provider;

            $email= $request->email;
            $user = Member::where('email', $email)->where('provider',$provider)->first();
            return response()->json([
                'status'=>true,
                'user'=>$user
            ]);


        }catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function sendOtp(Request $request)
    {
        $phone='84'.$request->phone;
        $min = 100000;
        $max = 999999;

        $code=strval(mt_rand($min, $max));
        $configuration = new Configuration(
            host: 'mm5z8w.api.infobip.com',
            apiKey: '75a822dcdf562b7f01f969e8ac6e7922-f3be7a8a-2bb2-4254-abcb-e84718ca51d4'
        );
        $sendSmsApi = new SmsApi(config: $configuration);

        $message = new SmsTextualMessage(
            destinations: [
                new SmsDestination(to: $phone)
            ],
            from: 'Vitinhnguyenkim',
            text: 'Mã xác nhận vitinhnguyenkim của bạn là '.$code
        );
        $message->setSubject('Tiêu đề tin nhắn');

        $request = new SmsAdvancedTextualRequest(messages: [$message]);

        try {
            $smsResponse = $sendSmsApi->sendSmsMessage($request);
            return response()->json([
                'status'=>true
            ]);
        } catch (ApiException $apiException) {
            // HANDLE THE EXCEPTION
        }

    }
}
