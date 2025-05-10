<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\ProductPicture;
class ProductDesc extends Model
{
    protected $table = 'product_descs';
    protected $primaryKey = 'id';
    use  HasFactory;
    protected $fillable = [
        'product_id',
        'title',
        'description',
        'gift_desc',
        'video_desc',
        'tech_desc',
        'option',
        'short',
        'short_code',
        'key_search',
        'friendly_url',
        'friendly_title',
        'metakey',
        'metadesc',
        'lang'
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id','product_id');
    }


}
