<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory;
    protected $table = 'order_status';
    protected $primaryKey = 'status_id';
    protected $timestamp = 'true';
    protected $fillable = [
            'title',
            'keyStatus',
            'color',
            'is_default',
            'is_payment',
            'is_complete',
            'is_cancel',
            'is_customer',
            'menu_order',
            'display',
            'lang',
            'date_post',
            'date_update',
            'adminid',
        ];
}
