<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $table = 'comment';
    protected $primaryKey = 'comment_id';
    protected $filable = [
        'post_id',
        'parentid',
        'mem_id', 'name', 'email', 'content', 'avatar', 'mark', 'address_IP',
        'display', 'date_post', 'date_update', 'adminid','phone'
    ];


    public function member()
    {
        return $this->belongsTo( Member::class, 'mem_id', 'mem_id' );
    }

    public function subcomments()
    {
        return $this->hasOne( Comment::class, 'parentid', 'comment_id' );
    }
    public function productDesc()
    {
        return $this->hasOne(ProductDesc::class, 'product_id', 'post_id')->select('product_id','title','friendly_url');
    }
    // public function newsDesc()
    // {
    //     return $this->hasOne( NewsDesc::class, 'news_id', 'post_id' );
    // }
}
