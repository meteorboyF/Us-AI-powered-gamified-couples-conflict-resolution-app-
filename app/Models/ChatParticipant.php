<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ChatParticipant extends Pivot
{
    /** @use HasFactory<\Database\Factories\ChatParticipantFactory> */
    use HasFactory;

    protected $table = 'chat_participants';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'chat_id',
        'user_id',
        'joined_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
