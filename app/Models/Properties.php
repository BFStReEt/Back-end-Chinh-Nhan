<?php

namespace App\Models;

use App\Models\PropertiesValue;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Properties extends Model
{
    use HasFactory;
    protected $table = 'properties';
    protected $primaryKey = 'id';
    protected $fillable = [
        'title',
    ];

    public function propertiesValue()
    {
        return $this->hasMany(PropertiesValue::class, 'properties_id', 'id');
    }
    public function PropertiesCategory()
    {
        return $this->hasOne(PropertiesCategory::class, 'properties_id', 'id');
    }
}
