<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'email',
        'subject',
        'priority',
        'user_id',
        'status',
        'file',
        'reason',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getFileAttribute($value)
    {
        if ($value) {
            return url($value); // or any custom path logic
        }
        return null;
    }
}
