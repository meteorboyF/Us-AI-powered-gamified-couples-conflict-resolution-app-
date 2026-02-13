<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoupleDate extends Model
{
    protected $fillable = [
        'couple_id',
        'created_by',
        'title',
        'event_date',
        'is_anniversary',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_anniversary' => 'boolean',
    ];

    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
