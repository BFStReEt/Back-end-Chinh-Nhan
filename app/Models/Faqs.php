<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\FaqsDesc;
use App\Models\FaqsCategoryDesc;
class Faqs extends Model
{
    use HasFactory;
    protected $table = 'faqs';
    protected $primaryKey = 'faqs_id';
    protected $fillable = [
        'cat_id', 'cat_list','poster','email_poster','phone_poster','answer_by','views','display','menu_order',
        'adminid','date_post','date_update'
    ];
    public function faqsDesc()
    {
        return $this->hasOne(FaqsDesc::class, 'faqs_id');;
    }
    public function faqsCate()
    {
        return $this->hasOne(FaqsCategoryDesc::class,'cat_id','cat_id');
    }
}
