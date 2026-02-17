<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoupleWorldState extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'couple_id',
        'vibe',
        'level',
        'xp',
    ];

    public function couple(): BelongsTo
    {
        return $this->belongsTo(Couple::class);
    }
}
