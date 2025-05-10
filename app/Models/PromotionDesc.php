<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionDesc extends Model
{
    use HasFactory;
    protected $table = 'promotion_desc';
    protected $primaryKey = 'promotion_id';
    protected $fillable = [ 'title', 'description','short', 'friendly_url','friendly_title','metakey','metadesc','lang' ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id','promotion_id');
    }
}
