<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    protected $table = 'infor_address';
    protected $primaryKey = 'id';
    protected $fillable = [ 'mem_id', 'gender', 'fullName','Phone',
    'address','province','district','ward','email'];

}
