<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;
    protected $table = 'news';
    protected $primaryKey = 'news_id';
    protected $fillable = [ 'cat_id', 'cat_list','picture', 'focus','focus_order','views','display','menu_order','adminid','date_post','date_update' ];
    public function newsDesc()
    {
        return $this->hasOne(NewsDesc::class, 'news_id','news_id');
    }
    public function category()
    {
        return $this->hasMany(Category::class,'cat_id','cat_id');
    }
    public function categoryDesc()
    {
        return $this->hasMany(NewsCategoryDesc::class,'cat_id','cat_id');
    }
}
