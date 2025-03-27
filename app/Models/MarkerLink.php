<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarkerLink extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "marker_link";
    protected $fillable = ['marker_id', 'user_id', 'link','description'];

    public function marker()
    {
        return $this->belongsTo(Marker::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

