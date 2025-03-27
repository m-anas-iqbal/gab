<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarkerNote extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "marker_note";
    protected $fillable = ['marker_id', 'user_id', 'note'];

    public function marker()
    {
        return $this->belongsTo(Marker::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

