<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XpEvent extends Model
{
    protected $fillable = [
        'couple_id',
        'user_id',
        'type',
        'xp_amount',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'xp_amount' => 'integer',
    ];

    // Relationships
    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
