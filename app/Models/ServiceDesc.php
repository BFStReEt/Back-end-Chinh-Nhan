<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Service;
class ServiceDesc extends Model
{
    use HasFactory;
    protected $table = 'service_desc';
    protected $primaryKey = 'id';
    protected $fillable = ['title','description','short','friendly_url','friendly_title','metakey','metadesc','lang' ];
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id','service_id');
    }
}

