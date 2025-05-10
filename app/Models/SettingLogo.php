<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingLogo extends Model
{
    use HasFactory;
    protected $table = 'setting_logo';
    protected $primaryKey = 'id';
    protected $fillable = [ 'logo', 'hotline','email','email_search','address','tool_search'];
}
