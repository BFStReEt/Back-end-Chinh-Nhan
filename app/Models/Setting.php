<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $table = 'setting';
    protected $primaryKey = 'id';
    protected $fillable = [ 'title', 'meta_desc','meta_extra', 'script','google_analytics_id','google_maps_api_id','charset','favicon'];
}
