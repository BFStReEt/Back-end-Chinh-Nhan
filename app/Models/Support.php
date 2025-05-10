<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Support extends Model
{
    use HasFactory;
    protected $table = 'support';
    protected $primaryKey = 'id';
    protected $fillable = [
        'title','group','email','phone','name','type',
    ];
}
