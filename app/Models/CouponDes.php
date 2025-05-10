<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CouponDesUsing;
use App\Models\Coupon;
class CouponDes extends Model
{
    use HasFactory;
    protected $table = 'coupondes';
    protected $primaryKey = 'idCouponDes';
    protected $fillable = [
        'idCouponDes',
        'MaCouponDes',
        'SoLanSuDungDes',
        'SoLanConLaiDes',
        'StatusDes',
        'DateCreateDes',
        'idCoupon',
        'Max',
    ];

    public function couponDesUsing()
    {
        return $this->hasMany(CouponDesUsing::class,'idCouponDes','idCouponDes');
    }
    public function coupon()
    {
        return $this->belongsTo(Coupon::class,'idCoupon','id');
    }

}
