<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupPermission extends Model
{
    use HasFactory;
    protected $table = 'group_permissions';
    protected $primaryKey = 'id';
    protected $fillable = [ 'name', 'slug', 'description','parentId'];
}
