<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderSum;
class InvoiceOrder extends Model
{
    use HasFactory;
    protected $table = 'invoice_order';
    protected $primaryKey = 'id';
    protected $fillable = [ 'order_id', 'taxCodeCompany', 'nameCompany','emailCompany','addressCompany'];

    public function orderSum()
    {
        return $this->hasMany(OrderSum::class, 'order_id','order_id');
    }
}
