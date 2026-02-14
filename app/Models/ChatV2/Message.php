<?php

namespace App\Models\ChatV2;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $table = 'chat_v2_messages';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'type',
        'body',
        'media_path',
        'media_mime',
        'media_size',
        'duration_ms',
        'reply_to_message_id',
    ];

    protected $casts = [
        'media_size' => 'integer',
        'duration_ms' => 'integer',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reply_to_message_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(MessageReceipt::class, 'message_id');
    }
}
