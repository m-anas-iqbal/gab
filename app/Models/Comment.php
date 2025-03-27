<?php
// app/Comment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{

    use SoftDeletes;
    // protected $softDelete = true;
    protected $fillable = ['body','post_id','user_id'];

    public function post()
    {
        return $this->belongsTo('App\Models\Post');
    }

    public function replies()
    {
        return $this->hasMany('App\Models\Reply');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
