<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidates extends Model
{
    use HasFactory;
    protected $table = 'candidates';
    protected $primaryKey = 'id';
    protected $fillable = [ 'name', 'gmail', 'phone', 'cv', 'hire_post_id', 'message','fileInfo','status','display','date_post' ];
    public function hirePost()
    {
        return $this->belongsTo(HirePost::class,'hire_post_id','id');
    }
}
