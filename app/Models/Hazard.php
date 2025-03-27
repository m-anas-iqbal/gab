<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hazard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'icon','name_sp'];

    public function getNameAttribute($value)
{
    return  $this->attributes['name']= str_replace(['_', '-'], ' ', $value);
}

}
