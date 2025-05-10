<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Advertise;
class Adpos extends Model
{
    protected $table = 'ad_pos';
    protected $primaryKey = 'id_pos';
    use  HasFactory;

    protected $fillable = [
        'name',
        'cat_id',
        'title',
        'width',
        'height',
        'n_show',
        'description',
        'display',
        'menu_order',
        'created_at',
        'updated_at'
    ];

    public function advertise()
    {
        return $this->hasMany(Advertise::class,'pos','id_pos');
    }
}
