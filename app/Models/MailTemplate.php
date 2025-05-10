<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{
    use HasFactory;
    protected $table = 'mail_template';
    protected $primaryKey = 'mailtemp_id';
    protected $fillable = [ 'title', 'name','description', 'menu_order','display','lang','adminid' ];
}
