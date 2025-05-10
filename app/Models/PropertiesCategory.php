<?php

namespace App\Models;

use App\Models\Properties;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertiesCategory extends Model
{
    use HasFactory;
    protected $table = 'properties_category';
    protected $primaryKey = 'id';
    protected $fillable = [
        'cat_id', 'properties_id', 'parentid', 'stt',
    ];
    public function properties()
    {
        return $this->hasOne(Properties::class, 'id', 'properties_id');
    }
    // public function properties()
    // {
    //     return $this->belongsTo(Properties::class, 'properties_id', 'id');
    // }
}
