<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscribedPlan extends Model
{

    use HasFactory, SoftDeletes;
  // The attributes that are mass assignable
  protected $fillable = [
    'user_id',
    'organization_id',
    'subscription_plan_id',
    'extra',
    'expaiy_at',
];

 // Relationships

    // This subscribed plan belongs to a subscription plan
    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id', 'plan_product_id');
    }

    // This subscribed plan belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // This subscribed plan belongs to an organization
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
