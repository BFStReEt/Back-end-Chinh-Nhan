<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactQoute extends Model
{
    use HasFactory;
    protected $table = 'contact_qoute';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name','phone', 'email','company','address','content','attach_file','status','menu_order','lang','display'
    ];
}
