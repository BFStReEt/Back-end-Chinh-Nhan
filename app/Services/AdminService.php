<?php

namespace App\Services;

use App\Services\Interfaces\AdminServiceInterface;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Gate;
use Illuminate\Support\Facades\Http;
use App\Models\SettingSmtp;

use App\Repositories\Interfaces\AdminRepositoryInterface as AdminRepository;


/**
 * Class AdminService
 * @package App\Services
 */
class AdminService implements AdminServiceInterface
{
    protected $adminRepository;


    public function __construct(
        AdminRepository $adminRepository,
    ){
        $this->adminRepository = $adminRepository;
    }

    // public function login($request){
    //     $val = Validator::make($request->all(), [
    //         'username' => 'required',
    //         'password' => 'required',
    //     ]);
    //     if ($val->fails()) {
    //         return response()->json($val->errors(), 202);
    //     }
    //     $now = date('d-m-Y H:i:s');
    //     $stringTime = strtotime($now);
    //     // $admin =  $this->adminRepository->findByAdmin($request->username);
    //     // $condition=[
    //     //     ['username','=', $request->username]
    //     // ];
    //     // $admin =  $this->adminRepository->findByCondition( $condition);
    //     $admin = Admin::where('username',$request->username)->first();



    //     if(isset($admin)!=1)
    //     {
    //         return response()->json([
    //             'status' => false,
    //             'mess' => 'username'
    //         ]);
    //     }

    //     $check =  $admin->makeVisible('password');


    //     if(Hash::check($request->password,$check->password)){

    //             $success= $admin->createToken('Admin')->accessToken;

    //             $admin->lastlogin=$stringTime;
    //             $admin->save();

    //             return response()->json([
    //                 'status' => true,
    //                 'token' => $success,
    //                 'username'=>$admin->display_name
    //             ]);
    //     }else {

    //         return response()->json([
    //                 'status' => false,
    //                 'mess' => 'pass'
    //         ]);
    //     }
    // }
    public function login($request){
        $val = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);
        if ($val->fails()) {
            return response()->json($val->errors(), 202);
        }
        $captchaToken = $request->input('captchaToken');
        $passwordSecurity= $request->input('passwordSecurity');



        $now = date('d-m-Y H:i:s');
        $stringTime = strtotime($now);
        // $admin =  $this->adminRepository->findByAdmin($request->username);
        // $condition=[
        //     ['username','=', $request->username]
        // ];
        // $admin =  $this->adminRepository->findByCondition( $condition);
        $admin = Admin::where('username',$request->username)->first();
      
        if(isset($admin)!=1)
        {
            return response()->json([
                'status' => false,
                'mess' => 'username'
            ]);
        }

