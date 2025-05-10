<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Mail\ForgetPassword;
use App\Mail\TestMail;
use App\Models\Address;
use App\Models\MailTemplate;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class MemberController extends Controller
{
    public function login(Request $request)
    {
        try {
            $member = Member::where('username', $request->username)
                ->first();

            if (!$member) {
                return response()->json([
                    'status' => false,
                    'message' => 'userNotExist',
                ]);
            }
            $abbreviation = "";
            $string = ucwords($member->password);
            $words = explode(" ", "$string");
            foreach ($words as $word) {
                $abbreviation .= $word[0];
            }

            if (isset($member) && $abbreviation != "$" && Hash::check($request->password, $member->password) == false) {
                Member::where('id', $member->id)->first()->update(['password' => Hash::make($request->password)]);
            }

            if ($member && $abbreviation == "$" && Hash::check($request->password, $member->password)) {
                // //Nên xóa sau khi test
                // $token = $member->createToken('auth_token')->plainTextToken;
                return response()->json([
                    'status' => true,
                    'member' => $member,
                    //'token' => $token,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'wrongPassword',
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function register(Request $request)
    {
        try {
            $date = Carbon::now('Asia/Ho_Chi_Minh');
            $timestamp = strtotime($date);
            // check null fill

            $username = isset($request->accountName) ? $request->accountName : '';
            $password = isset($request->password) ? $request->password : '';

            $full_name = isset($request->fullName) ? $request->fullName : '';

            $email = isset($request->email) ? $request->email : '';
            $gender = isset($request->gender) ? $request->gender : '';

            $phone = isset($request->numberPhone) ? $request->numberPhone : '';

            // $tencongty = isset($request->tencongty) ? $request->tencongty : '';
            // $masothue = isset($request->masothue) ? $request->masothue : '';
            // $emailcty = isset($request->emailcty) ? $request->emailcty : '';
            // $diachicongty = isset($request->diachicongty) ? $request->diachicongty : '';
            // $sdtcongty = isset($request->sdtcongty) ? $request->sdtcongty : '';
            // $address =  isset($request->address) ? $request->address : '';
            // $company =  isset($request->company) ? $request->company : '';
            // $district =  isset($request->district) ? $request->district : '';
            // $ward =  isset($request->ward) ? $request->ward : '';
            // $cityProvince =  isset($request->city_province) ? $request->city_province : '';
            // $MaKH = isset($request->MaKH) ? $request->MaKH : '';

            $isExistEmail = Member::where("email", $email)
                ->first();

            // $isExistMst = Member::where("masothue", $masothue)
            // ->first();
            $isExistUsername = Member::where("username", $username)
                ->first();
            // if($masothue != '') {
            //     if($isExistMst) {
            //         return response()->json(['message'=>'existMST', 'status' => false]);
            //     }
            // }
            if ($isExistUsername) {
                return response()->json(['message' => 'existUserName', 'status' => false]);
            }
            if ($isExistEmail) {
                return response()->json(['message' => 'existEmail', 'status' => false]);
            } else {

                $member = Member::create([
                    'provider' => 'credentials',
                    'username' => $username,
                    'mem_code' => '',
                    'gender' => $gender,
                    'email' => $email,
                    'password' => Hash::make($password),
                    // 'address' => $address,
                    // 'company' => $company,
                    'full_name' => $full_name,
                    'avatar' => '',
                    'phone' => $phone,
                    // 'provider' =>'',
                    'provider_id' => '',
                    // 'Tencongty' => $tencongty,
                    // 'Masothue' => $masothue,
                    // 'Diachicongty' => $diachicongty,
                    // 'Sdtcongty' => $sdtcongty,
                    // 'emailcty' => $emailcty,
                    // 'MaKH' => $MaKH,
                    // 'ward' => $ward,
                    // 'district' => $district,
                    // 'city_province' => $cityProvince,
                    'date_join' => $timestamp,
                    'm_status' => 0,
                    'status' => 0,

                ]);
                $to = $email;
                $data = [
                    'subject' => 'ViTinhNguyenKim',
                    'body' => 'Cảm ơn bạn đã đăng ký tài khoản thành công tại Công Nghệ Chính Nhân. ',
                ];

                // Mail::to($email)
                // ->send(new MailNotifyMember($data));

                $MailTemplate = MailTemplate::where('name', 'activate_code')->first();
                $dataEmail = [
                    'link_activate' => 'http://web.chinhnhan.com/login',
                    'html' => $MailTemplate->description,
                ];
                $dataEmail['html'] = str_replace(
                    ['[link_activate]',
                    ],
                    [$dataEmail['link_activate'],
                    ],
                    $dataEmail['html']
                );
                Mail::to($member->email)->send(new TestMail($dataEmail));

                return response()->json([
                    'message' => 'Đăng ký thành công',
                    'data' => [
                        // 'Id' => $member->MaKH,
                        'TenDD' => $member->username,
                        'Email' => $member->email,
                        'Phone' => $member->phone,
                    ],
                    'status' => true,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

    }
    public function information(Request $request, $id)
    {
        try
        {
            $memberId = $id;
            if ($memberId) {
                // return Auth::guard('member')->user();
                $member = Member::find($memberId);
                return response()->json([
                    'status' => true,
                    'member' => $member,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function updateInfoMember(Request $request, $id)
    {
        try {

            $memberId = $id;
            if ($memberId) {
                $member = Member::find($memberId);
                $member->full_name = $request->FullName ? $request->FullName : $member->full_name;
                $member->phone = $request->Phone ? $request->Phone : $member->phone;
                $member->gender = $request->Gender ? $request->Gender : $member->gender;
                //return $member->provider;
                if (!($member->provider == "google" || $member->provider == "facebook")) {

                    $member->email = $request->Email ? $request->Email : $member->email;
                }

                $member->dateOfBirth = $request->DateOfBirth ? $request->DateOfBirth : $member->dateOfBirth;
                $member->save();
                return response()->json([
                    'status' => true,
                    'member' => $member,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function showAddressMember(Request $request, $id, $status)
    {
        try {
            $address = null;
            if ($status == "main") {
                $address = Address::where('mem_id', $id)->where('status', 1)->first();
            } else if ($status == "all") {
                $address = Address::where('mem_id', $id)->get();
            }
            return response()->json([
                'status' => true,
                'address' => $address,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function updateAddressMember(Request $request, $id)
    {
        try {
            $memberId = $id;
            $addressId = $request->addressId;

            $address = Address::where('id', $addressId)->first();

            if ($request->Status == 1) {

                $listAddress = Address::where('mem_id', $memberId)
                    ->where('id', '!=', $addressId)->where('status', 1)->first();
                if ($listAddress) {
                    $listAddress->status = 0;
                    $listAddress->save();
                }

            }

            if (isset($address)) {

                $address->gender = $request->Gender ?? $address->gender;
                $address->fullName = $request->FullName ?? $address->fullName;
                $address->Phone = $request->Phone ?? $address->Phone;
                $address->address = $request->Address ?? $address->address;
                $address->province = $request->Province ?? $address->province;
                $address->district = $request->District ?? $address->district;
                $address->ward = $request->Ward ?? $address->ward;
                $address->status = $request->Status ?? $address->status;
                $address->email = $request->Email ?? $address->Email;
                $address->save();
            } else {

                $address = new Address();
                $address->mem_id = $memberId;
                $address->gender = $request->Gender;
                $address->fullName = $request->FullName;
                $address->Phone = $request->Phone;
                $address->address = $request->Address;
                $address->province = $request->Province;
                $address->district = $request->District;
                $address->ward = $request->Ward;
                $address->status = $request->Status;
                $address->email = $request->Email;
                $address->save();
            }

            return response()->json([
                'status' => true,
                'address' => $address,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function deleteAddressMember(Request $request, $id)
    {
        try {

            $address = Address::where('id', $id)->first();
            if ($address) {
                $address->delete();
            }
            return response()->json([
                'status' => true,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function changePassword(Request $request, $id)
    {
        try {
            $memberId = $id;
            if ($memberId) {
                $member = Member::where('id', $memberId)
                    ->first();

                if (!$member) {
                    return response()->json([
                        'status' => false,
                        'message' => 'userNotExist',
                    ]);
                }

                if ($member && Hash::check($request->currentPassword, $member->password)) {

                    $member->password = Hash::make($request->newPassword) ?? $member->password;
                    $member->save();
                    return response()->json([
                        'status' => true,
                        'message' => 'changePassword success',
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'changePassword fail',
                    ]);
                }

            }

            //$memberId->password      = $data['password']?Hash::make($data['password']):Auth::guard('member')->user()->password ;
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function forgetPassword(Request $request)
    {
        try
        {

            $random = implode('', array_rand(array_flip(range(0, 9)), 6));

            $email = $request->input('email');
            $passwordToken = $random;
            $member = Member::where('email', '=', $email)
                ->where('provider', 'credentials')
                ->first();
            if (!$member) {
                return response()->json([
                    'status' => false,
                    'message' => 'notCredentials',
                ]);
            }
            $member->password_token = $passwordToken;
            $member->save();
            $data = [
                'id_member' => $member->id,
                'username' => $member->username,
                'password_token' => $passwordToken,
            ];

            Mail::to($email)->send(new ForgetPassword($data));
            return response()->json(['message' => 'send email success', 'status' => true]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function forgetPasswordChange(Request $request)
    {
        try {

            $MailTemplate = MailTemplate::where('name', 'forget_pass')->first();

            $passwordToken = $request->input('OTP');
            $passwordNew = Hash::make($request->input('password'));
            $member = Member::where('password_token', $passwordToken)->first();
            if (!$member) {
                return response()->json(['error' => 'OTP not found'], 404);
            }
            $member->password = $passwordNew;
            $member->password_token = null;
            $member->save();

            $dataEmail = [
                'domain' => 'http://web.chinhnhan.com/',
                'username' => $member->username,
                'password' => $request->input('password'),
                'link' => 'http://web.chinhnhan.com/login',
                'date' => Carbon::now('Asia/Ho_Chi_Minh')->isoFormat('DD/MM/YYYY'),
                'html' => $MailTemplate->description,
            ];
            $dataEmail['html'] = str_replace(
                ['[domain]', '[username]', '[password]', '[link]', '[date]',
                ],
                [$dataEmail['domain'], $dataEmail['username'], $dataEmail['password'],
                    $dataEmail['link'], $dataEmail['date'],
                ],
                $dataEmail['html']
            );
            Mail::to($member->email)->send(new TestMail($dataEmail));

            return response()->json(['message' => 'change success', 'status' => true]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    public function index()
    {
        return 111;
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
