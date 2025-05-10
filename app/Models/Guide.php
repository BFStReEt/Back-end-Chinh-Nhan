<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GuideDesc;
class Guide extends Model
{
    use HasFactory;
    protected $table = 'guide';
    protected $primaryKey = 'guide_id';
    protected $fillable = [ 'picture', 'views','display', 'menu_order','adminid' ];

    public function guideDesc()
    {
        return $this->hasOne(GuideDesc::class, 'guide_id');
    }
}
