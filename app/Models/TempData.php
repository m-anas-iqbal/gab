<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempData extends Model
{
    use HasFactory;
    protected $fillable = [
        'marker_title',
        'hazard_id',
        'hazard_name',
        'internal_id',
        'organization_id',
        'color',
        'longitude',
        'latitude',
        'proximity',
        'description',
        'group_id_1',
        'group_id_2',
        'group_id_3',
        'group_name_1',
        'group_name_2',
        'group_name_3',
        'status',
        'extra'
    ];// Relationships

}
