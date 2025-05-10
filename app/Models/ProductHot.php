<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
class ProductHot extends Model
{
    protected $table = 'product_hot';
    protected $primaryKey = 'id';
    use  HasFactory;
    protected $fillable = [
        'product_id',
        'price',
        'price_old',
        'discount_percent',
        'discount_price',
        'time',
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
