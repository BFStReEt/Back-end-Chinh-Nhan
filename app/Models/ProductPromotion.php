<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
class ProductPromotion extends Model
{
    protected $table = 'product_promotion';
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
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id','product_id');
    }
}
