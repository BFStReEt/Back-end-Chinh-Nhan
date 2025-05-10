<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HireCategory extends Model
{
    use HasFactory;
    protected $table = 'hire_category';
    protected $primaryKey = 'id';
    protected $fillable = [ 'name', 'slug','title','status'];

}
