<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderSum;
class OrderAddress extends Model
{
    use HasFactory;
    protected $table = 'order_address';
    protected $primaryKey = 'id';
    protected $fillable = [ 'order_id', 'district', 'ward','province','address','from_day'];

    public function orderSum()
    {
        return $this->hasMany(OrderSum::class, 'order_id','order_id');
    }
}