        $check =  $admin->makeVisible('password');
        if(Hash::check($request->password,$check->password)){

                $success= $admin->createToken('Admin')->accessToken;

                $admin->lastlogin=$stringTime;
                $admin->save();
                $SettingSmtp=SettingSmtp::first();
               
                if($SettingSmtp){
                    $ps=$SettingSmtp->password_security;
                    if($ps!==$passwordSecurity){
                        return response()->json([
                            'status'=>false,
                            'mess'=>'wrong passwordSecurity'
                        ]);
                    }
                }
                // if (!$captchaToken) {
                //     return response()->json([
                //         'status'=>false,
                //         'mess'=>'Captcha token is missing.'
                //     ]);
                // }
                // $secretKey = env('RECAPTCHA_SECRET_KEY');
                // $verifyUrl = "https://www.google.com/recaptcha/api/siteverify";
                // $response = Http::asForm()->post($verifyUrl, [
                //         'secret' => $secretKey,
                //         'response' => $captchaToken,
                // ]);
                // $responseBody = $response->json();

              
                //if ($responseBody['success']==true) {
                    //return response()->json(['message' => 'Captcha verified successfully.'], 200);
                    return response()->json([
                        'status' => true,
                        'token' => $success,
                        'username'=>$admin->display_name
                    ]);
                //}

                return response()->json([
                    'status'=>false,
                    'mess'=>'Invalid captcha.'
                ]);


        }else {

            return response()->json([
                    'status' => false,
                    'mess' => 'pass'
            ]);
        }
    }

    public function information(){

        $id = Auth::guard('admin')->user()->id;
        // $condition=[
        //     ['id','=', $id]
        // ];
        // $userAdmin =  $this->adminRepository->findByCondition( $condition);
        $userAdmin = Admin::with('roles')->where('id',$id)->first();
        return response()->json([
            'status'=>true,
            'data'=> $userAdmin,
        ]);

    }
    public function logout(){
        Auth::guard('admin')->user()->token()->revoke();
        return response()->json([
            'status'=>true
        ]);

    }

    public function index($request)
    {

        if(Gate::allows('THÔNG TIN QUẢN TRỊ.Quản lý tài khoản admin.manage')){
            $query=Admin::with('roles')->orderBy('id','desc');
            $roleId=$request->role_id;
            $listAdminId=DB::table('admin_role')->where('role_id',$roleId)
            ->join('admin', 'admin.id', '=', 'admin_role.admin_id')
            ->select('admin.*')->pluck('id');

            if(isset($roleId)){


                $listAdminId=DB::table('admin_role')->where('role_id',$roleId)
                ->join('admin', 'admin.id', '=', 'admin_role.admin_id')
                ->select('admin.*')->pluck('id');


                if(count( $listAdminId)!=0){
                    $query=Admin::with('roles')->whereIn('id', $listAdminId);
                }else{
                    return response()->json([
                        'status'=>true,
                        'adminList'=>[],
                    ]);
                }

            }


            if($request->data == 'undefined' || $request->data =="")
            {
                $list=$query;
            }
            else{

                $list=$query->where('username','like', '%' . $request->data . '%')
                ->orWhere('email','like', '%' . $request->data . '%');
            }
            $adminList=$list->paginate(5);
            return response()->json([
                'status'=>true,
                'adminList'=>$adminList,
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }
    }
    public function store($request)
    {
        if(Gate::allows('THÔNG TIN QUẢN TRỊ.Quản lý tài khoản admin.add')){
        $validator = Validator::make($request->all(),[
            'username' => 'required',
            'password' => 'required',
            ]);
            if($validator->fails()){
                return response()->json([
                    'message'=>'Vui lòng nhập tên đăng nhập và mật khẩu',
                    'errors'=>$validator->errors()
                ],422);
            }
            $check = Admin::where('username',$request->username)->first();
            if($check != '')
            {
               return response()->json([
                   'message'=>'Tên đăng nhập bị trùng ,vui lòng nhập lại',
                   'status'=>'false'
               ],202);
            }
            $data = $request->only([
                'username',
                'password',
                'email',
                'display_name',
                'avatar',
                'phone',
                'status',
                'depart_id',
            ]);
            // $this->adminRepository->create($data);
            $userAdmin = new Admin();
            $userAdmin -> username = $request['username'];
            $userAdmin -> password = Hash::make($request['password']);
            $userAdmin -> email = $request['email'];
            $userAdmin -> display_name = $request['display_name'];
            //$userAdmin -> avatar = isset($request['avatar']) ? $request['avatar'] : null;

            $filePath = '';
            //$disPath = public_path();


            if ( $request->avatar!= null )
            {

                $DIR = 'uploads/admin';
                $httpPost = file_get_contents( 'php://input' );

                $file_chunks = explode( ';base64,', $request->avatar[ 0 ] );
                $fileType = explode( 'image/', $file_chunks[ 0 ] );
                $image_type = $fileType[ 0 ];
                $base64Img = base64_decode( $file_chunks[ 1 ] );
                $data = iconv( 'latin5', 'utf-8', $base64Img );
                $name = uniqid();
                // $file = $DIR .'\\'. $name . '.png';
                $file = public_path($DIR) . '/' . $name . '.png';
                $filePath = 'admin/'.$name . '.png';
                file_put_contents( $file,  $base64Img );
            }
            $userAdmin->avatar=$filePath;

            $userAdmin -> skin = "";
            $userAdmin -> is_default = 0;
            $userAdmin -> lastlogin = 0;
            $userAdmin -> code_reset = Hash::make($request['password']);
            $userAdmin -> menu_order = 0;
            $userAdmin -> phone = $request['phone'];
            $userAdmin -> status = $request['status'];
            $userAdmin -> depart_id= $request['depart_id'];

            $userAdmin -> save();
            $userAdmin->roles()->attach($request->input('role_id'));
            return response()->json([
                'status' => true,
                'userAdmin' => $userAdmin,
            ]);
        } else {
            return response()->json([
                'status'=>false,
                'mess' => 'no permission',
            ]);
        }

    }
    public function edit($id)
    {
        if(Gate::allows('THÔNG TIN QUẢN TRỊ.Quản lý tài khoản admin.edit')){
        $userAdminDetail = Admin::with('roles')->where('id',$id)
        ->first();
        return response()->json([
            'status'=>true,
            'userAdminDetail' => $userAdminDetail,
        ]);
    } else {
        return response()->json([
            'status'=>false,
            'mess' => 'no permission',
        ]);
    }
    }
    public function update($request,$id)
    {
        if(Gate::allows('THÔNG TIN QUẢN TRỊ.Quản lý tài khoản admin.update')){
        $userAdmin = Admin::where('id',$id)->first();
        if(!isset($userAdmin)){
            return response()->json([
                'message'=>'name',
                'status'=>'false'
            ],202);
        }
       // $userAdmin -> username = $request['username']? $request['username']:$userAdmin->username;
        $userAdmin->email = $request['email'] ? $request['email']:$userAdmin->email;
        $userAdmin->display_name = $request['display_name'] ? $request['display_name']: $userAdmin -> display_name ;
        $userAdmin->phone = $request['phone'] ? $request['phone']:$userAdmin ->phone;
        $userAdmin->status = $request['status'] ? $request['status']:$userAdmin->status;
        //$userAdmin->depart_id= $request['depart_id'] ? $request['depart_id']:$userAdmin->depart_id;
        //$userAdmin ->password = Hash::make($request['password'])??$userAdmin ->password;
        $filePath = '';
        //$disPath = public_path();
        if ( $request->avatar!= null && $userAdmin->avatar !=  $request->avatar )
        {
            $DIR ='uploads/admin';
            $httpPost = file_get_contents( 'php://input' );
            $file_chunks = explode( ';base64,', $request->avatar[ 0 ] );
            $fileType = explode( 'image/', $file_chunks[ 0 ] );
            $image_type = $fileType[ 0 ];
            $base64Img = base64_decode( $file_chunks[ 1 ] );
            $data = iconv( 'latin5', 'utf-8', $base64Img );
            $name = uniqid();
            $file = public_path($DIR) . '/' . $name . '.png';
            $filePath = 'admin/'.$name . '.png';
            file_put_contents( $file,  $base64Img );
        }
        else{
            $filePath =  $userAdmin->avatar;
        }
        $userAdmin->avatar=$filePath;
        $userAdmin->save();
        $userAdmin->roles()->sync($request->input('role_id',[]));
        return response()->json([
            'status' => true,
            'displayName' => $userAdmin,
        ]);
    } else {
        return response()->json([
            'status'=>false,
            'mess' => 'no permission',
        ]);
    }
    }
    public function destroy($id){
        if(Gate::allows('THÔNG TIN QUẢN TRỊ.Quản lý tài khoản admin.del')){
        Admin::where("id", $id)->delete();
        return response()->json([
            'status' => true
        ]);
    } else {
        return response()->json([
            'status'=>false,
            'mess' => 'no permission',
        ]);
    }
    }

}
