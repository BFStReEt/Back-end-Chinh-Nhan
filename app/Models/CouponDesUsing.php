<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderSum;
use App\Models\CouponDes;

class CouponDesUsing extends Model
{
    use HasFactory;
    protected $table = 'coupondesusing';
    protected $fillable = [
        'IDuser',
        'idCouponDes ',
        'DateUsingCode',
        'IDOrderCode',
        'MaCouponUSer',
    ];
    public function couponDes()
    {
        return $this->belongsTo(CouponDes::class,'idCouponDes','idCouponDes');
    }
}
