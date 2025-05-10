<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\FaqsCategoryDesc;
class FaqsCategory extends Model
{
    use HasFactory;
    protected $table = 'faqs_category';
    protected $primaryKey = 'cat_id';
    protected $fillable = [
        'cat_code', 'parentid','picture','is_default','show_home','focus_order','menu_order','views','display','adminid'
    ];
    public function faqsCategoryDesc()
    {
        return $this->hasOne(FaqsCategoryDesc::class, 'cat_id');
    }
}
