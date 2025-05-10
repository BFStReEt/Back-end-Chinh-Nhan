<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
class CategoryDesc extends Model
{
    protected $table = 'product_category_desc';
    protected $primaryKey = 'id';
    use HasFactory;
    public function category()
    {
        return $this->belongsTo(Category::class, 'cat_id');
    }
    public function productAdvertise()
    {
        return $this->hasMany(ProductAdvertise::class, 'cat_id','cat_id');
    }

}
