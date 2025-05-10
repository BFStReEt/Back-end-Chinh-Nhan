<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailList extends Model
{
    use HasFactory;
    protected $table = 'maillist';
    protected $primaryKey = 'id';
    protected $fillable = [ 'g_name', 'name','email', 'menu_order','display','lang','adminid','status' ];
}
