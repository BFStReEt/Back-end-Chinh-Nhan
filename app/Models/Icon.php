<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Icon extends Model
{
    use HasFactory;
    protected $table = 'icon';
    protected $primaryKey = 'icon_id';
    protected $fillable = [ 'type', 'picture','color', 'link','title','font_icon','target','description','menu_order','date_post','date_update','display','lang' ];
}
