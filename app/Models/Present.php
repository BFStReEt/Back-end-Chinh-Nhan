<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Present extends Model
{
    use HasFactory;
    protected $table = 'present';
    protected $primaryKey = 'id';
    protected $fillable = [ 'title','code','type','list_cat','list_product','content', 'display','priceMin','priceMax','StartDate','EndDate','cat_parent_id'];
}
