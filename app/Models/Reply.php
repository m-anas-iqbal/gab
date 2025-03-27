<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reply extends Model
{
    use  SoftDeletes;
    protected $fillable = ['body','comment_id','user_id'];

    public function comment()
    {
        return $this->belongsTo('App\Comment');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
