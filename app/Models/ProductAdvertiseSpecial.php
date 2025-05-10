<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAdvertiseSpecial extends Model
{
    use HasFactory;
    protected $table = 'product_advertise_special';
    protected $primaryKey = 'id';
    protected $fillable = [
        'itemID',
        'type',
        'status',
        'background',
        'pos',
        'cat_id',
        'picture',
        'link',
        'title',
        'description',
        'target',
        'height',
        'width',
        'display',
        'menu_order',
        'date_post',
        'date_update',
        'lang',
        'adminid',
    ];
}
