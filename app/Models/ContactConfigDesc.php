<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactConfigDesc extends Model
{
    use HasFactory;
    protected $table = 'contact_config_desc';
    protected $primaryKey = 'id';
    protected $fillable = [
        'title', 'map_desc','map_address','lang'
    ];
}
