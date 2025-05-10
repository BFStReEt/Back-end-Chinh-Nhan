<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\BrandDesc;
use App\Models\CategoryDesc;
use App\Models\Coupon;
use App\Models\Present;
use App\Models\Product;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function showPresent()
    {
        try {
            $now = date('d-m-Y H:i:s');
            $stringTime = strtotime($now);

            $listPresent = Present::orderBy('id', 'DESC')
            // ->whereRaw('FIND_IN_SET(?,mem_id)',[auth('member')->user()->mem_id])
                ->where('StartDate', '<=', $stringTime)
                ->where('EndDate', '>=', $stringTime)
                ->get();

            if (count($listPresent) > 0) {
                foreach ($listPresent as $present) {
                    $catId = $present->list_cat;
                    $category = [];
                    $categoryId = explode(',', $catId);
                    $category = CategoryDesc::whereIn('cat_id', $categoryId)->get();

                    $productCode = $present->list_product;
                    $product = [];
                    $productCode = explode(',', $productCode);
                    $product = Product::whereIn('macn', $productCode)->get();

                    $dataForYou[] = [
                        'id' => $present->id,
                        'title' => $present->title,
                        'code' => $present->code,
                        'StartDate' => $present->StartDate,
                        'StartDate' => $present->StartDate,
                        'content' => $present->content,
                        'type' => $present->type,
                        'display' => $present->display,
                        'priceMin' => $present->priceMin,
                        'priceMax' => $present->priceMax,
                        'categoryName' => $category->pluck('cat_name'),
                        'friendlyUrlCat' => $category->pluck('friendly_url'),
                        'productCode' => $product->pluck('macn'),
                        'couponType' => $coupon->CouponType,
                    ];
                }
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function index(Request $request)
    {
        try {
            $now = date('d-m-Y H:i:s');

            $stringTime = strtotime($now);
            $dataForYou = [];
            $dataPublic = [];

            // if(auth('member')->user()->mem_id ) {

            $listCouponForYou = Coupon::with('couponDesc')->orderBy('id', 'DESC')
                ->whereHas('couponDesc', function ($q) {
                    $q->where('SoLanConLaiDes', '>', 0);
                })
                ->where('StartCouponDate', '<=', $stringTime)
                ->where('EndCouponDate', '>=', $stringTime)
                ->get();

            if (count($listCouponForYou) > 0) {
                foreach ($listCouponForYou as $coupon) {
                    if ($request->data) {
                        $listCouponDes = [];
                        foreach ($coupon->couponDesc as $item) {
                            if ($item->MaCouponDes == $request->data) {
                                $listCouponDes[] = $item;
                            }
                        }
                        if (count($listCouponDes) > 0) {
                            $catId = $coupon->DanhMucSpChoPhep;
                            $category = [];

                            $id = explode(',', $catId);
                            $category = CategoryDesc::whereIn('cat_id', $id)->get();

                            $brandId = $coupon->ThuongHieuSPApDung;
                            $brand = [];
                            $id = explode(',', $brandId);
                            $brand = BrandDesc::whereIn('brand_id', $id)->get();
                            $dataForYou = [
                                'id' => $coupon->id,
                                'TenCoupon' => $coupon->TenCoupon,
                                'MaPhatHanh' => $coupon->MaPhatHanh,
                                'StartCouponDate' => $coupon->StartCouponDate,
                                'EndCouponDate' => $coupon->EndCouponDate,
                                'DesCoupon' => $coupon->DesCoupon,
                                'GiaTriCoupon' => $coupon->GiaTriCoupon,
                                'MaxValueCoupon' => $coupon->MaxValueCoupon,
                                'SoLanSuDung' => $coupon->SoLanSuDung,
                                //'MaKhoSPApdung' => $coupon->MaKhoSPApdung,
                                'MaKhoSPApdung' => $coupon->MaKhoSPApdung ? explode(',', $coupon->MaKhoSPApdung) : [],
                                'KHSuDungToiDa' => $coupon->KHSuDungToiDa,
                                'SuDungDongThoi' => $coupon->SuDungDongThoi,
                                'DonHangChapNhanTu' => $coupon->DonHangChapNhanTu,
                                //'categoryName' => $category->pluck('cat_name'),
                                'categoryName' => $category->pluck('cat_name')->isEmpty() ? true : $category->pluck('cat_name'),
                                'friendlyUrlCat' => $category->pluck('friendly_url'),
                                'brandName' => $brand->pluck('title'),
                                'brandFriendlyUrl' => $brand->pluck('friendly_url'),
                                'dataCouponDesc' => $listCouponDes ?? null,
                                'couponType' => $coupon->CouponType,
                            ];

                        }
                    } else {

                        $catId = $coupon->DanhMucSpChoPhep;
                        $category = [];

                        $id = explode(',', $catId);
                        $category = CategoryDesc::whereIn('cat_id', $id)->get();

                        $brandId = $coupon->ThuongHieuSPApDung;
                        $brand = [];

                        $id = explode(',', $brandId);
                        $brand = BrandDesc::whereIn('brand_id', $id)->get();

                        $listCouponDes = [];
                        // if($request->data){
                        //     foreach($coupon->couponDesc as $item){
                        //         if($item->MaCouponDes==$request->data){
                        //             $listCouponDes[]=$item;
                        //         }
                        //     }
                        // }
                        $dataForYou[] = [
                            'id' => $coupon->id,
                            'TenCoupon' => $coupon->TenCoupon,
                            'MaPhatHanh' => $coupon->MaPhatHanh,
                            'StartCouponDate' => $coupon->StartCouponDate,
                            'EndCouponDate' => $coupon->EndCouponDate,
                            'DesCoupon' => $coupon->DesCoupon,
                            'GiaTriCoupon' => $coupon->GiaTriCoupon,
                            'MaxValueCoupon' => $coupon->MaxValueCoupon,
                            'SoLanSuDung' => $coupon->SoLanSuDung,
                            //'MaKhoSPApdung' => $coupon->MaKhoSPApdung,
                            'MaKhoSPApdung' => $coupon->MaKhoSPApdung ? explode(',', $coupon->MaKhoSPApdung) : [],
                            'KHSuDungToiDa' => $coupon->KHSuDungToiDa,
                            'SuDungDongThoi' => $coupon->SuDungDongThoi,
                            'DonHangChapNhanTu' => $coupon->DonHangChapNhanTu,
                            //'categoryName' => $category->pluck('cat_name'),
                            'categoryName' => $category->pluck('cat_name')->isEmpty() ? true : $category->pluck('cat_name'),
                            'friendlyUrlCat' => $category->pluck('friendly_url'),
                            'brandName' => $brand->pluck('title'),
                            'brandFriendlyUrl' => $brand->pluck('friendly_url'),
                            'dataCouponDesc' => $coupon->couponDesc ?? null,
                            'couponType' => $coupon->CouponType,
                        ];
                    }
                }
            } else {
                $listCouponPublic = Coupon::with('couponDesc')->orderBy('id', 'DESC')
                    ->where('StartCouponDate', '<=', $stringTime)
                    ->where('EndCouponDate', '>=', $stringTime)->get();
                if (count($listCouponPublic) > 0) {
                    foreach ($listCouponPublic as $coupon) {
                        $catId = $coupon->DanhMucSpChoPhep;
                        $category = [];

                        $id = explode(',', $catId);
                        $category = CategoryDesc::whereIn('cat_id', $id)->get();

                        $brandId = $coupon->ThuongHieuSPApDung;
                        $brand = [];

                        $id = explode(',', $brandId);
                        $brand = BrandDesc::whereIn('brand_id', $id)->get();

                        // $dataPublic[] = [
                        //     'id' => $coupon->id,
                        //     'TenCoupon' => $coupon->TenCoupon,
                        //     'MaPhatHanh' => $coupon->MaPhatHanh,
                        //     'StartCouponDate' => $coupon->StartCouponDate,
                        //     'EndCouponDate' => $coupon->EndCouponDate,
                        //     'DesCoupon' => $coupon->DesCoupon,
                        //     'GiaTriCoupon' => $coupon->GiaTriCoupon,
                        //     'MaxValueCoupon' => $coupon->MaxValueCoupon,
                        //     'MaKhoSPApdung' => $coupon->MaKhoSPApdung,
                        //     'SoLanSuDung' => $coupon->SoLanSuDung,
                        //     'KHSuDungToiDa' => $coupon->KHSuDungToiDa,
                        //     'SuDungDongThoi' => $coupon->SuDungDongThoi,
                        //     'DonHangChapNhanTu' => $coupon->DonHangChapNhanTu,
                        //     'categoryName' => $category->pluck('cat_name'),
                        //     'friendlyUrlCat' => $category->pluck('friendly_url'),
                        //     'brandName' => $brand->pluck('title'),
                        //     'brandFriendlyUrl' => $brand->pluck('friendly_url'),
                        //     'dataCouponDesc' => $coupon->couponDesc?? null,
                        //     'couponType' =>$coupon->CouponType,
                        // ];
                    }
                }
            }

            return response()->json([
                'status' => true,
                'data' => $dataForYou,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

}
