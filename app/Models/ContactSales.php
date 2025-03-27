<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSales extends Model
{
    use HasFactory;

    protected $table = 'contact_sales';

    protected $fillable = [
        'name',
        'email',
        'message',
    ];

    // protected $casts = [
    //     'message' => 'nullable',
    // ];
}
