<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'code',
        'status',
        'created_by',
        'banner',
        'avatar',
    ];

    /**
     * The group belongs to an organization.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    public function members()
    {
        return $this->belongsToMany(User::class, 'group_members')
                    ->withPivot('status', 'is_admin') // Additional fields from the pivot table
                    ->withTimestamps();
    }
    // Add a method to count members
    public function memberCount()
    {
        return $this->members()->count();
    }

    public function getBannerAttribute($value)
    {
        if ($value) {
            return asset('storage/' . $value); // or any custom path logic
        }
        return null;
    }

    public function getAvatarAttribute($value)
    {
        if ($value) {
            return asset('storage/' . $value); // or any custom path logic
        }
        return null;
    }
}
