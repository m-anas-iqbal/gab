<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;
    // The attributes that are mass assignable
    protected $fillable = [
        'user_id',
        'organization_id',
        'invoice_no',
        'total_amount',
        'payment_type',
        'invoice_url',
    ];

    // Relationships

    // An order can have many order details
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    // An order belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // An order belongs to an organization
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    // An order belongs to an organization
    public function getInvoiceUrlAttribute($value)
    {
        if ($value) {
            return url($value); // or any custom path logic
        }
        return null;
    }
}
