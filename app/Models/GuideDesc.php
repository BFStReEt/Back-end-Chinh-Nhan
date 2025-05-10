<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Guide;
class GuideDesc extends Model
{
    use HasFactory;
    protected $table = 'guide_desc';
    protected $primaryKey = 'id';
    protected $fillable = [
        'guide_id',
        'title',
        'description',
        'short',
        'friendly_url',
        'friendly_title',
        'metakey',
        'metadesc',
        'lang',
    ];
    public function guide()
    {
        return $this->belongsTo(Guide::class, 'guide_id','guide_id');
    }
}
