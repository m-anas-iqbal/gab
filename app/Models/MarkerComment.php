<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarkerComment extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "marker_comments";
    protected $fillable = ['marker_id', 'comment','user_id'];

    // Define the relationship with the Marker model
    public function marker()
    {
        return $this->belongsTo(Marker::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
