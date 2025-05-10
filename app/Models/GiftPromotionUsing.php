<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GiftPromotion;
class GiftPromotionUsing extends Model
{
    protected $table = 'gift_promotiondesusing';
    protected $fillable = [
        'IDuser',
        'idGiftPromotion',
        'DateUsingCode',
        'IDOrderCode',
        'MaGiftPromotion',
    ];
    public function giftPromotion()
    {
        return $this->belongsTo(GiftPromotion::class,'idGiftPromotion','id');
    }
}
