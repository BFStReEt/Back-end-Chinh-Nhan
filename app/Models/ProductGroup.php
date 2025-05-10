<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
class ProductGroup extends Model
{
    use HasFactory;
    protected $table = 'product_group';
    protected $primaryKey = 'id_group';
    protected $fillable = [
        'product_main', 'product_child','date_post','date_update','titleGroup','date_start','date_end','discount'
    ];
    public function product()
    {
        return $this->belongsTo(Product::class,'product_main','product_id');
    }
    public function productChild()
    {
        return $this->belongsTo(Product::class,'product_child','product_id');
    }

}
