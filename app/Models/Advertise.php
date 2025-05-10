<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertise extends Model
{
    protected $table = 'advertise';
    protected $primaryKey = 'id';
    use  HasFactory;


    protected $fillable = [
        'title',
        'picture',
        'pos',
        'id_pos',
        'width',
        'height',
        'link',
        'target',
        'module_show',
        'description',
        'menu_order',
        'display',
        'lang',
        'created_at',
        'updated_at'
    ];
}
