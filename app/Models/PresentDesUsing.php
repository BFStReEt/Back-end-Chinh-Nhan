<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Present;
class PresentDesUsing extends Model
{
    use HasFactory;
    protected $table = 'presentdesusing';
    protected $primaryKey = 'id';
    protected $fillable = [
        'IDuser',
        'idPresent',
        'DateUsingCode',
        'IDOrderCode',
        'MaPresentUSer',
        'IdProduct',
        'group_id'
    ];
    public function present()
    {
        return $this->belongsTo(Present::class,'idPresent','id');
    }

}
