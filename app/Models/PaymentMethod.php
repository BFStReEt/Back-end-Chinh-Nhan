<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;
    protected $table = 'payment_method';
    protected $primaryKey = 'payment_id';
    protected $fillable = [ 'title', 'name', 'description',
    'options','is_config','menu_order','display','date_post','date_update','lang'];
}
