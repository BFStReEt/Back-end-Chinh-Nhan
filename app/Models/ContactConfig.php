<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ContactConfigDesc;
class ContactConfig extends Model
{
    use HasFactory;
    protected $table = 'contact_config';
    protected $primaryKey = 'contact_id';
    protected $fillable = [
        'company', 'address','phone','fax','email','email_order','website','work_time','map_lat','map_lng','menu_order','display','adminid'
    ];
    public function contactConfigDesc()
    {
        return $this->belongsTo(ContactConfigDesc::class,'contact_id');
    }
}
