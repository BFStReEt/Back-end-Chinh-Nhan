<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftPromotion extends Model
{
    protected $table = 'gift_promotion';
    protected $primaryKey = 'id';
    protected $fillable = [ 'title','code','type','list_cat','list_product','content', 'display','priceMin','priceMax','StartDate','EndDate','list_product','cat_parent_id'];
}
