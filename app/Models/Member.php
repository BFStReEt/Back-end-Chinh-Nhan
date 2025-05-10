<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Address;
class Member extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'members';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id','username','mem_code', 'email', 'password','address', 'company', 'full_name', 'gender', 'birthday','provider','provider_id','avatar','phone','Tencongty','Masothue','Diachicongty', 'Sdtcongty',
        'emailcty', 'MaKH','status','m_status','ward','district','city_province','password_token'
    ];
    public function roles(){
        return $this->belongsToMany(Address::class, 'mem_id','id');
    }
    public function orderSum()
    {
        return $this->hasMany(OrderSum::class,'mem_id','id');
    }
}
