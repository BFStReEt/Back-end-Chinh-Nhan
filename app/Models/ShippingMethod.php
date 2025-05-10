<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use HasFactory;
    protected $table = 'shipping_method';
    protected $primaryKey = 'shipping_id';
    protected $fillable = [ 'title', 'name', 'description','price',
    'discount','status','s_type','s_time','menu_order','display','date_post','date_update','lang'];

}
