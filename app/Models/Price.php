<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;
    protected $table = 'price';
    protected $primaryKey = 'id';
    protected $fillable = [
        'cat_id', 'product_id', 'price', 'price_old', 'picture', 'main'
    ];
    public function propertiesProduct()
    {
        return $this->hasMany(ProductProperties::class, 'price_id', 'id');
    }
}
