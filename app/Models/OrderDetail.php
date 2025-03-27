<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetail extends Model
{
    use HasFactory, SoftDeletes;
     // The attributes that are mass assignable
     protected $fillable = [
        'order_id',
        'subscription_plan_id',
        'quantity',
        'amount',
        'st_subscription_id',
    ];

   // Relationships

    // This order detail belongs to an order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // This order detail belongs to a subscription plan
    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id', 'plan_product_id');
    }
}
