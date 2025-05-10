<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Collaborator extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'collaborators';
    protected $primaryKey = 'id';

    protected $fillable = ['username','password','email',
    'display_name','avatar','status','phone','created_at','updated_at'
    ];
}
