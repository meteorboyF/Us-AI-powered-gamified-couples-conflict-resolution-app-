<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftSuggestion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'couple_id',
        'requested_by',
        'input_snapshot',
        'suggestions',
        'source',
        'created_at',
    ];

    protected $casts = [
        'input_snapshot' => 'array',
        'suggestions' => 'array',
        'created_at' => 'datetime',
    ];

    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
