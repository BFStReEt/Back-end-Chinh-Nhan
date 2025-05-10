<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Member;
class StatisticsPages extends Model
{
    use HasFactory;
    protected $table = 'statistics_pages';
    protected $primaryKey = 'id_static_page';
    protected $fillable = [ 'url', 'date','count', 'mem_id','module','action','ip'];
    public function member()
    {
        return $this->hasOne(Member::class, 'id','mem_id')->select('id','username','full_name');
    }

}
