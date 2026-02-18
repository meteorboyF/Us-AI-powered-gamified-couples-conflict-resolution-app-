<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Couple extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'invite_code',
        'created_by_user_id',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(CoupleMember::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'couple_members')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    public function worldState(): HasOne
    {
        return $this->hasOne(CoupleWorldState::class);
    }

    public function worldItems(): BelongsToMany
    {
        return $this->belongsToMany(WorldItem::class, 'couple_world_items')
            ->withPivot(['unlocked_at'])
            ->withTimestamps();
    }

    public function missions(): HasMany
    {
        return $this->hasMany(CoupleMission::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(DailyCheckin::class);
    }
}
