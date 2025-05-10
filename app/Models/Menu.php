<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\MenuDes;
class Menu extends Model
{
    use HasFactory;
    protected $table = 'menu';
    protected $primaryKey = 'menu_id';
    protected $fillable = [
        'target', 'parentid','pos','menu_icon','menu_class','menu_order','display','date_post','date_update','adminid'
    ];
    public function menuDesc()
    {
        return $this->hasOne(MenuDes::class, 'menu_id');;
    }
}
