<?php

namespace App\Models\ChatV2;

use App\Models\Couple;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $table = 'chat_v2_conversations';

    protected $fillable = [
        'couple_id',
    ];

    public function couple(): BelongsTo
    {
        return $this->belongsTo(Couple::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }
}
