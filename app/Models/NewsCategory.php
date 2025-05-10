<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    use HasFactory;
    protected $table = 'news_category';
    protected $primaryKey = 'cat_id';
    protected $fillable = [ 'cat_code', 'parentid','picture', 'is_default','show_home','focus_order','menu_order','views','display','adminid' ];

    public function newsCategoryDesc()
    {
        return $this->hasOne(NewsCategoryDesc::class,'cat_id');
    }
    public function news()
    {
        return $this->hasMany(News::class,'cat_id','cat_id');
    }
    public function subNewsCategory()
    {
        return $this->hasMany(NewsCategory::class,'parentid','cat_id');
    }
}
