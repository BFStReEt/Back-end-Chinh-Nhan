<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingSmtp extends Model
{
    use HasFactory;
    protected $table = 'setting_smtp_security';
    protected $primaryKey = 'id';
    protected $fillable = [ 'method', 'host','port','username','password','from_name','password_security','time_cache'];
}
