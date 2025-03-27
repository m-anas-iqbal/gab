<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plan_product_id',
        'product_product_id',
        'name',
        'plan_type',
        'description',
        'details',
        'status',
        'livemode',
        'amount',
        'currency',
        'interval_count',
        'interval'
    ];// Relationships

    // A subscription plan might have multiple orders
    public function orders()
    {
        return $this->hasMany(OrderDetail::class, 'subscription_plan_id', 'plan_product_id');
    }

    // A subscription plan might have multiple subscribed plans
    public function subscribedPlans()
    {
        return $this->hasMany(SubscribedPlan::class, 'subscription_plan_id', 'plan_product_id');
    }
}
