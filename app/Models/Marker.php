<?php

// app/Marker.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Marker extends Model
{
    use SoftDeletes;
    protected $fillable = ['rotation','altitude','user_id', 'group_id','internal_id','proximity','description','marker_title','lat', 'long', 'icon','flag_lat', 'flag_long', 'color', 'status'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function comments()
    {
        return $this->hasMany(MarkerComment::class);
    }
    public function images()
    {
        return $this->hasMany(MarkerImg::class);
    }

    public function todo()
    {
        return $this->hasMany(MarkerTodo::class);
    }

    public function note()
    {
        return $this->hasMany(MarkerNote::class);
    }
    public function links()
    {
        return $this->hasMany(MarkerLink::class);
    }

    public function hazards()
    {
        return $this->hasMany(MarkerHazard::class);
    }

    // public function getIconAttribute($value)
    // {
    //     if ($value) {
    //         return asset('storage/' . $value); // or any custom path logic
    //     }
    //     return null;
    // }
}
