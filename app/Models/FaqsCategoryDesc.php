<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqsCategoryDesc extends Model
{
    use HasFactory;
    protected $table = 'faqs_category_desc';
    protected $primaryKey = 'id';
    protected $fillable = [
        'cat_id', 'cat_name','description','friendly_url','friendly_title','metakey','metadesc','lang'
    ];
}
