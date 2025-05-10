<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderStatus;
use App\Models\OrderDetail;
use App\Models\Member;
use App\Models\InvoiceOrder;
use App\Models\ShippingMethod;
use App\Models\PaymentMethod;
use App\Models\CouponDesUsing;
class OrderSum extends Model
{
    use HasFactory;
    protected $table = 'order_sum';
    protected $primaryKey = 'order_id';
    protected $timestamp = true;
    protected $fillable = ['order_code','MaKH','d_name','d_address','d_country',
    'd_city','d_phone','d_email','c_name','c_address','c_country','c_city','c_phone',
'c_email','s_price','total_cart','total_price','shipping_method','payment_method',
'status','date_order','ship_date','comment','note','menu_order','display','lang','mem_id',
'CouponDiscout','diem_use','diem_tich','status_diem','time_pay','time_deli','time_done',
'userManual','accumulatedPoints','accumulatedPoints_1','date_order_status1','date_order_status2',
'date_order_status3','date_order_status4','date_order_status5','date_order_status6','date_order_status7','list_group_product'];
    public function orderStatus()
    {
        return $this->belongsTo(OrderStatus::class, 'status','status_id');
    }
    public function orderDetail()
    {
        return $this->hasMany(OrderDetail::class, 'order_id','order_id');
    }
    public function orderAddress()
    {
        return $this->belongsTo(OrderAddress::class, 'order_id','order_id');
    }
    public function invoiceOrder()
    {
        return $this->belongsTo(InvoiceOrder::class, 'order_id','order_id');
    }
    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method','name');
    }
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method','name');
    }
    public function member()
    {
        return $this->belongsTo(Member::class, 'mem_id','id');
    }


}
