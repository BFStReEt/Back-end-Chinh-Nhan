<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Properties;
use App\Models\PropertiesValue;
use App\Models\Price;

class ProductProperties extends Model
{
    use HasFactory;
    protected $table = 'product_properties';
    protected $primaryKey = 'id';
    protected $fillable = [
        'pv_id', 'properties_id', 'price_id', 'description'
    ];

    public function property()
    {
        return $this->belongsTo(Properties::class, 'properties_id', 'id');
    }

    public function propertyValue()
    {
        return $this->belongsTo(PropertiesValue::class, 'pv_id');
    }
    //Thông số kỹ thuật
    public function price()
    {
        return $this->belongsTo(Price::class, 'price_id', 'id');
    }
    public function properties()
    {
        return $this->belongsTo(Properties::class, 'properties_id', 'id');
    }
    public function propertiesValue()
    {
        return $this->belongsTo(PropertiesValue::class, 'pv_id', 'id');
    }
}
