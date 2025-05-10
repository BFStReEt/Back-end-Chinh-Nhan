<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\NewsCategoryDesc;
class NewsDesc extends Model
{
    use HasFactory;
    protected $table = 'news_desc';
    protected $primaryKey = 'id';
    protected $fillable = [ 'news_id', 'product_id','title', 'description','short','friendly_url','friendly_title','metakey','metadesc','lang' ];
    public function news()
    {
        return $this->belongsTo(News::class, 'news_id','news_id');
    }
    public function categoryDesc()
    {
        return $this->hasMany(NewsCategoryDesc::class,'cat_id','cat_id');
    }
}
