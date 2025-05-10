<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Member;
use App\Models\ProductDesc;
use App\Models\Product;
use App\Models\ProductGroup;
class ListCart extends Model
{
    protected $table = "list_cart";
    public $timestamps = true;
    use  HasFactory;

    protected $fillable = [
        'mem_id',
        'mem_name',
        'md5_id',
        'id_group',
        'product_id',
        'stock',
        'quality',
        'title',
        'status',
        'price',
        'priceFlashSale'

    ];
    protected $hidden = [
        'password', 'remember_token',
    ];
    public function member()
    {
        return $this->belongsTo(Member::class,'mem_id');
    }
    public function productDesc()
    {
        return $this->belongsTo(ProductDesc::class,'product_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id','product_id');
    }
    public function groupProduct()
    {
        return $this->belongsTo(ProductGroup::class,'id_group','id_group');
    }
}
