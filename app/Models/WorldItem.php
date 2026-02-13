<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorldItem extends Model
{
    protected $fillable = [
        'couple_id',
        'world_type',
        'item_key',
        'level',
        'slot',
        'position',
        'is_built',
    ];

    protected $casts = [
        'level' => 'integer',
        'position' => 'array',
        'is_built' => 'boolean',
    ];

    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }
}
