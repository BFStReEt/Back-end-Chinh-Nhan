<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\About;
class AboutDesc extends Model
{
    use HasFactory;
    protected $table = 'about_desc';
    protected $primaryKey = 'id';
    protected $fillable = [
        'about_id',
        'title',
        'description',
        'friendly_url',
        'friendly_title',
        'metakey',
        'metadesc',
        'lang',
    ];
    public function about()
    {
        return $this->belongsTo(About::class, 'about_id','about_id');
    }
}
