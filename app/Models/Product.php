<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CategoryDesc;
use App\Models\Category;
use App\Models\ProductDesc;
use App\Models\Brand;
use App\Models\BrandDesc;
use App\Models\ProductPicture;
use App\Models\ProductGroup;
use App\Models\ProductStatusDesc;
use App\Models\ProductStatus;
use App\Models\ProductFlashSale;
use App\Models\ProductProperties;

class Product extends Model
{
    use  HasFactory;
    protected $table = 'products';
    protected $primaryKey = 'product_id';
    protected $fillable = [
        'cat_id','cat_list','maso', 'macn','code_script',
        'picture','price','price_old','brand_id',
        'status','options', 'op_search',
        'cat_search',
        'technology',
        'focus',
        'focus_order',
        'deal',
        'deal_order',
        'deal_data_start',
        'deal_data_end',
        'stock',
        'votes',
        'numvote',
        'menu_order',
        'menu_order_cate_lv0',
        'menu_order_cate_lv1',
        'menu_order_cate_lv2',
        'menu_order_cate_lv3',
        'menu_order_cate_lv4',
        'menu_order_cate_lv5',
        'menu_order_cate_lv6',
        'menu_order_cate_lv7',
        'menu_order_cate_lv8',
        'menu_order_cate_lv9',
        'menu_order_cate_lv10',
        'views',
        'display',
        'date_post',
        'date_update',
        'adminid',
        'url',
    ];

    //Thông số kỹ thuật
    public function technologies()
    {
        return $this->hasMany(ProductProperties::class, 'product_id')
                    ->with(['property', 'propertyValue']);
    }

    public function productProperties()
    {
        return $this->hasManyThrough(
            ProductProperties::class,
            Price::class,
            'product_id',        
            'price_id',          
            'product_id',       
            'id'           
        )->with(['property', 'propertyValue']);
    }

    public function prices()
    {
        return $this->hasMany(Price::class, 'product_id', 'product_id');
    }

    public function price()
    {
        return $this->hasMany(Price::class,'product_id','product_id');
    }

    public function categoryDes()
    {
        return $this->belongsTo(CategoryDesc::class, 'cat_id','cat_id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id','cat_id');
    }

    public function productDesc()
    {
        return $this->hasOne(ProductDesc::class, 'product_id');
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class,'brand_id');
    }
    public function brandDesc()
    {
        return $this->belongsTo(BrandDesc::class,'brand_id');
    }
    public function productPicture()
    {
        return $this->hasMany(ProductPicture::class,'product_id','product_id');
    }
    public function productGroups()
    {
        return $this->hasMany(ProductGroup::class,'product_main','product_id');

    }
    public function productStatusDesc()
    {
        return $this->hasOne(ProductStatusDesc::class, 'status_id','status');
    }
    public function productStatus()
    {
        return $this->hasOne(ProductStatus::class, 'status_id','status');
    }
    public function productFlashSale()
    {
        return $this->belongsTo(ProductFlashSale::class, 'product_id','product_id');
    }

}
