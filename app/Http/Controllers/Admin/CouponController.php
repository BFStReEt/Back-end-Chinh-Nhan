<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandDesc;
use App\Models\Category;
use App\Models\CategoryDesc;
use App\Models\Coupon;
use App\Models\CouponDes;
use App\Models\CouponDesUsing;
use App\Models\CouponStatus;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        try {
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' => $stringTime,
                'ip' => $request->ip(),
                'action' => 'show all coupon',
                'cat' => 'coupon',
            ]);

            if (Gate::allows('Quản Lý Coupon.Quản lý coupon.manage')) {
                $dataSearch = $request->input('data');
                $offset = $request->page ? $request->page : 1;

                $coupon = Coupon::with('couponDesc', 'couponStatus')
                    ->orderBy('id', 'DESC');

                if ($request->data == 'undefined' || $dataSearch == "") {
                    $coupon = $coupon;
                } else {
                    $coupon = $coupon->where('MaPhatHanh', 'like', '%' . $dataSearch . '%');
                }
                if ($request->StartCouponDate && $request->EndCouponDate) {
                    //return $request->StartCouponDate;
                    $start = $request->StartCouponDate;
                    $end = $request->EndCouponDate;
                    $coupon = $coupon->whereBetween('DateCreateCoupon', [$start, $end]);
                }
                $countCoupon = count($coupon->get());

                $listCoupon = $coupon->limit(10)
                    ->offset(($offset - 1) * 10)->get();

                $data = [];

                foreach ($listCoupon as $coupon) {
                    // $memId = $coupon->mem_id;
                    // $members=[];
                    // if(is_string($memId)){
                    //     $id = explode(',',$memId);
                    //     $members = Member::whereIn('mem_id',$id)->get();
                    // }else{
                    //     $members = Member::where('mem_id',$memId)->get();
                    // }
                    // $isMemIdContained = false;
                    // foreach ($members as $member) {
                    //     if ($member->mem_id == $memId) {
                    //         $isMemIdContained = true;
                    //         break;
                    //     }
                    // }
                    $data[] = [
                        'id' => $coupon->id,
                        'couponName' => $coupon->TenCoupon,
                        'MaPhatHanh' => $coupon->MaPhatHanh,
                        'StartCouponDate' => $coupon->StartCouponDate,
                        'EndCouponDate' => $coupon->EndCouponDate,
                        'DesCoupon' => $coupon->DesCoupon,
                        'GiaTriCoupon' => $coupon->GiaTriCoupon,
                        'MaxValueCoupon' => $coupon->MaxValueCoupon,
                        'SoLanSuDung' => $coupon->SoLanSuDung,
                        'KHSuDungToiDa' => $coupon->KHSuDungToiDa,
                        'SuDungDongThoi' => $coupon->SuDungDongThoi,
                        'DonHangChapNhanTu' => $coupon->DonHangChapNhanTu,
                        'DanhMucSpChoPhep' => $coupon->DanhMucSpChoPhep,
                        'ThuongHieuSPApDung' => $coupon->ThuongHieuSPApDung,
                        'LoaiKHSuDung' => $coupon->LoaiKHSuDung,
                        'mem_id' => $coupon->mem_id,
                        'status' => $coupon->status_id,
                        'SoLuongMa' => $coupon->SoLuongMa,
                        'DateCreateCoupon' => $coupon->DateCreateCoupon,
                        'MaKhoSPApdung' => $coupon->MaKhoSPApdung,
                        'couponDescription' => $coupon->coupon_desc,
                        'title' => $coupon->couponStatus->title ?? null,
                        'colorStatus' => $coupon->couponStatus->color ?? null,
                        'couponBrand' => $coupon->couponBrand->brandDesc->title ?? null,
                        'couponCategory' => $coupon->category->categoryDesc->cat_name ?? null,
                        // 'isMemIdContained' => $isMemIdContained,
                        // 'members' => $members->pluck('username')->toArray(),
                    ];
                }

                return response()->json([
                    'listCoupon' => $data,
                    'countCoupon' => $countCoupon,

                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'mess' => 'no permission',
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
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
            'time' => $stringTime,
            'ip' => $request->ip(),
            'action' => 'add a coupon',
            'cat' => 'coupon',
        ]);
        try {
            if (Gate::allows('Quản Lý Coupon.Quản lý coupon.add')) {
                $data = $request->all();

                $check = Coupon::where('TenCoupon', $request->TenCoupon)->orWhere('MaPhatHanh', $request->MaPhatHanh)->first();
                if ($check != '' && $check->TenCoupon == $request->TenCoupon) {
                    return response()->json([
                        'message' => 'tencoupon',
                        'status' => 'false',
                    ]);
                }
                if ($check != '' && $check->MaPhatHanh == $request->MaPhatHanh) {
                    return response()->json([
                        'message' => 'maphathanh',
                        'status' => 'false',
                    ]);
                }

                $now = Carbon::now();
                $date = Carbon::now('Asia/Ho_Chi_Minh');
                $coupon = new Coupon();
                $coupon->TenCoupon = $data['TenCoupon'];
                $coupon->MaPhatHanh = $data['MaPhatHanh'];
                $coupon->StartCouponDate = strtotime($data['StartCouponDate']);
                $coupon->EndCouponDate = strtotime($data['EndCouponDate']);
                $coupon->DesCoupon = $data['DesCoupon'];
                $coupon->GiaTriCoupon = $data['GiaTriCoupon'];
                $coupon->SoLanSuDung = $data['SoLanSuDung'];
                $coupon->KHSuDungToiDa = $data['KHSuDungToiDa'] ? $data['KHSuDungToiDa'] : 0;
                $coupon->DonHangChapNhanTu = $data['DonHangChapNhanTu'] ? $data['DonHangChapNhanTu'] : 0;
                $coupon->status_all_member = 0;
                // if(!is_null($data['mem_id']) )
                // {
                //     $coupon->mem_id = implode(',',$data['mem_id']);
                // }else{
                //     $coupon -> mem_id = 0;
                // }
                if (!is_null($data['DanhMucSpChoPhep'])) {
                    if (count($data['DanhMucSpChoPhep']) > 0) {
                        $coupon->DanhMucSpChoPhep = implode(',', $data['DanhMucSpChoPhep']);
                    } else {
                        $coupon->DanhMucSpChoPhep = implode(',', $data['cat_parent_id']);
                    }
                } else {
                    $coupon->DanhMucSpChoPhep = null;
                }
                if (!is_null($data['ThuongHieuSPApDung'])) {
                    $coupon->ThuongHieuSPApDung = implode(',', $data['ThuongHieuSPApDung']);
                } else {
                    $coupon->ThuongHieuSPApDung = null;
                }
                $coupon->LoaiKHSuDUng = 1;
                $coupon->DateCreateCoupon = strtotime($date);
                $coupon->MaKhoSPApdung = isset($request->MaKhoSPApdung) ? $data['MaKhoSPApdung'] : 0;
                $coupon->IDAdmin = isset($data['IDAdmin']) ? $data['IDAdmin'] : 0;
                $coupon->status_id = $data['status_id'];
                $coupon->CouponType = isset($data['type']) ? $data['type'] : 0;
                $coupon->SoLuongMa = $data['SoLuongMa'] ?? 1;
                $coupon->save();
                $couponDes = "";

                // if($coupon ->CouponType == 0) {
                $prefixMaCoupon = isset($data['prefix']) ? $data['prefix'] : '';
                $suffixesMaCoupon = isset($data['suffixes']) ? $data['suffixes'] : '';

                switch ($data['number']) {
                    case 1:
                        $result = 9;
                        break;
                    case 2:
                        $result = 99;
                        break;
                    case 3:
                        $result = 999;
                        break;
                    case 4:
                        $result = 9999;
                        break;
                    case 5:
                        $result = 99999;
                        break;
                    case 6:
                        $result = 999999;
                        break;
                    default:
                        exit;
                }

                for ($i = 0; $i < $data['SoLuongMa']; $i++) {
                    $MaCouponDes = $prefixMaCoupon . '' . rand(0, $result) . '' . $suffixesMaCoupon;
                    $couponDes = new CouponDes();
                    $couponDes->MaCouponDes = $MaCouponDes;
                    $couponDes->SoLanSuDungDes = 1;
                    $couponDes->SoLanConLaiDes = $data['SoLanSuDung'];
                    $couponDes->StatusDes = $request->StatusDes ? $data['StatusDes'] : 0;
                    $couponDes->DateCreateDes = $date;
                    $couponDes->idCoupon = $coupon->id;
                    $couponDes->save();
                }
                // }
                $dataSocket = [
                    'type' => 'coupon',
                    'socketId' => rand(9, 9999)
                    . Carbon::now('Asia/Ho_Chi_Minh')->isoFormat('DDMMYYYY'),
                    'titleCoupon' => $coupon->TenCoupon,
                    'priceCoupon' => $coupon->GiaTriCoupon,
                    'codeCoupon' => $coupon->MaPhatHanh,
                    'typeCoupon' => $coupon->CouponType = 0 ? 'Giảm Tổng Đơn Hàng' : 'Giảm Theo Mã Hàng',
                    'date' => $date,
                    'seen' => false,
                ];
                try {
                    $message = json_encode($dataSocket);
                    //$message=$dataSocket;
                    // return $message;

                    $endpoint = 'http://socket.chinhnhan.net/api/notifies';
                    $endpoint .= '?message=' . urlencode($message);

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
                        //echo "cURL Error: " . $error;
                    }

                } catch (Exception $e) {
                    return ['error' => $e->getMessage()];
                }

                return response()->json([
                    'coupon' => $coupon,
                    // 'couponDes' => $couponDes,
                    // 'members' => $members,
                    'status' => true,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'mess' => 'no permission',
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
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
    public function edit(Request $request, string $id)
    {
        try {
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);
            DB::table('adminlogs')->insert([
                'admin_id' => Auth::guard('admin')->user()->id,
                'time' => $stringTime,
                'ip' => $request->ip(),
                'action' => 'edit a coupon',
                'cat' => 'coupon',
            ]);
            if (Gate::allows('Quản Lý Coupon.Quản lý coupon.edit')) {
                //$couponUsing = CouponDesUsing::with('couponDesUsing')->get();
                $listCoupon = Coupon::with('couponDesc')->find($id);
                //$couponUsing = CouponDesUsing::where('idCouponDes')->get();

                //member
                // $member = explode(",",$listCoupon->mem_id);
                // $savemember =[];
                // if($listCoupon->mem_id!="")
                // {
                //     foreach ($member as $value) {
                //         $savemember[] = Member::select('MaKH')->whereRaw('FIND_IN_SET(?, mem_id)', [$value])->first();
                //     }
                // }
                // $listCoupon['mem_id'] = $savemember;

                //brand
                $brand = explode(",", $listCoupon->ThuongHieuSPApDung);
                $savebrand = [];
                if ($listCoupon->ThuongHieuSPApDung != "") {
                    foreach ($brand as $value) {
                        $savebrand[] = BrandDesc::select('title')->whereRaw('FIND_IN_SET(?, brand_id)', [$value])->first();
                    }
                }
                $listCoupon['ThuongHieuSPApDung'] = $savebrand;

                //category
                $category = explode(",", $listCoupon->DanhMucSpChoPhep);
                $savecategory = [];
                if ($listCoupon->DanhMucSpChoPhep != "") {
                    foreach ($category as $value) {
                        $savecategory[] = CategoryDesc::select('cat_name')->whereRaw('FIND_IN_SET(?, cat_id)', [$value])->first();
                    }
                }
                $listCoupon['DanhMucSpChoPhep'] = $savecategory;

                //$CouponId = DB::table('coupon','coupon.couponDesc')->where('id',$id)->first();

                //    $listCoupon['couponUsing']= [$couponUsing];
                return response()->json([
                    'listCoupon' => $listCoupon,
                    //'CouponId' => $CouponId,

                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'mess' => 'no permission',
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }

    }
    public function getCouponDetail($code)
    {
        try {

            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);

            $couponUsing = CouponDesUsing::where('MaCouponUSer', $code)->get();

            if (count($couponUsing) != 0) {

                $CouponDes = CouponDes::where('idCouponDes', $couponUsing[0]->idCouponDes)->first();
                if (isset($CouponDes)) {

                    $Coupon = Coupon::where('id', $CouponDes->idCoupon)
                        ->where('StartCouponDate', '<=', $stringTime)
                        ->where('EndCouponDate', '>=', $stringTime)
                        ->first();
                    // return $Coupon;
                }

                //return $CouponDes;
            }
            $DateCreateDes = CouponDes::where('MaCouponDes', $code)->first();
            $status = Coupon::where('id', $DateCreateDes->idCoupon)
                ->where('StartCouponDate', '<=', $stringTime)
                ->where('EndCouponDate', '>=', $stringTime)
                ->first();

            //return $couponUsing;
            return response()->json([
                'status' => true,
                'data' => $couponUsing,
                'codeCoupon' => $code,
                'DateCreateCoupon' => isset($CouponDes) ? $CouponDes->DateCreateDes : $DateCreateDes->DateCreateDes,
                'status' => isset($status) ? 1 : 0,
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }

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
