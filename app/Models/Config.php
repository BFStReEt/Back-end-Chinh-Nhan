<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HasFactory;
    protected $table = 'config';
    protected $primaryKey = 'id';
    protected $filable = [
        'title','metaKeywords','metaDescription','priceOfPoint','valueOfPoint','productOfPage','width','displayPicture','picture'
    ];
}
