<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CateParentPer extends Model
{
    use HasFactory;
    protected $table = 'cate_parent_permission';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name'
    ];

}
