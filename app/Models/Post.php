<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title', 'group_id','content','user_id','image','video_url','post_type'
    ];
    public function likes()
    {
        return $this->hasMany('App\Models\Like');
    }
    public function comments()
    {
        return $this->hasMany('App\Models\Comment');
    }
    public function user()
        {
            return $this->belongsTo(User::class);
        }
}
