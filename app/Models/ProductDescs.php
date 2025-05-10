<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products;
class ProductDescs extends Model
{
    protected $table = 'product_desc';
    protected $primaryKey = 'id';
    use  HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'title',
        'description',
        'gift_desc',
        'video_desc',
        'tech_desc',
        'option',
        'short',
        'start_date_promotion',
        'end_date_promotion',
        'status_promotion',
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
        return $this->belongsTo(Products::class, 'product_id','product_id');
    }
    public function priceList()
    {
        return $this->hasMany(Price::class,'product_id','product_id');
    }
}
