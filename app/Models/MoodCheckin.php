<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoodCheckin extends Model
{
    protected $fillable = [
        'couple_id',
        'user_id',
        'date',
        'mood_level',
        'reason_tags',
        'needs',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
        'reason_tags' => 'array',
        'needs' => 'array',
        'mood_level' => 'integer',
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
