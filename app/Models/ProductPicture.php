<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
class ProductPicture extends Model
{
    use HasFactory;
    protected $table='product_pictured';
    protected $primaryKey = 'id';
    protected $fillable = [
        'product_id',
        'pic_name',
        'picture',
        'menu_order',
        'display',
        'date_post',
        'date_update',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class,'product_id', 'product_id');
    }
}
