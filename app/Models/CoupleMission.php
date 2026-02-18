<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoupleMission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'couple_id',
        'mission_template_id',
        'status',
        'started_at',
        'completed_at',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'completed_at' => 'date',
            'meta' => 'array',
        ];
    }

    public function couple(): BelongsTo
    {
        return $this->belongsTo(Couple::class);
    }

    public function missionTemplate(): BelongsTo
    {
        return $this->belongsTo(MissionTemplate::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(MissionCompletion::class);
    }
}
