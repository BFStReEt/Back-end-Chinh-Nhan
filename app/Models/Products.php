<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductDescs;
use App\Models\ProductPicture;
use App\Models\Price;
class Products extends Model
{
    use  HasFactory;
    protected $table = 'products';
    protected $primaryKey = 'product_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cat_id','cat_list','maso', 'macn','code_script',
        'picture','price','price_old','brand_id',
        'status','options', 'op_search',
        'cat_search',
        'picture',
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
        'news_list',
        'position_page', 'event','priceEvent','priceOldEvent'
    ];
    public function productDescs()
    {
        return $this->hasOne(ProductDescs::class,'product_id','product_id');
    }
    public function priceList()
    {
        return $this->hasMany(Price::class,'product_id','product_id');
    }
    public function productPicture()
    {
        return $this->hasMany(ProductPicture::class,'product_id','product_id');
    }
    public function price()
    {
        return $this->hasOne(Price::class, 'product_id', 'product_id');
    }

}
