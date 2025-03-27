<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable
{
    use HasRoles, HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $guard_name = 'web';
    protected $fillable = [
        'name',
        'email',
        'is_admin',
        'phone',
        'last_device',
        'last_login_date',
        'password',
        'image',
        'device_token',
        'organization_id',
        'status',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $appends = ['role_id'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];
    public function getRoleIdAttribute()
    {
        $role = $this->roles()->first();;
        return $role ? $role->id : null;
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function isAdmin()
    {
        return $this->is_admin == 1;
    }
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members');
                    // ->withPivot('status', 'is_admin') // Additional fields from the pivot table
                    // ->withTimestamps();
    }
    public function group_member()
    {
        return $this->hasMany(GroupMember::class);
    }
    public function group_admin()
    {
        return $this->hasMany(GroupMember::class)->where('is_admin', 1);
    }
    public function getImageAttribute($value)
    {
        if ($value) {
            return asset('storage/' . $value); // or any custom path logic
        }
        return null;
    }
}
