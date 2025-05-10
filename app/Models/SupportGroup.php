<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportGroup extends Model
{
    use HasFactory;
    protected $table = 'support_group';
    protected $primaryKey = 'id';
    protected $timestamp = true;
    protected $fillable = [
        'title','name'
    ];
}
