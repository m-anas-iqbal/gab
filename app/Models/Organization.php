<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{

    use HasFactory, SoftDeletes;
    protected $fillable = [
        'organization_type',
        'cid_stripe',
        'address',
        'contact_phone',
        'contact_email',
        'state',
        'status',
        'city',
        'name',
        'country',
        'website',
        'admin_id',
        'plan_id',
        'expires_at',
        'banner',
        'logo',
        'zipcode'
    ];
    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function admin()
    {
        return $this->hasOne(User::class,'id','admin_id');
    }
    // An organization has many subscribed plans
    public function subscribedPlans()
    {
        return $this->hasMany(SubscribedPlan::class);
    }

    // An organization can have many orders
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
