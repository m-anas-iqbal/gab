<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarkerTodo extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "marker_todo";
    protected $fillable = ['marker_id', 'user_id','status', 'name'];

    public function marker()
    {
        return $this->belongsTo(Marker::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

