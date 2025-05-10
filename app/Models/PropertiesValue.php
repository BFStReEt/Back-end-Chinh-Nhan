<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertiesValue extends Model
{
    use HasFactory;
    protected $table = 'properties_value';
    protected $primaryKey = 'id';
    protected $fillable = [
        'properties_id', 'name'
    ];
}
