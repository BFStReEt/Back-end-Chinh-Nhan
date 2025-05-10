<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\ProductPicture;

class ProductFlashSale extends Model
{
    protected $table = 'product_flash_sale';
    protected $primaryKey = 'id';
    use  HasFactory;
    protected $fillable = [
        'product_id',
        'price',
        'price_old',
        'discount_percent',
        'discount_price',
        'start_time',
        'end_time',
        'status',
        'adminid'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id','product_id');
    }
}
