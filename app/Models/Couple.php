<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Couple extends Model
{
    protected $fillable = [
        'invite_code',
        'created_by',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relationships
    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role', 'is_active', 'joined_at')
            ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function world()
    {
        return $this->hasOne(World::class);
    }

    public function xpEvents()
    {
        return $this->hasMany(XpEvent::class);
    }

    public function moodCheckins()
    {
        return $this->hasMany(MoodCheckin::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function giftSuggestions()
    {
        return $this->hasMany(GiftSuggestion::class);
    }

    public function coupleDates()
    {
        return $this->hasMany(CoupleDate::class);
    }

    // Helper methods
    public function generateInviteCode(): string
    {
        return strtoupper(substr(md5(uniqid(rand(), true)), 0, 10));
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
