<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductStatusDesc;
class ProductStatus extends Model
{
    use  HasFactory;
    protected $table = 'product_status';
    protected $primaryKey = 'status_id';
    protected $fillable = [
        'picture','name','show_home', 'width','height',
        'views','menu_order','display','date_post',
        'date_update','adminid'
    ];
    public function productStatusDesc()
    {
        return $this->hasOne(ProductStatusDesc::class, 'status_id');
    }

}
