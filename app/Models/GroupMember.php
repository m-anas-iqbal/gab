<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupMember extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'organization_id',
        'user_id',
        'group_id',
        'status', // Status of the membership (active/inactive)
        'is_admin', // Boolean flag to indicate if the user is an admin in the group
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    /**
     * A member belongs to a group.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * A member is a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
