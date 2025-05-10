<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactStaff extends Model
{
    use HasFactory;
    protected $table = 'contact_staff';
    protected $primaryKey = 'staff_id';
    protected $fillable = [
        'title', 'email','phone','description','menu_order','display','adminid','lang'
    ];
}
