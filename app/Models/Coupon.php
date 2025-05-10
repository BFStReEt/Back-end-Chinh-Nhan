<?php

namespace App\Models;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CouponDes;
use App\Models\CouponStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;
    protected $table = 'coupon';
    protected $primaryKey = 'id';
    protected $timestamp = true;
    protected $casts = [
        'DanhMucSpChoPhep' => 'array',
        'ThuongHieuSPApDung' => 'array'
    ];

    protected $fillable = [
        'TenCoupon ',
        'MaPhatHanh ',
        'StartCouponDate ',
        'EndCouponDate ',
        'DesCoupon ',
        'GiaTriCoupon ',
        'SoLanSuDung ',
        'KHSuDungToiDa ',
        'DonHangChapNhanTu ',
        'DanhMucSpChoPhep ',
        'ThuongHieuSPApDung ',
        'LoaiKHSuDUng ',
        'DateCreateCoupon ',
        'status_id',
        'MaKhoSPApdung ',
        'IDAdmin',
        'mem_id','user_for','SoLuongMa'
    ];

    public function couponDesc()
    {
        return $this->hasMany(CouponDes::class,'idCoupon','id');
    }
    public function couponStatus()
    {
        return $this->belongsTo(CouponStatus::class,'status_id');
    }
    public function couponBrand()
    {
        return $this->belongsTo(Brand::class,'ThuongHieuSPApDung','brand_id');
    }
    public function couponCategory()
    {
        return $this->belongsTo(Category::class,'DanhMucSpChoPhep','cat_id');
    }
}
