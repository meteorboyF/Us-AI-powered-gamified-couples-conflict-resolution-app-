<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $fillable = [
        'couple_id',
        'user_id',
        'budget_min',
        'budget_max',
        'currency',
        'love_languages',
        'likes',
        'dislikes',
        'share_with_partner',
    ];

    protected $casts = [
        'budget_min' => 'integer',
        'budget_max' => 'integer',
        'love_languages' => 'array',
        'likes' => 'array',
        'dislikes' => 'array',
        'share_with_partner' => 'boolean',
    ];

    public function couple()
    {
        return $this->belongsTo(Couple::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
