<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductCatOption;
class ProductCatOptionDesc extends Model
{
    use HasFactory;
    protected $table = 'product_cat_option_desc';
    protected $primaryKey = 'id';
    use  HasFactory;

    protected $fillable = [
        'op_id',
        'title',
        'slug',
        'description',
        'lang'
    ];
    public function catOption()
    {
        return $this->hasOne(ProductCatOption::class, 'op_id', 'op_id');
    }
}
