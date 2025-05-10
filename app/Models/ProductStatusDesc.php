<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStatusDesc extends Model
{
    use  HasFactory;
    protected $table = 'product_status_desc';
    protected $primaryKey = 'id';
    protected $fillable = [
        'status_id','title','description', 'friendly_url','friendly_title',
        'metakey','metadesc','lang'
    ];
}
