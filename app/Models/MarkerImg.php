<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarkerImg extends Model
{
    use HasFactory;
    protected $fillable = [
        'marker_id',
        'img',
        'user_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
