<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponStatus extends Model
{
    use HasFactory;
    protected $table = 'coupon_status';
    protected $primaryKey = 'status_id';
    protected $fillable = [
            'title',
        ];
}
