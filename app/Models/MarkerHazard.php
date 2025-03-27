<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarkerHazard extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "marker_hazards";
    protected $fillable = ['marker_id', 'user_id', 'hazards_id'];

    public function marker()
    {
        return $this->belongsTo(Marker::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function hazards()
    {
        return $this->belongsTo(Hazard::class);
    }

}

