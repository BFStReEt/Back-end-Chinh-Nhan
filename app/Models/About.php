<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AboutDesc;
class About extends Model
{
    use HasFactory;
    protected $table = 'about';
    protected $primaryKey = 'about_id';
    protected $fillable = [ 'picture', 'parentid', 'views','menu_order', 'display','adminid' ];

    public function aboutDesc()
    {
        return $this->hasOne(AboutDesc::class, 'about_id');
    }
}
