<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqsDesc extends Model
{
    use HasFactory;
    protected $table = 'faqs_desc';
    protected $primaryKey = 'id';
    protected $fillable = [
        'title', 'description','lang'
    ];
}
