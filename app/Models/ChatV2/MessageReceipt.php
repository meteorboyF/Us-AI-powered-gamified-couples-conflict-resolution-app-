<?php

namespace App\Models\ChatV2;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageReceipt extends Model
{
    protected $table = 'chat_v2_message_receipts';

    protected $fillable = [
        'message_id',
        'user_id',
        'delivered_at',
        'read_at',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
